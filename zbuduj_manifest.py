#!/usr/bin/env python3
"""
Buduje manifest.json motywu (sumy md5 plików wydania) z ZATWIERDZONEGO stanu repozytorium (git HEAD), czyli z tych samych
bajtów, które Appsero spakuje z tagu. Uruchom po ostatnim commicie przed tagiem:
    python zbuduj_manifest.py && git add manifest.json && git commit -m "manifest X.Y.Z"
Sprawdzenie (bez zapisu): python zbuduj_manifest.py --sprawdz  (exit 1, gdy manifest nie pasuje do HEAD)
"""
import hashlib
import json
import re
import subprocess
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parent


def git(*a: str) -> bytes:
    return subprocess.run(["git", "-C", str(REPO), *a], capture_output=True, check=True).stdout


def policz() -> dict:
    pliki = {}
    for linia in git("ls-tree", "-r", "HEAD").decode("utf-8").splitlines():
        meta, sciezka = linia.split("\t", 1)
        if sciezka in ("manifest.json",):
            continue
        pliki[sciezka] = hashlib.md5(git("cat-file", "blob", meta.split()[2])).hexdigest()
    styl = git("show", "HEAD:style.css").decode("utf-8", "replace")
    wersja = re.search(r"^Version:\s*(\S+)", styl, re.M)
    return {"wersja": wersja.group(1) if wersja else "", "pliki": dict(sorted(pliki.items()))}


def main() -> None:
    m = policz()
    sciezka = REPO / "manifest.json"
    if "--sprawdz" in sys.argv:
        obecny = json.loads(git("show", "HEAD:manifest.json")) if "manifest.json" in git("ls-tree", "--name-only", "HEAD").decode() else {}
        ok = obecny == m
        print("manifest zgodny z HEAD" if ok else "manifest NIEZGODNY z HEAD (przebuduj)")
        sys.exit(0 if ok else 1)
    sciezka.write_text(json.dumps(m, ensure_ascii=False, indent=1) + "\n", encoding="utf-8", newline="\n")
    print(f"manifest.json: wersja {m['wersja']}, plików {len(m['pliki'])}")


if __name__ == "__main__":
    main()
