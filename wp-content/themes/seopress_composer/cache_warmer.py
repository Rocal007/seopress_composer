import sys
import urllib.request
import urllib.error
import xml.etree.ElementTree as ET
from concurrent.futures import ThreadPoolExecutor, as_completed
import time

def fetch_sitemap(url):
    print(f"Lese Sitemap: {url}")
    urls = []
    try:
        # Standard Browser User-Agent vortäuschen, um Blockaden durch Hosting-Firewalls zu vermeiden
        headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'}
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req, timeout=15) as response:
            xml_content = response.read()
            
        # WordPress Sitemaps haben oft unsichtbare Leerzeichen oder Leerzeilen am Anfang
        xml_text = xml_content.decode('utf-8', errors='ignore').strip()
        
        # Schneide alles ab, was vor dem eigentlichen XML-Start-Tag steht
        if '<?xml' in xml_text:
            xml_text = xml_text[xml_text.find('<?xml'):]
            
        root = ET.fromstring(xml_text)
        
        # XML Namespaces in Sitemaps ignorieren wir einfach
        for child in root:
            tag = child.tag.split('}')[-1] if '}' in child.tag else child.tag
            
            # Wenn es ein Sitemap-Index ist (enthält weitere Sitemaps)
            if tag == 'sitemap':
                loc = None
                for elem in child:
                    elem_tag = elem.tag.split('}')[-1] if '}' in elem.tag else elem.tag
                    if elem_tag == 'loc':
                        loc = elem.text
                        break
                if loc:
                    urls.extend(fetch_sitemap(loc))
                    
            # Wenn es eine normale Sitemap ist (enthält URLs)
            elif tag == 'url':
                loc = None
                for elem in child:
                    elem_tag = elem.tag.split('}')[-1] if '}' in elem.tag else elem.tag
                    if elem_tag == 'loc':
                        loc = elem.text
                        break
                if loc:
                    urls.append(loc)
                    
    except urllib.error.HTTPError as e:
        if e.code != 404:
            print(f"HTTP Fehler beim Lesen der Sitemap {url}: {e.code}")
    except Exception as e:
        print(f"Fehler beim Parsen der Sitemap {url}: {e}")
        
    return urls

def warm_url(url, max_retries=3):
    attempt = 0
    while attempt < max_retries:
        try:
            headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'}
            req = urllib.request.Request(url, headers=headers)
            with urllib.request.urlopen(req, timeout=120) as response:
                status = response.getcode()
                return (url, status)
        except urllib.error.HTTPError as e:
            # Bei einem echten 404 (Nicht gefunden) bringt ein erneuter Versuch nichts
            if e.code == 404:
                return (url, e.code)
            attempt += 1
            if attempt >= max_retries:
                return (url, e.code)
            time.sleep(3) # Kurz warten vor dem Retry
        except Exception as e:
            attempt += 1
            if attempt >= max_retries:
                return (url, f"Timeout/Error: {e}")
            time.sleep(3)

def main():
    if len(sys.argv) < 2:
        print("Verwendung: python cache_warmer.py <basis_url>")
        print("Beispiel: python cache_warmer.py https://meine-seite.at")
        sys.exit(1)
        
    base_url = sys.argv[1].rstrip('/')
    
    # Gängige Sitemap-Pfade bei WordPress und SEO-Plugins
    sitemap_paths = [
        '/wp-sitemap.xml',       # Standard WordPress Sitemap
        '/sitemap_index.xml',    # Yoast SEO / RankMath Sitemap
        '/sitemap.xml'           # Generische Sitemap
    ]
    
    urls_to_warm = []
    
    # Suche nach der passenden Sitemap
    for path in sitemap_paths:
        sitemap_url = base_url + path
        urls = fetch_sitemap(sitemap_url)
        if urls:
            urls_to_warm.extend(urls)
            break # Höre auf, sobald wir eine gültige Sitemap gefunden haben
            
    if not urls_to_warm:
        print("Keine URLs in der Sitemap gefunden oder Sitemap nicht erreichbar.")
        sys.exit(1)
        
    # Duplikate entfernen, falls vorhanden
    urls_to_warm = list(set(urls_to_warm))
    total_urls = len(urls_to_warm)
    print(f"\n{total_urls} URLs zum Aufwärmen gefunden.")
    print("Starte Cache Warmup...\n")
    
    success_count = 0
    error_count = 0
    start_time = time.time()
    
    # Wir nutzen hier bewusst nur 1-2 Worker (max_workers=1),
    # da die allererste WebP-Generierung pro Seite locker 20-30 Sekunden dauern kann.
    # 5 parallele Requests würden den Server überlasten und in Timeouts enden.
    max_workers = 1
    
    with ThreadPoolExecutor(max_workers=max_workers) as executor:
        futures = {executor.submit(warm_url, url): url for url in urls_to_warm}
        
        for i, future in enumerate(as_completed(futures), 1):
            url, status = future.result()
            if status == 200:
                success_count += 1
                print(f"[{i}/{total_urls}] OK (200): {url}")
            else:
                error_count += 1
                print(f"[{i}/{total_urls}] FEHLER ({status}): {url}")
                
    end_time = time.time()
    duration = end_time - start_time
    
    print("\n--- Cache Warmup Abgeschlossen ---")
    print(f"Erfolgreich geladen: {success_count}")
    print(f"Fehler/Nicht gefunden: {error_count}")
    print(f"Benötigte Zeit: {duration:.2f} Sekunden")

if __name__ == '__main__':
    main()
