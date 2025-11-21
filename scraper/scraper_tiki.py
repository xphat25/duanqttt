import sys, io, json

# ÉP PYTHON OUTPUT UTF-8
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

import requests
import json
import sys
import re

def scrape_tiki(category_id):
    results = []
    page = 1

    while True:
        api_url = f"https://tiki.vn/api/personalish/v1/blocks/listings?category={category_id}&page={page}"

        res = requests.get(api_url, headers={
            "User-Agent": "Mozilla/5.0"
        })

        if res.status_code != 200:
            break

        data = res.json()
        items = data.get("data", [])

        if not items:
            break

        for p in items:

            # Xử lý sold an toàn
            qs = p.get("quantity_sold")
            sold = qs["value"] if isinstance(qs, dict) else None

            results.append({
                "title": p.get("name"),
                "link": "https://tiki.vn/" + p.get("url_path", ""),
                "image": p.get("thumbnail_url"),
                "price_new": p.get("price"),
                "price_old": p.get("original_price"),
                "discount": p.get("discount_rate"),
                "rating": p.get("rating_average"),
                "rating_count": p.get("review_count"),
                "sold": sold
            })

        page += 1

    return results


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Missing URL"}))
        sys.exit()

    url = sys.argv[1]

    # Lấy category_id từ URL
    m = re.search(r"/c(\d+)", url)
    if not m:
        print(json.dumps({"error": "Invalid Tiki URL"}))
        sys.exit()

    category_id = m.group(1)

    data = scrape_tiki(category_id)
    print(json.dumps(data, ensure_ascii=False))
