<?php
require_once __DIR__ . '/config.php';

function allowed_file($filename) {
    global $ALLOWED_EXTENSIONS;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $ALLOWED_EXTENSIONS);
}

function get_local_timestamp() {
    $dt = new DateTime('now', new DateTimeZone('Europe/Berlin'));
    return $dt->format('Y-m-d H:i:s');
}

function optimize_image($file_path) {
    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg'])) {
        $img = imagecreatefromjpeg($file_path);
        if ($img) {
            $width = imagesx($img);
            $height = imagesy($img);
            $scale = min(800/$width, 800/$height, 1);
            $new_w = (int)($width*$scale);
            $new_h = (int)($height*$scale);
            $thumb = imagecreatetruecolor($new_w,$new_h);
            imagecopyresampled($thumb,$img,0,0,0,0,$new_w,$new_h,$width,$height);
            imagejpeg($thumb,$file_path,85);
            imagedestroy($img); imagedestroy($thumb);
        }
    }
}
?>
