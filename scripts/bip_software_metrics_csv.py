import pymysql
import csv
import re
import json
from pathlib import Path
from bs4 import BeautifulSoup

# Increase CSV field size limit to handle large fields
csv.field_size_limit(10485760)

# Settings
CSV_FILE = Path("records_software-2025-07-11.csv")
OUTPUT_CSV = Path("zenodo_software_code_urls_from_csv.csv")

def clean_title(title):
    """Remove HTML tags from title field"""
    if not title:
        return ""
    # Only parse if it looks like HTML (contains < or >)
    if '<' in title or '>' in title:
        import warnings
        with warnings.catch_warnings():
            warnings.simplefilter("ignore")
            soup = BeautifulSoup(title, 'html.parser')
            return soup.get_text().strip()
    return title.strip()

def extract_code_url(row):
    """Extract code repository URL from all possible columns"""
    code_url = None
    
    # 1. Try related_identifiers (parse JSON)
    related_identifiers = row.get('related_identifiers', '').strip()
    if related_identifiers:
        try:
            # Find JSON array in the text (might be mixed with description)
            json_match = re.search(r'\[.*?\]', related_identifiers)
            if json_match:
                json_str = json_match.group(0)
                related_list = json.loads(json_str)
                
                for rel in related_list:
                    identifier = rel.get("identifier", "").lower()
                    relation = rel.get("relation", "").lower()
                    if "git" in identifier or "code" in relation:
                        code_url = rel["identifier"]
                        break
        except (json.JSONDecodeError, KeyError):
            pass  # Skip if JSON parsing fails
    
    # 2. Check additional_descriptions
    if not code_url:
        additional_descriptions = row.get('additional_descriptions', '').strip()
        if additional_descriptions and ('github.com' in additional_descriptions or 'gitlab.com' in additional_descriptions or 'bitbucket.org' in additional_descriptions):
            url_match = re.search(r'(https?://[^\s<>"\']+)', additional_descriptions)
            if url_match:
                code_url = url_match.group(1)
    
    # 3. Check custom_code_coderepository
    if not code_url:
        custom_code_repo = row.get('custom_code_coderepository', '').strip()
        if custom_code_repo and ('github.com' in custom_code_repo or 'gitlab.com' in custom_code_repo or 'bitbucket.org' in custom_code_repo):
            url_match = re.search(r'(https?://[^\s<>"\']+)', custom_code_repo)
            if url_match:
                code_url = url_match.group(1)
    
    # 4. Check alternate_identifiers
    if not code_url:
        alternate_identifiers = row.get('alternate_identifiers', '').strip()
        if alternate_identifiers and ('github.com' in alternate_identifiers or 'gitlab.com' in alternate_identifiers or 'bitbucket.org' in alternate_identifiers):
            url_match = re.search(r'(https?://[^\s<>"\']+)', alternate_identifiers)
            if url_match:
                code_url = url_match.group(1)
    
    # 5. Check description
    if not code_url:
        description = row.get('description', '').strip()
        if description and ('github.com' in description or 'gitlab.com' in description or 'bitbucket.org' in description):
            url_match = re.search(r'(https?://[^\s<>"\']+)', description)
            if url_match:
                code_url = url_match.group(1)
    
    # 6. Fallback to title (last resort)
    if not code_url:
        title = row.get('title', '').strip()
        if title and ('github.com' in title or 'gitlab.com' in title or 'bitbucket.org' in title):
            url_match = re.search(r'(https?://[^\s<>"\']+)', title)
            if url_match:
                code_url = url_match.group(1)
    
    return code_url

def extract_zenodo_id_from_record_id(record_id):
    """Extract Zenodo ID from record_id - assuming it's the same as record_id"""
    return record_id

# Connect to database to get internal_id mappings
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

# Get mapping of zenodo_id to internal_id from database
try:
    cursor.execute("""
        SELECT pp.internal_id, p.doi 
        FROM pmc_paper pp
        JOIN pmc_paper_pids p ON pp.internal_id = p.paper_id
        WHERE pp.type = 2 AND p.doi LIKE '10.5281/zenodo.%'
    """)
    db_records = cursor.fetchall()
    print(f"Fetched {len(db_records)} software records from database.")
    
    # Create mapping from zenodo_id to internal_id
    zenodo_to_internal = {}
    for record in db_records:
        doi = record['doi']
        internal_id = record['internal_id']
        match = re.search(r'zenodo\.(\d+)', doi)
        if match:
            zenodo_id = match.group(1)
            zenodo_to_internal[zenodo_id] = internal_id
            
    print(f"Created mapping for {len(zenodo_to_internal)} zenodo records.")
    
except Exception as e:
    print(f"Database query failed: {e}")
    conn.close()
    exit()

conn.close()

# Process CSV file
results = []
processed_count = 0
found_count = 0
error_count = 0

if not CSV_FILE.exists():
    print(f"CSV file not found: {CSV_FILE}")
    exit()

print(f"Reading CSV file: {CSV_FILE}")

try:
    with open(CSV_FILE, 'r', encoding='utf-8', errors='ignore') as csvfile:
        # Use comma as delimiter (sniffer can get confused with complex JSON content)
        delimiter = ','
        
        reader = csv.DictReader(csvfile, delimiter=delimiter)
        
        for row_num, row in enumerate(reader, 1):
            processed_count += 1
            
            if processed_count % 1000 == 0:
                print(f"Processed {processed_count} rows...")
            
            try:
                record_id = row.get('record_id', '').strip().strip('"')  # Remove quotes
                if not record_id:
                    continue
                
                # Get clean title
                title = clean_title(row.get('title', ''))
                
                # Extract code URL
                code_url = extract_code_url(row)
                
                # Check if this zenodo record exists in our database
                zenodo_id = extract_zenodo_id_from_record_id(record_id)
                internal_id = zenodo_to_internal.get(zenodo_id)
                
                if internal_id and code_url:
                    results.append({
                        'record_id': record_id,
                        'internal_id': internal_id,
                        'zenodo_id': zenodo_id,
                        'title': title,
                        'code_url': code_url,
                        'is_github': '1' if 'github.com' in code_url else '0'
                    })
                    found_count += 1
                
            except Exception as e:
                error_count += 1
                if error_count <= 10:  # Only show first 10 errors
                    print(f"Error processing row {row_num}: {e}")
                continue

except Exception as e:
    print(f"Error reading CSV file: {e}")
    exit()

# Write results to CSV
print(f"\nWriting results to {OUTPUT_CSV}")
with open(OUTPUT_CSV, mode="w", newline="", encoding="utf-8") as f:
    fieldnames = ['record_id', 'internal_id', 'zenodo_id', 'title', 'code_url', 'is_github']
    writer = csv.DictWriter(f, fieldnames=fieldnames)
    writer.writeheader()
    writer.writerows(results)

print(f"\nPROCESSING COMPLETE")
print(f"Total rows processed: {processed_count}")
print(f"Records found in database: {len(results)}")
print(f"Records with code URLs: {found_count}")
print(f"Errors encountered: {error_count}")
print(f"Results saved to: {OUTPUT_CSV}")

# Summary statistics
github_count = sum(1 for r in results if r['is_github'] == '1')
other_repo_count = sum(1 for r in results if r['code_url'] and r['is_github'] == '0')
no_repo_count = sum(1 for r in results if not r['code_url'])

print(f"\nSUMMARY:")
print(f"   GitHub repositories: {github_count}")
print(f"   Other repositories: {other_repo_count}")
print(f"   No repository found: {no_repo_count}")
print(f"   Total database matches: {len(results)}")