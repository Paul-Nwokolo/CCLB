import os

files_to_update = ['index.html', 'give/index.html', 'harvest/index.html', 'join/index.html']

new_footer = """<footer class="site-footer">
  <div class="wrap">
    <span class="crest" role="img" aria-label="Church crest"></span>
    <h3>Catholic Church of the Lord's Baptism</h3>
    <p class="tag">Lord baptise us with your Spirit</p>
    <p>Osapa London, off Beach Resort, Lekki, Lagos · +234 913 222 2286 · <a href="mailto:info@cclblekki.com">info@cclblekki.com</a></p>
    <p>Sunday Mass: Preparation 8:00 AM · Holy Mass 8:30 AM</p>
    <p class="fine">© <span id="yr"></span> Catholic Church of the Lord's Baptism. Built and maintained by the CCLB Lekki.</p>

    <div class="footer-eyebrow">Follow the Parish</div>
    <ul class="social-bar">
      <li>
        <a href="https://www.instagram.com/cclblekki/" aria-label="Follow us on Instagram">
          <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
          </svg>
        </a>
      </li>
    </ul>
  </div>
</footer>"""

for filepath in files_to_update:
    if not os.path.exists(filepath):
        print(f'File {filepath} not found')
        continue
    with open(filepath, 'rb') as f:
        raw_content = f.read()
        
    raw_start = raw_content.find(b'<footer class="site-footer">')
    raw_end = raw_content.find(b'</footer>', raw_start) + len(b'</footer>')
    
    if raw_start == -1 or raw_end < len(b'</footer>'):
        print(f'Footer not found in {filepath}')
        continue
    
    new_raw = raw_content[:raw_start] + new_footer.encode('utf-8') + raw_content[raw_end:]
    
    with open(filepath, 'wb') as f:
        f.write(new_raw)
    print(f'Updated {filepath}')
