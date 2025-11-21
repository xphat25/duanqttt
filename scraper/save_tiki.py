import requests

url = "https://tiki.vn/dien-thoai-may-tinh-bang/c1789"
headers = {"User-Agent": "Mozilla/5.0"}

html = requests.get(url, headers=headers).text

open("tiki.html", "w", encoding="utf-8").write(html)

print("Đã lưu file tiki.html")
