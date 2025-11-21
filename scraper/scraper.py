# -*- coding: utf-8 -*-

import os
os.environ['WDM_LOG'] = '0'
os.environ['WDM_PRINT_FIRST_LINE'] = 'False'

import sys
import io
import json
import logging

# ÉP PYTHON XUẤT UTF-8 → TRÁNH LỖI CHARMAP WINDOWS
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.common.exceptions import WebDriverException
from webdriver_manager.chrome import ChromeDriverManager

# Import hàm extract từ beauti scraper
from scraper_beauti import extract_products

logging.basicConfig(stream=sys.stderr, level=logging.INFO)

REQUEST_TIMEOUT = 30
WAIT_TIME = 10


def get_driver():
    chrome_options = Options()
    chrome_options.add_argument("--headless=new")
    chrome_options.add_argument("--disable-gpu")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    chrome_options.add_argument("--disable-blink-features=AutomationControlled")
    chrome_options.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')

    chrome_options.add_experimental_option("excludeSwitches", ["enable-logging"])

    service = Service(ChromeDriverManager().install())
    driver = webdriver.Chrome(service=service, options=chrome_options)
    driver.set_page_load_timeout(REQUEST_TIMEOUT)
    return driver


def scrape_page(url):
    driver = None

    try:
        logging.info(f"ChromeDriver loading: {url}")
        driver = get_driver()
        driver.get(url)

        # Chờ DOM load
        WebDriverWait(driver, WAIT_TIME).until(
            EC.presence_of_element_located((By.TAG_NAME, "body"))
        )

        html = driver.page_source

        # Parse bằng beauti scraper
        results = extract_products(html, url)

        logging.info(f"[OK] Extracted {len(results)} products.")
        return results

    except Exception as e:
        logging.error(f"[SCRAPER ERROR] {e}")
        # Trả lỗi đúng JSON → PHP không crash
        return {"error": str(e)}

    finally:
        if driver:
            try:
                driver.quit()
            except:
                pass


# ============================================================
# MAIN
# ============================================================

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No URL provided"}, ensure_ascii=False))
        sys.exit(0)

    input_url = sys.argv[1].strip()

    data = scrape_page(input_url)

    print(json.dumps(data, ensure_ascii=False))
