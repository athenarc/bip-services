import pymysql
import requests
import time
import re
import csv
import random
from pathlib import Path
from concurrent.futures import ThreadPoolExecutor, as_completed


# Settings
THREADS = 1
#SLEEP_SECONDS = 0.3
TIMEOUT = 5
OUTPUT_CSV = Path("zenodo_software_code_urls.csv")

print("🚀 SCRIPT STARTED")

# Connect to database
try:
    conn = pymysql.connect(
        host="localhost",
        user="apache_bip_user",
        password="b1p1$@w3s0m3",
        database="bcn_papers",
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )
    cursor = conn.cursor()
    print("✅ Connected to database.")
except Exception as e:
    print(f"❌ DB connection failed: {e}")
    exit()

try:
    cursor.execute("""
        SELECT doi 
        FROM pmc_paper 
        WHERE type = 2 AND doi LIKE '10.5281/zenodo.%'
    """)
    rows = cursor.fetchall()
    print(f"✅ Fetched {len(rows)} software DOIs.")
except Exception as e:
    print(f"❌ Query failed: {e}")
    conn.close()
    exit()

conn.close()

# Extract Zenodo IDs
doi_id_pairs = []
for row in rows:
    doi = row['doi']
    match = re.search(r'zenodo\.(\d+)', doi)
    if match:
        zenodo_id = match.group(1)
        doi_id_pairs.append((doi, zenodo_id))

results = []

def process_zenodo_entry(doi, zenodo_id, i, total):
    print(f"[{i}/{total}] 🔍 Checking {doi}")
    url = f"https://zenodo.org/api/records/{zenodo_id}"
    code_url = None

    try:
        resp = requests.get(url, timeout=TIMEOUT)
        if resp.status_code != 200:
            print(f"[{i}] ❌ Zenodo returned {resp.status_code}")
            return None

        data = resp.json()
        resource_type = data.get("metadata", {}).get("resource_type", {}).get("type", "")
        if resource_type != "software":
            print(f"[{i}] ⏭️ Not software (type: {resource_type})")
            return None

        # 1. Try related_identifiers
        related = data.get("metadata", {}).get("related_identifiers", [])
        for rel in related:
            identifier = rel.get("identifier", "").lower()
            if "git" in identifier or "code" in rel.get("relation", "").lower():
                code_url = rel["identifier"]
                break

        # 2. Fallback to metadata.code_repository
        if not code_url:
            code_url = data.get("metadata", {}).get("code_repository")

        # 3. Fallback to metadata.custom["code:codeRepository"]
        if not code_url:
            code_url = (
                data.get("metadata", {})
                    .get("custom", {})
                    .get("code:codeRepository")
            )

        if code_url:
            print(f"[{i}] ✅ Found repo: {code_url}")
            return (doi, code_url)
        else:
            print(f"[{i}] ❌ No code repo found")
            return None

    except Exception as e:
        print(f"[{i}] ⚠️ Error: {e}")
        return None
    finally:
        time.sleep(random.uniform(0.2, 0.7))

# Run threads
with ThreadPoolExecutor(max_workers=THREADS) as executor:
    futures = {
        executor.submit(process_zenodo_entry, doi, zid, idx+1, len(doi_id_pairs)): (doi, zid)
        for idx, (doi, zid) in enumerate(doi_id_pairs)
    }

    for future in as_completed(futures):
        result = future.result()
        if result:
            results.append(result)

# Write to CSV
with open(OUTPUT_CSV, mode="w", newline="", encoding="utf-8") as f:
    writer = csv.writer(f)
    writer.writerow(["doi", "code_url"])
    writer.writerows(results)

print(f"\n✅ Done. Saved {len(results)} entries to {OUTPUT_CSV}")