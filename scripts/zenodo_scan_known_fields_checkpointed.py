import pymysql
import requests
import time
import re
import csv
import random
import os
from pathlib import Path
from concurrent.futures import ThreadPoolExecutor, as_completed

# Settings
THREADS = 1
TIMEOUT = 5
BATCH_SIZE = 90000
CSV_PREFIX = "zenodo_batch_"
CHECKPOINT_FILE = "zenodo_checkpoint.txt"

print("SCRIPT STARTED")

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
    print("Connected to database.")
except Exception as e:
    print(f"DB connection failed: {e}")
    exit()

try:
    cursor.execute("""
        SELECT doi 
        FROM pmc_paper 
        WHERE type = 2 AND doi LIKE '10.5281/zenodo.%'
    """)
    rows = cursor.fetchall()
    print(f"Fetched {len(rows)} software DOIs.")
except Exception as e:
    print(f"Query failed: {e}")
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

# Resume from checkpoint if it exists
start_index = 0
if Path(CHECKPOINT_FILE).exists():
    with open(CHECKPOINT_FILE, "r") as ck:
        start_index = int(ck.read().strip())
        print(f"Resuming from checkpoint: index {start_index}")

total = len(doi_id_pairs)

def process_zenodo_entry(doi, zenodo_id, i, total):
    print(f"[{i}/{total}] Checking {doi}")
    url = f"https://zenodo.org/api/records/{zenodo_id}"

    related_url = None
    code_repo_url = None
    custom_repo_url = None
    seen = set()

    try:
        resp = requests.get(url, timeout=TIMEOUT)
        if resp.status_code != 200:
            print(f"[{i}] Zenodo returned {resp.status_code}")
            return None

        data = resp.json()
        resource_type = data.get("metadata", {}).get("resource_type", {}).get("type", "")
        if resource_type != "software":
            print(f"[{i}] Not software (type: {resource_type})")
            return None

        for rel in data.get("metadata", {}).get("related_identifiers", []):
            identifier = rel.get("identifier", "").strip()
            if identifier and identifier not in seen:
                related_url = identifier
                seen.add(identifier)
                break

        code_repo = data.get("metadata", {}).get("code_repository")
        if code_repo:
            code_repo = code_repo.strip()
            if code_repo and code_repo not in seen:
                code_repo_url = code_repo
                seen.add(code_repo)

        custom_repo = (
            data.get("metadata", {})
                .get("custom", {})
                .get("code:codeRepository")
        )
        if custom_repo:
            custom_repo = custom_repo.strip()
            if custom_repo and custom_repo not in seen:
                custom_repo_url = custom_repo
                seen.add(custom_repo)

        if related_url or code_repo_url or custom_repo_url:
            print(f"[{i}] Found repo(s)")
            return (doi, related_url, code_repo_url, custom_repo_url)
        else:
            print(f"[{i}] No code repo found")
            return None

    except Exception as e:
        print(f"[{i}] Error: {e}")
        return None
    finally:
        time.sleep(random.uniform(0.2, 0.7))

# Main processing loop
current_batch = start_index // BATCH_SIZE
for batch_start in range(start_index, total, BATCH_SIZE):
    current_batch += 1
    batch_end = min(batch_start + BATCH_SIZE, total)
    batch_file = Path(f"{CSV_PREFIX}{current_batch}.csv")

    print(f"\n🔹 Processing batch {current_batch} — entries {batch_start} to {batch_end - 1}")

    with open(batch_file, mode="a", newline="", encoding="utf-8") as f:
        writer = csv.writer(f)

        if f.tell() == 0:
            writer.writerow(["doi", "related_identifier_url", "code_repository_url", "custom_codeRepository_url"])

        with ThreadPoolExecutor(max_workers=THREADS) as executor:
            futures = {
                executor.submit(process_zenodo_entry, doi, zid, idx + 1, total): (doi, zid)
                for idx, (doi, zid) in enumerate(doi_id_pairs[batch_start:batch_end], start=batch_start)
            }

            for future in as_completed(futures):
                result = future.result()
                if result:
                    writer.writerow(result)

    # Save checkpoint
    with open(CHECKPOINT_FILE, "w") as ck:
        ck.write(str(batch_end))
        print(f"Checkpoint saved at index {batch_end}")

print("\n All results saved in batch CSV files.")
