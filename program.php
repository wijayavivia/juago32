<?php
// Fungsi untuk menghitung jumlah baris dalam file JSON (jumlah slug)
function getFileRowCount($filename)
{
    $data = json_decode(file_get_contents($filename), true);
    return count($data['brands']);
}

// Mendapatkan URL dasar
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$fullUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

if (isset($fullUrl)) {
    // Memproses URL asli untuk digunakan dalam sitemap
    $parsedUrl = parse_url($fullUrl);
    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : '';
    $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
    
    // Ambil path dan hapus "program.php"
    $path = isset($parsedUrl['path']) ? str_replace("program.php", "", $parsedUrl['path']) : '';
    
    // Bangun URL dasar tanpa "program.php"
    $baseUrl = $scheme . "://" . $host . rtrim($path, '/');

    // Pastikan $baseUrl diakhiri dengan "/"
    if (substr($baseUrl, -1) !== '/') {
        $baseUrl .= '/';
    }

    // Membuat robots.txt
    $robotsTxt = "User-agent: *" . PHP_EOL;
    $robotsTxt .= "Allow: /" . PHP_EOL;
    $robotsTxt .= "Sitemap: " . $baseUrl . "sitemap.xml" . PHP_EOL;
    file_put_contents('robots.txt', $robotsTxt);

    // Membaca file list.json
    $filename = "list.json";
    if (file_exists($filename)) {
        $jsonContent = file_get_contents($filename);
        $data = json_decode($jsonContent, true);

        if (is_array($data['brands'])) {
            // Membuat file sitemap.xml
            $sitemapFile = fopen("sitemap.xml", "w");
            fwrite($sitemapFile, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
            fwrite($sitemapFile, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL);

            // Proses setiap slug dari file list.json
            foreach ($data['brands'] as $item) {
                $sitemapLink = $baseUrl . $item['slug']; // Gunakan slug dari JSON

                fwrite($sitemapFile, '  <url>' . PHP_EOL);
                fwrite($sitemapFile, '    <loc>' . htmlspecialchars($sitemapLink) . '</loc>' . PHP_EOL);

                date_default_timezone_set('Asia/Jakarta');
                $currentTime = date('Y-m-d\TH:i:sP');
                fwrite($sitemapFile, '    <lastmod>' . $currentTime . '</lastmod>' . PHP_EOL);
                fwrite($sitemapFile, '    <changefreq>daily</changefreq>' . PHP_EOL);
                fwrite($sitemapFile, '  </url>' . PHP_EOL);
            }

            fwrite($sitemapFile, '</urlset>' . PHP_EOL);
            fclose($sitemapFile);

            echo "Sitemap berhasil dibuat!";
        } else {
            echo "Format file JSON valid.";
        }
    } else {
        echo "File list.json ditemukan.";
    }
} else {
    echo "URL saat ini didefinisikan.";
}
?>
