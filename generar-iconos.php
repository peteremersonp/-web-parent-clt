<?php

// Genera iconos PNG de la PWA: fondo esmeralda redondeado + "P" blanca (DejaVu Bold)
$out = '/home/peter/dev/web-parent-clt/public/pwa-icons';
@mkdir($out, 0775, true);

$font = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
if (! file_exists($font)) {
    $candidates = glob('/usr/share/fonts/**/DejaVuSans-Bold.ttf') ?: glob('/usr/share/fonts/**/*Bold*.ttf');
    $font = $candidates[0] ?? null;
}

function icon(int $size, string $font, string $path, float $padRatio = 0.15): void
{
    $img = imagecreatetruecolor($size, $size);

    // Fondo esmeralda con esquinas redondeadas (canvas transparente)
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    imagesavealpha($img, true);

    $emerald = imagecolorallocate($img, 16, 185, 129); // #10b981
    $radius = (int) ($size * 0.22);
    $x = 0;
    $y = 0;
    $w = $size;
    $h = $size;
    imagefilledrectangle($img, $x + $radius, $y, $x + $w - $radius, $y + $h, $emerald);
    imagefilledrectangle($img, $x, $y + $radius, $x + $w, $y + $h - $radius, $emerald);
    imagefilledellipse($img, $x + $radius, $y + $radius, $radius * 2, $radius * 2, $emerald);
    imagefilledellipse($img, $x + $w - $radius, $y + $radius, $radius * 2, $radius * 2, $emerald);
    imagefilledellipse($img, $x + $radius, $y + $h - $radius, $radius * 2, $radius * 2, $emerald);
    imagefilledellipse($img, $x + $w - $radius, $y + $h - $radius, $radius * 2, $radius * 2, $emerald);

    // "P" blanca centrada
    if ($font && function_exists('imagettftext')) {
        $fontSize = $size * 0.52;
        $bbox = imagettfbbox($fontSize, 0, $font, 'P');
        $textW = abs($bbox[4] - $bbox[0]);
        $textH = abs($bbox[5] - $bbox[1]);
        $tx = (int) (($size - $textW) / 2 - $bbox[0]);
        $ty = (int) (($size + $textH) / 2 - $bbox[1]);
        imagettftext($img, $fontSize, 0, $tx, $ty, imagecolorallocate($img, 255, 255, 255), $font, 'P');
    } else {
        imagestring($img, 5, (int) ($size * 0.42), (int) ($size * 0.4), 'P', imagecolorallocate($img, 255, 255, 255));
    }

    imagepng($img, $path, 6);
    imagedestroy($img);
    echo ' generado: '.$path.' ('.filesize($path).' bytes)'.PHP_EOL;
}

icon(192, $font, $out.'/icon-192.png');
icon(512, $font, $out.'/icon-512.png');
icon(512, $font, $out.'/icon-512-maskable.png', 0.25); // más margen para maskable
echo 'iconos listos'.PHP_EOL;
