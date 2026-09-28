"""Read-only DBF profiler; standard library only. Run from any working directory."""
from pathlib import Path
import struct, json, hashlib, collections, re

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / 'docs' / 'evidence'
OUT.mkdir(parents=True, exist_ok=True)
tables, data = [], {}
for path in sorted(ROOT.glob('*'), key=lambda p:p.name.upper()):
    if path.suffix.lower() != '.dbf':
        continue
    raw = path.read_bytes()
    count = struct.unpack_from('<I', raw, 4)[0]
    header, size = struct.unpack_from('<HH', raw, 8)
    fields, pos, offset = [], 32, 1
    while pos + 32 <= header and raw[pos] != 13:
        chunk = raw[pos:pos+32]
        name = chunk[:11].split(b'\0')[0].decode('ascii')
        fields.append(dict(name=name, type=chr(chunk[11]), length=chunk[16], decimals=chunk[17], offset=offset))
        offset += chunk[16]
        pos += 32
    rows, deleted, invalid = [], 0, 0
    for i in range(count):
        rec = raw[header+i*size:header+(i+1)*size]
        if len(rec) != size:
            invalid += 1
            continue
        if rec[:1] == b'*':
            deleted += 1
            continue
        if rec[:1] != b' ':
            invalid += 1
            continue
        rows.append({f['name']:rec[f['offset']:f['offset']+f['length']].decode('cp437').strip() for f in fields})
    item = dict(file=path.name, bytes=len(raw), sha256=hashlib.sha256(raw).hexdigest(), version=raw[0], language_driver=raw[29], header_records=count, active_records=len(rows), deleted_records=deleted, invalid_records=invalid, record_bytes=size, fields=fields)
    tables.append(item)
    data[path.stem.upper()] = rows

keys = {'RMMAST':['RMCODE'],'FGMAST':['FGCODE'],'NDLMAST':['NDLCODE'],'AREA':['CODE'],'FACTORY':['CODE'],'TYPEMAST':['PRTYPE1'],'COSTREF':['CODE'],'FORMULA':['FGCODE','RMCODE'],'NDLFORM':['NDLCODE','FGCODE'],'VOLNDL':['CODE','NDLCODE'],'VOLUME':['FGCODE','CODE'],'SYNONIM':['RMCODE']}
quality = {'keys':[], 'relationships':[], 'small_master_values':{}, 'numeric_ranges':{}}
for name, columns in keys.items():
    counts = collections.Counter(tuple(r.get(c,'') for c in columns) for r in data[name])
    quality['keys'].append(dict(table=name, columns=columns, duplicate_groups=sum(v>1 for v in counts.values()), duplicate_extra_rows=sum(v-1 for v in counts.values()), blank_key_rows=sum(v for k,v in counts.items() if any(not a for a in k))))
for child, col, parent, pcol in [('FORMULA','FGCODE','FGMAST','FGCODE'),('FORMULA','RMCODE','RMMAST','RMCODE'),('NDLFORM','NDLCODE','NDLMAST','NDLCODE'),('NDLFORM','FGCODE','FGMAST','FGCODE'),('VOLNDL','CODE','AREA','CODE'),('VOLNDL','NDLCODE','NDLMAST','NDLCODE'),('VOLUME','FGCODE','FGMAST','FGCODE'),('VOLUME','CODE','AREA','CODE'),('SYNONIM','RMCODE','RMMAST','RMCODE'),('SYNONIM','FGCODE','FGMAST1','FGCODE')]:
    valid = {r[pcol] for r in data[parent]}
    misses = [r[col] for r in data[child] if r[col] not in valid]
    quality['relationships'].append(dict(child=child, column=col, parent=parent, parent_column=pcol, unmatched_rows=len(misses), unmatched_distinct=len(set(misses))))
for name in ['AREA','FACTORY','TYPEMAST','COSTREF']:
    quality['small_master_values'][name] = data[name]
for name, cols in {'RMMAST':['WASTE','ID','TYPE_CURR','UNIT'], 'FGMAST':['LEVEL','PECKP','PESMG','PESBY','PEPLG']}.items():
    for col in cols:
        vals = collections.Counter(r.get(col,'') for r in data[name])
        quality['numeric_ranges'][name+'.'+col] = dict(distinct=len(vals), common=vals.most_common(12))

(OUT/'dbf-schema.json').write_text(json.dumps(tables,indent=2,ensure_ascii=False),encoding='utf-8')
(OUT/'data-quality.json').write_text(json.dumps(quality,indent=2,ensure_ascii=False),encoding='utf-8')
inventory = ['# Inventaris DBF aktual','', 'Dihasilkan oleh `docs/tools/inspect_legacy.py`. DBF hanya dibaca; record deleted tidak masuk pemeriksaan relasi. Teks didekode sementara sebagai CP437; encoding produksi perlu dikonfirmasi. Header field bersifat faktual, makna bisnis dibahas terpisah.','', '| File | Record header | Aktif | Deleted | Invalid/truncated | Kolom |','|---|---:|---:|---:|---:|---:|']
for t in tables:
    inventory.append(f"| {t['file']} | {t['header_records']} | {t['active_records']} | {t['deleted_records']} | {t['invalid_records']} | {len(t['fields'])} |")
inventory += ['', '## Kamus seluruh kolom', '', 'C = karakter; N = numerik; D = tanggal; L = logical; M = referensi memo. Panjang adalah byte pada DBF, bukan ukuran tipe SQL. Tidak ada primary key/foreign key SQL yang dideklarasikan DBF.']
for t in tables:
    inventory += ['', '### '+t['file'], '', '| Kolom | Tipe | Byte | Desimal |','|---|---|---:|---:|']
    inventory += [f"| {f['name']} | {f['type']} | {f['length']} | {f['decimals']} |" for f in t['fields']]
(ROOT/'docs'/'02-kamus-dbf.md').write_text('\n'.join(inventory)+'\n',encoding='utf-8')
source_map=[]
for path in sorted(ROOT.iterdir()):
    if path.suffix.lower() == '.prg':
        lines=path.read_bytes().decode('cp437').splitlines()
        for i,line in enumerate(lines,1):
            if re.match(r'\s*(function|procedure|func|proc)\s+',line,re.I):
                source_map.append({'file':path.name,'line':i,'declaration':line.strip()})
(OUT/'source-map.json').write_text(json.dumps(source_map,indent=2),encoding='utf-8')
print(json.dumps({'dbf_files':len(tables),'records':sum(t['active_records'] for t in tables),'keys':quality['keys'],'relationships':quality['relationships'],'master_values':quality['small_master_values'],'ranges':quality['numeric_ranges']},indent=2))
