import sys

with open('index.html', 'rb') as f:
    lines = f.readlines()

new_lines = []
for i, line in enumerate(lines):
    new_lines.append(line)
    if b"<script>" in line:
        # Check next line
        if i + 1 < len(lines) and b"document.addEventListener" in lines[i+1]:
            # Insert our logic after this next line
            pass

# let's just do a string replace but handle \r\n
with open('index.html', 'rb') as f:
    content = f.read()

# Replace \r\n with \n to make it easier, or just find
if b"const yrEl =" not in content:
    idx = content.find(b"const photo = document.getElementById('featured-photo');")
    if idx != -1:
        insert = b"const yrEl = document.getElementById('yr');\n  if (yrEl) yrEl.textContent = new Date().getFullYear();\n\n  "
        content = content[:idx] + insert + content[idx:]
        with open('index.html', 'wb') as f:
            f.write(content)
        print("Updated")
    else:
        print("Could not find const photo")
else:
    print("Already updated")
