import re
import urllib.request
import urllib.parse
import http.cookiejar

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))
login_page = opener.open("http://127.0.0.1:8000/admin/login/").read().decode()
csrf = re.search(r'name="csrfmiddlewaretoken" value="([^"]+)"', login_page).group(1)
data = urllib.parse.urlencode(
    {
        "username": "admin",
        "password": "admin123",
        "csrfmiddlewaretoken": csrf,
        "next": "/admin/dashboard/",
    }
).encode()
req = urllib.request.Request(
    "http://127.0.0.1:8000/admin/login/",
    data=data,
    headers={"Referer": "http://127.0.0.1:8000/admin/login/"},
)
resp = opener.open(req)
print("login", resp.geturl(), resp.status)
for url in [
    "/admin/dashboard/",
    "/admin/courses/",
    "/admin/applications/",
    "/admin/applications/1/",
    "/admin/reports/",
    "/admin/settings/",
]:
    r = opener.open("http://127.0.0.1:8000" + url)
    print(url, r.status, len(r.read()))
