<?php

namespace App\Helpers;

/**
 * Image upload and processing helper
 */
class ImageUpload
{
    private $uploadDir = 'public/uploads/images';
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $maxFileSize = 5242880; // 5MB in bytes
    private $maxWidth = 2000;
    private $maxHeight = 2000;

    public function __construct()
    {
        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Upload and process image file
     *
     * @param array $file $_FILES array element
     * @return array ['success' => bool, 'filename' => string, 'path' => string, 'error' => string]
     */
    public function upload($file)
    {
        try {
            // Validate file upload
            if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                return ['success' => false, 'error' => 'No file uploaded'];
            }

            // Check file size
            if ($file['size'] > $this->maxFileSize) {
                return ['success' => false, 'error' => 'File size exceeds 5MB limit'];
            }

            // Check file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $this->allowedTypes)) {
                return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP allowed'];
            }

            // Get image dimensions
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return ['success' => false, 'error' => 'Invalid image file'];
            }

            // Generate unique filename
            $extension = $this->getExtensionFromMime($mimeType);
            $filename = $this->generateUniqueFilename($extension);
            $filepath = $this->uploadDir . '/' . $filename;

            // Resize if too large
            if ($imageInfo[0] > $this->maxWidth || $imageInfo[1] > $this->maxHeight) {
                $this->resizeImage($file['tmp_name'], $filepath, $this->maxWidth, $this->maxHeight, $mimeType);
            } else {
                // Move uploaded file
                if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                    return ['success' => false, 'error' => 'Failed to save uploaded file'];
                }
            }

            Logger::info('Image uploaded successfully', ['filename' => $filename]);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $filepath,
                'url' => '/' . $filepath,
                'mime_type' => $mimeType,
                'size' => filesize($filepath)
            ];
        } catch (\Exception $e) {
            Logger::error('Image upload error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Upload failed: ' . $e->getMessage()];
        }
    }

    /**
     * Resize image to fit within max dimensions
     *
     * @param string $source Source file path
     * @param string $destination Destination file path
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     * @param string $mimeType Image MIME type
     * @return bool
     */
    private function resizeImage($source, $destination, $maxWidth, $maxHeight, $mimeType)
    {
        list($width, $height) = getimagesize($source);

        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        // Create image from source
        switch ($mimeType) {
            case 'image/jpeg':
                $srcImage = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $srcImage = imagecreatefrompng($source);
                break;
            case 'image/gif':
                $srcImage = imagecreatefromgif($source);
                break;
            case 'image/webp':
                $srcImage = imagecreatefromwebp($source);
                break;
            default:
                return false;
        }

        // Create new image
        $dstImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
            imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save resized image
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($dstImage, $destination, 90);
                break;
            case 'image/png':
                imagepng($dstImage, $destination, 9);
                break;
            case 'image/gif':
                imagegif($dstImage, $destination);
                break;
            case 'image/webp':
                imagewebp($dstImage, $destination, 90);
                break;
        }

        // Free memory
        imagedestroy($srcImage);
        imagedestroy($dstImage);

        return true;
    }

    /**
     * Generate unique filename
     *
     * @param string $extension File extension
     * @return string
     */
    private function generateUniqueFilename($extension)
    {
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        return $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Get file extension from MIME type
     *
     * @param string $mimeType
     * @return string
     */
    private function getExtensionFromMime($mimeType)
    {
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];

        return $mimeMap[$mimeType] ?? 'jpg';
    }

    /**
     * Delete uploaded image
     *
     * @param string $filename
     * @return bool
     */
    public function delete($filename)
    {
        $filepath = $this->uploadDir . '/' . $filename;

        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        return false;
    }
}
