import chardet
files = ['index.html', 'give/index.html', 'harvest/index.html', 'join/index.html']
for f in files:
    with open(f, 'rb') as file:
        raw = file.read()
    res = chardet.detect(raw)
    dot_count = raw.count('·'.encode('utf-8'))
    copy_count = raw.count('©'.encode('utf-8'))
    print(f'{f}: {res["encoding"]} | dot_count (utf-8): {dot_count} | copy_count (utf-8): {copy_count}')
