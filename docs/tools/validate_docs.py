"""Check documentation links, schema arithmetic and unchanged DBF inputs."""
from pathlib import Path
import json, hashlib, re

root = Path(__file__).resolve().parents[2]
docs = root / 'docs'
schema = json.loads((docs/'evidence/dbf-schema.json').read_text(encoding='utf-8'))
errors = []
for table in schema:
    file = root / table['file']
    if hashlib.sha256(file.read_bytes()).hexdigest() != table['sha256']:
        errors.append('Source changed: '+table['file'])
    if table['header_records'] != sum(table[k] for k in ['active_records','deleted_records','invalid_records']):
        errors.append('Record accounting: '+table['file'])
    if sum(f['length'] for f in table['fields']) + 1 != table['record_bytes']:
        errors.append('Record width: '+table['file'])
links = 0
for file in docs.glob('*.md'):
    text = file.read_text(encoding='utf-8')
    if text.count('```') % 2:
        errors.append('Unclosed code block: '+file.name)
    for target in re.findall(r'\]\(([^)]+)\)',text):
        if target.startswith(('https://','http://','#')):
            continue
        links += 1
        if not (file.parent/target.split('#')[0]).exists():
            errors.append('Broken link: '+target)
result = dict(dbf_files_checked=len(schema), source_hashes_unchanged=not any('Source changed' in e for e in errors), local_links_checked=links, markdown_files=len(list(docs.glob('*.md'))), errors=errors)
(docs/'evidence/document-validation.json').write_text(json.dumps(result,indent=2),encoding='utf-8')
print(json.dumps(result,indent=2))
raise SystemExit(bool(errors))
