<?php

declare(strict_types=1);

namespace App\Helpers;

class ImageOptimizer
{
    public static function generateWebP(string $sourcePath, ?string $destinationPath = null): ?string
    {
        if (!file_exists($sourcePath)) {
            return null;
        }
        
        if ($destinationPath === null) {
            $destinationPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $sourcePath);
        }
        
        $imageInfo = @getimagesize($sourcePath);
        if ($imageInfo === false) {
            return null;
        }
        
        $mimeType = $imageInfo['mime'];
        $image = null;
        
        switch ($mimeType) {
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($sourcePath);
                break;
            default:
                return null;
        }
        
        if ($image === false) {
            return null;
        }
        
        if (function_exists('imagewebp')) {
            $success = imagewebp($image, $destinationPath, 85);
            imagedestroy($image);
            
            return $success ? $destinationPath : null;
        }
        
        imagedestroy($image);
        return null;
    }
    
    public static function generateResponsiveImages(string $sourcePath, array $widths = [320, 640, 1024, 1920]): array
    {
        $images = [];
        
        if (!file_exists($sourcePath)) {
            return $images;
        }
        
        $imageInfo = @getimagesize($sourcePath);
        if ($imageInfo === false) {
            return $images;
        }
        
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        $sourceImage = null;
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = @imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = @imagecreatefromgif($sourcePath);
                break;
        }
        
        if ($sourceImage === false) {
            return $images;
        }
        
        $pathInfo = pathinfo($sourcePath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        
        foreach ($widths as $width) {
            if ($width > $originalWidth) {
                continue;
            }
            
            $height = (int)(($width / $originalWidth) * $originalHeight);
            
            $resizedImage = imagecreatetruecolor($width, $height);
            
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
                imagefilledrectangle($resizedImage, 0, 0, $width, $height, $transparent);
            }
            
            imagecopyresampled(
                $resizedImage,
                $sourceImage,
                0, 0, 0, 0,
                $width, $height,
                $originalWidth, $originalHeight
            );
            
            $newPath = $directory . '/' . $filename . '-' . $width . 'w.' . $extension;
            
            $saved = false;
            switch ($mimeType) {
                case 'image/jpeg':
                    $saved = imagejpeg($resizedImage, $newPath, 85);
                    break;
                case 'image/png':
                    $saved = imagepng($resizedImage, $newPath, 8);
                    break;
                case 'image/gif':
                    $saved = imagegif($resizedImage, $newPath);
                    break;
            }
            
            if ($saved) {
                $images[$width] = $newPath;
                
                if (function_exists('imagewebp')) {
                    $webpPath = $directory . '/' . $filename . '-' . $width . 'w.webp';
                    imagewebp($resizedImage, $webpPath, 85);
                }
            }
            
            imagedestroy($resizedImage);
        }
        
        imagedestroy($sourceImage);
        
        return $images;
    }
    
    public static function generateSrcset(string $imagePath, bool $includeWebP = true): string
    {
        $pathInfo = pathinfo($imagePath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        
        $widths = [320, 640, 1024, 1920];
        $srcset = [];
        
        foreach ($widths as $width) {
            $resizedPath = $directory . '/' . $filename . '-' . $width . 'w.' . $extension;
            if (file_exists($resizedPath)) {
                $srcset[] = $resizedPath . ' ' . $width . 'w';
            }
        }
        
        if (file_exists($imagePath)) {
            $imageInfo = @getimagesize($imagePath);
            if ($imageInfo !== false) {
                $srcset[] = $imagePath . ' ' . $imageInfo[0] . 'w';
            }
        }
        
        return implode(', ', $srcset);
    }
    
    public static function pictureElement(string $imagePath, string $alt = '', array $attributes = []): string
    {
        $pathInfo = pathinfo($imagePath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        
        $html = '<picture>';
        
        $webpSrcset = [];
        $widths = [320, 640, 1024, 1920];
        foreach ($widths as $width) {
            $webpPath = $directory . '/' . $filename . '-' . $width . 'w.webp';
            if (file_exists($webpPath)) {
                $webpSrcset[] = $webpPath . ' ' . $width . 'w';
            }
        }
        
        if (!empty($webpSrcset)) {
            $html .= '<source type="image/webp" srcset="' . implode(', ', $webpSrcset) . '">';
        }
        
        $fallbackSrcset = self::generateSrcset($imagePath, false);
        if (!empty($fallbackSrcset)) {
            $html .= '<source type="image/' . $extension . '" srcset="' . $fallbackSrcset . '">';
        }
        
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        
        $html .= '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($alt) . '"' . $attrString . '>';
        $html .= '</picture>';
        
        return $html;
    }
}
