#!/usr/bin/env python3
"""
Wydanie motywu w Appsero z tagu (uruchamia .github/workflows/appsero.yml).

Appsero samo wydawało wersje z tagów (push to deploy), ale jego token GitHuba wygasł (401 przy pobieraniu ZIP tagu),
a GitHub unieważnia nieużywane tokeny OAuth po roku. Dlatego wydanie robi ten skrypt, tokenem API konta Appsero
(sekret APPSERO_TOKEN w repozytorium):
  1. paczka z TAGU: pliki bez wykluczeń z appsero.json, katalog główny `lunadesign-zero-theme` (NIGDY `breakdance-zero-theme`:
     strona bez strażnika nadpisałaby aktywny motyw), wersja w style.css = tag, manifest.json zgodny z plikami;
  2. Appsero: wgranie paczki (upload-project) i nowe wydanie (releases), jak w panelu;
  3. kontrola: API aktualizacji podaje nową wersję, pobrana paczka = nasza bajt w bajt.
Wersja, która już jest w Appsero, jest pomijana (bez błędu). Bez --wyslij: tylko próba (paczka + logowanie + lista wydań).
"""
from __future__ import annotations

import argparse
import datetime as dt
import hashlib
import io
import json
import os
import re
import subprocess
import sys
import tarfile
import time
import urllib.error
import urllib.parse
import urllib.request
import uuid
import zipfile

API = "https://api.appsero.com"
KATALOG = "lunadesign-zero-theme"
# Cloudflare przed api.appsero.com odrzuca zapytania bez nagłówków przeglądarki (błąd 1010)
NAGLOWKI = {
    "Accept": "application/json",
    "User-Agent": "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0 Safari/537.36",
    "Origin": "https://dashboard.appsero.com",
    "Referer": "https://dashboard.appsero.com/",
}


def git(*a: str) -> bytes:
    return subprocess.run(["git", *a], capture_output=True, check=True).stdout


def zapytanie(metoda: str, sciezka: str, token: str | None = None, dane: bytes | None = None, typ: str | None = None):
    h = dict(NAGLOWKI)
    if token:
        h["Authorization"] = f"Bearer {token}"
    if typ:
        h["Content-Type"] = typ
    for proba in range(3):
        r = urllib.request.Request(API + sciezka, data=dane, headers=h, method=metoda)
        try:
            with urllib.request.urlopen(r, timeout=120) as o:
                return o.status, o.read()
        except urllib.error.HTTPError as e:
            tresc = e.read()
            if e.code >= 500 and proba < 2:
                time.sleep(15)
                continue
            return e.code, tresc
        except urllib.error.URLError:
            if proba < 2:
                time.sleep(15)
                continue
            raise
    return 0, b""


def stop(tekst: str) -> None:
    print(f"::error::{tekst}")
    sys.exit(1)


def paczka(tag: str, wersja: str) -> tuple[bytes, int]:
    styl = git("show", f"{tag}:style.css").decode("utf-8", "replace")
    w = re.search(r"^Version:\s*(\S+)", styl, re.M)
    if not w or w.group(1) != wersja:
        stop(f"style.css w {tag} ma wersję {w.group(1) if w else '?'}, a wydanie to {wersja}")
    wyklucz = set(json.loads(git("show", f"{tag}:appsero.json")).get("exclude", []))
    t = tarfile.open(fileobj=io.BytesIO(git("archive", "--format=tar", tag)))
    pliki = {}
    for m in t.getmembers():
        n = m.name.rstrip("/")
        if m.isfile() and not any(n == x or n.startswith(x + "/") for x in wyklucz):
            pliki[n] = t.extractfile(m).read()
    for wymagany in ("style.css", "functions.php"):
        if wymagany not in pliki:
            stop(f"w paczce brak {wymagany}")
    if "manifest.json" in pliki:
        spis = json.loads(pliki["manifest.json"]).get("pliki", {})
        zle = [n for n, s in spis.items() if n in pliki and hashlib.md5(pliki[n]).hexdigest() != s]
        bez = [n for n in pliki if n != "manifest.json" and n not in spis]
        if zle or bez:
            stop(f"manifest.json niezgodny z tagiem (zmienione {zle[:3]}, spoza manifestu {bez[:3]}): zbuduj_manifest.py, commit, nowy tag")
    czas = dt.datetime.fromtimestamp(int(git("log", "-1", "--format=%ct", tag).strip()), dt.timezone.utc).timetuple()[:6]
    bufor = io.BytesIO()
    with zipfile.ZipFile(bufor, "w", zipfile.ZIP_DEFLATED) as z:
        for n in sorted(pliki):
            info = zipfile.ZipInfo(f"{KATALOG}/{n}", date_time=czas)
            info.external_attr = 0o644 << 16
            info.compress_type = zipfile.ZIP_DEFLATED
            z.writestr(info, pliki[n])
    return bufor.getvalue(), len(pliki)


def opis_zmian(tag: str) -> str:
    tekst = git("tag", "-l", "--format=%(contents)", tag).decode("utf-8", "replace")
    tekst = tekst.split("-----BEGIN PGP SIGNATURE-----")[0].strip() or git("log", "-1", "--format=%B", tag).decode("utf-8", "replace").strip()
    return tekst.replace("—", "-").replace("–", "-") or f"Wersja {tag}"


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--tag", required=True)
    ap.add_argument("--projekt", default="themes/lunadesign-zero-theme", help="baza/slug projektu w Appsero")
    ap.add_argument("--wyslij", action="store_true")
    a = ap.parse_args()
    if not re.fullmatch(r"v\d+(\.\d+){1,3}", a.tag):
        stop(f"tag {a.tag!r} nie wygląda jak wydanie (vX.Y.Z)")
    if not re.fullmatch(r"(themes|plugins)/[a-z0-9-]+", a.projekt):
        stop(f"zły projekt {a.projekt!r}")
    token = os.environ.get("APPSERO_TOKEN", "").strip()
    if not token:
        stop("brak sekretu APPSERO_TOKEN")
    wersja = a.tag[1:]
    slug = a.projekt.split("/")[1]

    s, b = zapytanie("GET", f"/v1/{a.projekt}", token)
    if s != 200:
        stop(f"Appsero: projekt {a.projekt} niedostępny (HTTP {s}); token wygasł albo zmieniony? {b[:150]!r}")
    hash_ = json.loads(b)["data"]["hash"]
    s, b = zapytanie("GET", f"/v1/{a.projekt}/releases?page=1&orderby=release_date&order=desc", token)
    if s != 200:
        stop(f"Appsero: lista wydań HTTP {s}")
    wersje = [r["version"] for r in json.loads(b).get("data", [])]
    print(f"Appsero {a.projekt}: zalogowano, ostatnie wydania {wersje[:5]}")
    if wersja in wersje:
        print(f"Wersja {wersja} już jest w Appsero, nic nie robię.")
        return

    zip_, n = paczka(a.tag, wersja)
    nazwa = f"{KATALOG}-{wersja}.zip"
    print(f"Paczka {nazwa}: {n} plików, {len(zip_)} B, md5 {hashlib.md5(zip_).hexdigest()}")
    if not a.wyslij:
        print("PRÓBA: nic nie wydano (publikacja: --wyslij).")
        return

    granica = uuid.uuid4().hex
    cialo = (f"--{granica}\r\nContent-Disposition: form-data; name=\"project_file\"; filename=\"{nazwa}\"\r\n"
             f"Content-Type: application/zip\r\n\r\n").encode() + zip_ + \
            (f"\r\n--{granica}\r\nContent-Disposition: form-data; name=\"type\"\r\n\r\n{a.projekt.split('/')[0][:-1]}\r\n"
             f"--{granica}--\r\n").encode()  # type: theme / plugin (jak panel)
    s, b = zapytanie("POST", f"/v1/projects/{slug}/upload-project", token, cialo, f"multipart/form-data; boundary={granica}")
    if s not in (200, 201):
        stop(f"Appsero: wgranie paczki HTTP {s} {b[:200]!r}")
    plik = json.loads(b)
    dane = {
        "version": wersja,
        "release_date": dt.datetime.now(dt.timezone.utc).strftime("%Y-%m-%dT%H:%M:%S.000Z"),
        "requires": "", "php": "", "tested": "",
        "change_log": opis_zmian(a.tag),
        "required_plugins": {},
        "file_location": {slug: {"path": plik["path"], "name": plik["name"]}},
    }
    s, b = zapytanie("POST", f"/v1/{a.projekt}/releases", token, json.dumps(dane).encode(), "application/json;charset=UTF-8")
    if s not in (200, 201):
        stop(f"Appsero: utworzenie wydania HTTP {s} {b[:300]!r}")
    print(f"Appsero: wydanie {wersja} utworzone (id {json.loads(b).get('data', {}).get('id')})")

    time.sleep(5)
    form = urllib.parse.urlencode({"version": "0.0.1", "slug": slug, "name": "Breakdance Zero Theme", "basename": slug, "license_key": ""}).encode()
    s, b = zapytanie("POST", f"/update/{hash_}/check", None, form, "application/x-www-form-urlencoded")
    api = json.loads(b).get("new_version") if s == 200 else None
    s2, pobrana = zapytanie("GET", f"/update/{hash_}/download")
    zgodna = s2 == 200 and pobrana == zip_
    print(f"Kontrola: API aktualizacji podaje {api}; pobrana paczka {'= nasza' if zgodna else 'INNA niż nasza'}")
    if api != wersja or not zgodna:
        stop("kontrola po wydaniu nie przeszła: sprawdź panel Appsero (Releases)")
    print(f"GOTOWE: {wersja} wydana w Appsero ({a.projekt}).")


if __name__ == "__main__":
    main()
