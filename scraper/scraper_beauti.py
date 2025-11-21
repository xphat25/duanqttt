# -*- coding: utf-8 -*-

import sys
import io

# ÉP PYTHON IN UTF-8 → TRÁNH LỖI CHARMAP
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin
import logging
import re
import json


def clean_price_value(text):
    """Làm sạch chuỗi giá (vd: '2.090.000đ' -> 2090000)"""
    if not text:
        return None
    cleaned = re.sub(r"[^\d]", "", text)
    try:
        return int(cleaned) if cleaned else None
    except:
        return None


def extract_products(html_content, base_url):
    """Phân tích HTML dựa trên div.item-slide của SieuThiYTe"""
    results = []
    soup = BeautifulSoup(html_content, 'html.parser')

    product_items = soup.select("div.item-slide")
    logging.info(f"Found {len(product_items)} item-slide elements.")

    for item in product_items:
        try:
            # ---- LINK ----
            link_tag = item.find('a', href=True)
            if not link_tag:
                continue
            product_link = urljoin(base_url, link_tag['href'])

            # ---- TITLE ----
            title_tag = item.select_one("h3.title")
            title = title_tag.get_text(strip=True) if title_tag else "No Title"

            # ---- IMAGE ----
            img_tag = item.select_one("div.img img")
            image_url = None
            if img_tag:
                raw_img_url = (
                    img_tag.get("data-original")
                    or img_tag.get("data-src")
                    or img_tag.get("src")
                )
                if raw_img_url:
                    image_url = urljoin(base_url, raw_img_url)

            # ---- PRICE ----
            price_old = None
            price_new = None
            discount = None

            price_tag = item.select_one("p.price")
            if price_tag:
                del_tag = price_tag.select_one("del")
                if del_tag:
                    price_old = clean_price_value(del_tag.get_text())

                full_text = price_tag.get_text(strip=True)
                nums = re.findall(r"[\d\.]+", full_text)
                nums_clean = [clean_price_value(n) for n in nums if clean_price_value(n)]

                if nums_clean:
                    price_new = min(nums_clean)
                    if len(nums_clean) > 1:
                        old_val = max(nums_clean)
                        if old_val > price_new:
                            price_old = old_val

            # ---- DISCOUNT ----
            if price_old and price_new and price_old > price_new:
                pct = int(((price_old - price_new) / price_old) * 100)
                discount = f"-{pct}%"

            results.append({
                "title": title,
                "link": product_link,
                "image": image_url,
                "price_old": price_old,
                "price_new": price_new,
                "discount": discount
            })

        except Exception as e:
            logging.error(f"Error parsing item: {e}")
            continue

    return results


# ============================================================
#                           MAIN
# ============================================================

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Missing URL"}, ensure_ascii=False))
        sys.exit(0)

    url = sys.argv[1]

    try:
        response = requests.get(url, timeout=10, headers={
            "User-Agent": "Mozilla/5.0"
        })
        response.raise_for_status()
    except Exception as e:
        print(json.dumps({"error": f"Cannot fetch URL: {e}"}, ensure_ascii=False))
        sys.exit(0)

    data = extract_products(response.text, url)

    # Trả JSON UTF-8 về PHP
    print(json.dumps(data, ensure_ascii=False))
