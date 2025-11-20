# scrape.py - Scraper sử dụng Selenium để lấy HTML, Regex để trích xuất dữ liệu, và gọi scrape_beauti.py để lấy ảnh.
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.common.exceptions import TimeoutException, WebDriverException
import re
import logging
from urllib.parse import urlparse, urljoin
import time
from webdriver_manager.chrome import ChromeDriverManager 
from scrape_beauti import extract_images_and_titles # <--- IMPORT TỪ FILE MỚI

# Configuration
REQUEST_TIMEOUT = 30
WAIT_TIME = 10

def get_driver():
    """Khởi tạo Chrome WebDriver."""
    chrome_options = Options()
    
    chrome_options.add_argument('--headless')
    chrome_options.add_argument('--no-sandbox')
    chrome_options.add_argument('--disable-dev-shm-usage')
    chrome_options.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')
    chrome_options.add_experimental_option("excludeSwitches", ["enable-logging"])
    chrome_options.add_argument('--log-level=3')

    try:
        service = Service(ChromeDriverManager().install())
        driver = webdriver.Chrome(service=service, options=chrome_options)
        driver.set_page_load_timeout(REQUEST_TIMEOUT)
        logging.info("Chrome driver initialized successfully")
        return driver
    except WebDriverException as e:
        logging.error(f"Cannot initialize Chrome driver: {e}")
        raise

def clean_price(text):
    """Làm sạch chuỗi giá, loại bỏ 'đ' và dấu chấm/phẩy, chuyển về integer."""
    if not text:
        return None
    
    # Loại bỏ tất cả các ký tự không phải chữ số
    cleaned = re.sub(r"[^\d]", "", text)
    
    if not cleaned:
        return None
    
    try:
        return int(cleaned)
    except ValueError:
        logging.warning(f"Cannot convert price: '{text}' -> '{cleaned}'")
        return None

def scrape_page(url):
    """
    Hàm scraping chính: Sử dụng Selenium + Regex (chính) và gọi scrape_beauti 
    để lấy hình ảnh bổ sung.
    """
    logging.info(f"=" * 70)
    logging.info(f"Starting Hybrid Scrape (Regex + Separate BS): {url}")
    logging.info(f"=" * 70)
    
    driver = None
    results = []
    
    try:
        driver = get_driver()
        driver.get(url)
        
        # Chờ phần tử body tải xong
        WebDriverWait(driver, WAIT_TIME).until(
            EC.presence_of_element_located((By.TAG_NAME, "body"))
        )
        
        # Lấy toàn bộ HTML đã được kết xuất (Đầu vào cho BS)
        page_source = driver.page_source 
        
        # Lấy nội dung văn bản thô cho Regex
        body_text = driver.find_element(By.TAG_NAME, "body").text

        # 1. CHẠY LOGIC REGEX CŨ (Để lấy Title và Price)
        pattern = r"(Combo.*?)\s+([\d\.]+đ)\s+([\d\.]+đ)"
        matches = re.findall(pattern, body_text, re.MULTILINE)
        
        if not matches:
             # Fallback
             pattern = r"(Combo.*?)\s+([\d]{3,}(?:\.[\d]{3})*đ)\s+([\d]{3,}(?:\.[\d]{3})*đ)"
             matches = re.findall(pattern, body_text, re.MULTILINE)

        logging.info(f"Tìm thấy {len(matches)} combo khuyến mãi bằng Regex.")

        # 2. GỌI BEAUTIFUL SOUP TỪ FILE KHÁC ĐỂ LẤY HÌNH ẢNH
        image_map = extract_images_and_titles(page_source, url)

        # 3. KẾT HỢP VÀ XỬ LÝ DỮ LIỆU
        if matches:
            for match in matches:
                title, price_old_str, price_new_str = match
                title_stripped = title.strip()
                
                # --- XỬ LÝ GIÁ VÀ DISCOUNT (SỬA LỖI BIẾN price_old) ---
                price_old = clean_price(price_old_str)
                price_new = clean_price(price_new_str)
                
                # Tính toán discount
                discount = None
                if price_old and price_new and price_old > price_new:
                     try:
                        percentage = round((price_old - price_new) / price_old * 100)
                        discount = f"-{percentage}%"
                     except (ZeroDivisionError, TypeError):
                        discount = None
                
                # LẤY HÌNH ẢNH: ĐỐI CHIẾU LINH HOẠT
                matched_image = None
                
                for image_title_key, image_url in image_map.items():
                    # Kiểm tra xem tiêu đề Regex có là một phần của tiêu đề ALT không (hoặc ngược lại)
                    if title_stripped in image_title_key or image_title_key in title_stripped:
                        matched_image = image_url
                        break
                
                results.append({
                    "title": title_stripped,
                    "price_old": price_old,
                    "price_new": price_new,
                    "discount": discount,
                    "image": matched_image, 
                    "link": url + "#" + title_stripped.replace(" ", "_")[:50], 
                })
        
        logging.info(f"Đã xử lý {len(results)} sản phẩm hợp lệ.")
        return results
        
    except Exception as e:
        logging.error(f"Lỗi trong quá trình scraping: {e}")
        import traceback
        logging.error(traceback.format_exc())
        raise
        
    finally:
        if driver:
            driver.quit()

def is_valid_url(url):
    """Validate URL format."""
    if not url or not isinstance(url, str):
        return False
    
    try:
        parsed = urlparse(url.strip())
        return parsed.scheme in ("http", "https") and bool(parsed.netloc)
    except Exception:
        return False