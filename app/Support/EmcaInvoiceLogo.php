<?php

namespace App\Support;

class EmcaInvoiceLogo
{
    public static function base64(): ?string
    {
        $path = public_path('EmCa-Logo.png');

        if (! is_file($path)) {
            return null;
        }

        if (! extension_loaded('gd')) {
            return 'data:image/png;base64,'.base64_encode(file_get_contents($path));
        }

        $image = imagecreatefrompng($path);

        if ($image === false) {
            return null;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;
                $red = ($rgba >> 16) & 0xFF;
                $green = ($rgba >> 8) & 0xFF;
                $blue = $rgba & 0xFF;

                if ($alpha >= 120 || ($red < 40 && $green < 40 && $blue < 40)) {
                    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
                    imagesetpixel($image, $x, $y, $transparent);
                }
            }
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($png);
    }
}
