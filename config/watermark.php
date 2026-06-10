<?php
/**
 * Menambahkan watermark transparan ke gambar menggunakan GD library.
 * Gambar asli disimpan terpisah, dan gambar preview (ber-watermark) disimpan untuk ditampilkan.
 *
 * @param string $sourcePath  Path absolut ke gambar asli (yang akan di-watermark)
 * @param string $outputPath  Path absolut ke file output (gambar preview ber-watermark)
 * @param string $watermarkPath Path absolut ke gambar watermark (PNG transparan)
 * @return bool  True jika berhasil
 */
function apply_watermark(string $sourcePath, string $outputPath, string $watermarkPath = ''): bool
{
    if (!$watermarkPath) {
        $watermarkPath = __DIR__ . '/../Assets/galateart_logo.png';
    }

    // Pastikan file ada
    if (!file_exists($sourcePath) || !file_exists($watermarkPath)) {
        return false;
    }

    // Deteksi tipe gambar sumber
    $info = getimagesize($sourcePath);
    if (!$info) return false;

    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $source = imagecreatefrompng($sourcePath);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }

    if (!$source) return false;

    // Load watermark
    $watermark = imagecreatefrompng($watermarkPath);
    if (!$watermark) {
        imagedestroy($source);
        return false;
    }

    $srcW = imagesx($source);
    $srcH = imagesy($source);
    $wmW  = imagesx($watermark);
    $wmH  = imagesy($watermark);

    // Resize watermark agar proporsional (~30% lebar gambar)
    $targetWmW = (int) ($srcW * 0.30);
    $ratio = $targetWmW / $wmW;
    $targetWmH = (int) ($wmH * $ratio);

    $wmResized = imagecreatetruecolor($targetWmW, $targetWmH);
    imagealphablending($wmResized, false);
    imagesavealpha($wmResized, true);
    $transparent = imagecolorallocatealpha($wmResized, 0, 0, 0, 127);
    imagefilledrectangle($wmResized, 0, 0, $targetWmW, $targetWmH, $transparent);
    imagecopyresampled($wmResized, $watermark, 0, 0, 0, 0, $targetWmW, $targetWmH, $wmW, $wmH);

    // Terapkan watermark di tengah gambar dengan opacity rendah
    $destX = (int) (($srcW - $targetWmW) / 2);
    $destY = (int) (($srcH - $targetWmH) / 2);

    // Set opacity watermark (~40% visible)
    imagecopymerge($source, $wmResized, $destX, $destY, 0, 0, $targetWmW, $targetWmH, 40);

    // Tambahkan teks watermark diagonal sebagai lapisan tambahan
    $textColor = imagecolorallocatealpha($source, 255, 255, 255, 100); // sangat transparan
    $fontSize = max(12, (int)($srcW * 0.025));
    $text = 'GalateArt';
    
    // Buat pattern diagonal watermark
    $angle = -30;
    $stepX = (int)($srcW * 0.25);
    $stepY = (int)($srcH * 0.20);
    
    for ($y = -$srcH; $y < $srcH * 2; $y += $stepY) {
        for ($x = -$srcW; $x < $srcW * 2; $x += $stepX) {
            // Gunakan font bawaan jika tidak ada font file
            imagestring($source, 5, $x, $y, $text, $textColor);
        }
    }

    // Simpan hasil
    $ext = strtolower(pathinfo($outputPath, PATHINFO_EXTENSION));
    $result = false;
    
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $result = imagejpeg($source, $outputPath, 85);
            break;
        case 'png':
            $result = imagepng($source, $outputPath, 8);
            break;
        case 'webp':
            $result = imagewebp($source, $outputPath, 85);
            break;
        default:
            $result = imagejpeg($source, $outputPath, 85);
    }

    imagedestroy($source);
    imagedestroy($watermark);
    imagedestroy($wmResized);

    return $result;
}
