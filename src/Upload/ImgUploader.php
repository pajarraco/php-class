<?php

declare(strict_types=1);

namespace PhpClass\Upload;

use RuntimeException;

final class ImgUploader
{
    /** Maps the actual (sniffed) MIME type of an upload to the GD function that reads it. */
    private const CREATORS = [
        'image/jpeg' => 'imagecreatefromjpeg',
        'image/png' => 'imagecreatefrompng',
        'image/gif' => 'imagecreatefromgif',
    ];

    /** Path to store the untouched original upload; null to skip saving it. */
    public ?string $pathOriginal = null;

    /** Path to store the resized image. */
    public string $pathImages = '../image/productos';

    /** Path to store the resized thumbnail; null to skip generating one. */
    public ?string $pathThumbs = '../image/productos';

    public int $imageWidth = 200;
    public int $thumbWidth = 50;

    /** Whether to restrict uploads to allowedExtensions. */
    public bool $limitExtensions = true;

    /** @var string[] */
    public array $allowedExtensions = ['gif', 'jpg', 'jpeg', 'png'];

    /** Filename of the most recently uploaded image (set after upload()). */
    public string $filename = '';

    public function __construct()
    {
        foreach ([$this->pathOriginal, $this->pathThumbs, $this->pathImages] as $path) {
            if ($path !== null && !is_writable($path)) {
                throw new RuntimeException("The directory ({$path}) is not writable.");
            }
        }
    }

    /**
     * Resizes an uploaded image (and optionally a thumbnail), saving both
     * under a random filename, and returns an HTML snippet linking to them.
     *
     * @param array{name?: string, type?: string, tmp_name?: string, size?: int, error?: int} $file
     *        A single entry from $_FILES, e.g. $_FILES['img_file'].
     *
     * @throws RuntimeException if no file was uploaded, the extension isn't
     *         allowed, or the file isn't actually a readable image.
     */
    public function upload(array $file): ?string
    {
        $tmpName = $file['tmp_name'] ?? '';
        $originalName = $file['name'] ?? '';
        $size = $file['size'] ?? 0;

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new RuntimeException('No file was uploaded.');
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($this->limitExtensions && !in_array($extension, $this->allowedExtensions, true)) {
            throw new RuntimeException('Only image files are allowed.');
        }

        if ($size <= 0) {
            return null;
        }

        // Validate the file's actual content, not the client-supplied
        // $file['type'], which is trivially spoofable.
        $imageInfo = getimagesize($tmpName);
        if ($imageInfo === false || !isset(self::CREATORS[$imageInfo['mime']])) {
            throw new RuntimeException('The uploaded file is not a valid image.');
        }

        $creator = self::CREATORS[$imageInfo['mime']];
        $sourceImage = $creator($tmpName);
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        [$newWidth, $newHeight] = $this->scale($width, $height, $this->imageWidth);
        [$thumbWidth, $thumbHeight] = $this->scale($width, $height, $this->thumbWidth);

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        $resizedThumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

        imagecopyresized($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagecopyresized($resizedThumb, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

        $randomName = bin2hex(random_bytes(8));
        $thumbFilename = "{$randomName}_thumb.{$extension}";
        $imageFilename = "{$randomName}.{$extension}";

        if ($this->pathOriginal !== null) {
            imagejpeg($sourceImage, "{$this->pathOriginal}/{$originalName}");
        }

        if ($this->pathThumbs !== null) {
            imagejpeg($resizedThumb, "{$this->pathThumbs}/{$thumbFilename}");
        }

        imagejpeg($resizedImage, "{$this->pathImages}/{$imageFilename}");

        imagedestroy($resizedImage);
        imagedestroy($resizedThumb);
        imagedestroy($sourceImage);

        $this->filename = $imageFilename;

        return sprintf(
            '<a href="%1$s/%2$s"><img src="%3$s/%4$s" width="%5$d" height="%6$d" alt="" /></a>',
            htmlspecialchars($this->pathImages, ENT_QUOTES),
            htmlspecialchars($imageFilename, ENT_QUOTES),
            htmlspecialchars((string) $this->pathThumbs, ENT_QUOTES),
            htmlspecialchars($thumbFilename, ENT_QUOTES),
            $thumbWidth,
            $thumbHeight,
        );
    }

    /**
     * Scales width/height proportionally so the longer side equals $target.
     *
     * @return array{0: int, 1: int} [width, height]
     */
    private function scale(int $width, int $height, int $target): array
    {
        $ratio = $width / $height;

        if ($ratio > 1) {
            return [$target, (int) round($target / $ratio)];
        }

        return [(int) round($target * $ratio), $target];
    }
}
