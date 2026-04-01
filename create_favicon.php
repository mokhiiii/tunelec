<?php
// Load the source image
$source = imagecreatefromjpeg('public/tunelec.jpg');

// Get the dimensions
$width = imagesx($source);
$height = imagesy($source);

// Create a square image that fits the logo
$size = min($width, $height);

// Create a new image with transparency
$icon = imagecreatetruecolor(32, 32);
imagealphablending($icon, false);
imagesavealpha($icon, true);
$transparent = imagecolorallocatealpha($icon, 0, 0, 0, 127);
imagefill($icon, 0, 0, $transparent);

// Copy and resize the logo
imagecopyresampled($icon, $source, 0, 0, 0, 0, 32, 32, $size, $size);

// Save as ICO file
$iconData = [];
$iconData[] = pack('v', 0);  // Reserved. Must always be 0
$iconData[] = pack('v', 1);  // Image type: 1 = icon, 2 = cursor
$iconData[] = pack('v', 1);  // Number of images

// Image entry
$iconData[] = pack('C', 32);  // Width
$iconData[] = pack('C', 32);  // Height
$iconData[] = pack('C', 0);   // Color palette size
$iconData[] = pack('C', 0);   // Reserved
$iconData[] = pack('v', 1);   // Color planes
$iconData[] = pack('v', 32);  // Bits per pixel
$imageSize = 32 * 32 * 4;     // 32x32 pixels, 4 bytes per pixel (RGBA)
$iconData[] = pack('V', $imageSize);  // Size of image data
$iconData[] = pack('V', 22);  // Offset of image data

// Create image data
ob_start();
imagepng($icon);
$pngData = ob_get_clean();

// Write the ICO file
$fp = fopen('public/favicon.ico', 'wb');
foreach ($iconData as $data) {
    fwrite($fp, $data);
}
fwrite($fp, $pngData);
fclose($fp);

// Clean up
imagedestroy($source);
imagedestroy($icon);

echo "Favicon created successfully!";
?>
