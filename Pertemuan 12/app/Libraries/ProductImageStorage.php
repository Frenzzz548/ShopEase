<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;
use DomainException;
use RuntimeException;

class ProductImageStorage
{
    public const PREFIX = 'uploads/products/';
    public const MAX_FILES = 4;
    public const MAX_KIB = 2048;
    public const MAX_DIMENSION = 4096;

    private const MIME = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];

    public function isUploadPath(string $path): bool
    {
        return preg_match('~\Auploads/products/[a-f0-9]{32}\.(?:jpg|png|webp)\z~', $path) === 1;
    }

    public function mime(string $path): string
    {
        return self::MIME[pathinfo($path, PATHINFO_EXTENSION)];
    }

    public function file(string $path): ?string
    {
        if (! $this->isUploadPath($path)) {
            return null;
        }
        try {
            $file = $this->directory() . DIRECTORY_SEPARATOR . basename($path);
        } catch (RuntimeException) {
            return null;
        }

        return ! is_link($file) && is_file($file) && is_readable($file) ? $file : null;
    }

    public function store(UploadedFile $upload): string
    {
        $mime = $upload->getMimeType();
        $extension = array_search($mime, self::MIME, true);
        $clientExtension = strtolower($upload->getClientExtension());
        $clientExtension = $clientExtension === 'jpeg' ? 'jpg' : $clientExtension;
        $contents = file_get_contents($upload->getTempName());
        $metadata = $contents === false ? false : @getimagesizefromstring($contents);
        if ($extension === false || $clientExtension !== $extension || $metadata === false || $metadata['mime'] !== $mime) {
            throw new DomainException('Ekstensi dan isi gambar harus sesuai dengan format JPEG, PNG, atau WebP.');
        }

        // Encode decoded pixels only, discarding metadata and any trailing input.
        $image = @imagecreatefromstring($contents);
        if ($image === false) {
            throw new DomainException('File gambar rusak atau tidak dapat dibaca.');
        }
        imagepalettetotruecolor($image);
        imagesavealpha($image, true);

        $directory = $this->directory(true);
        $name = bin2hex(random_bytes(16)) . '.' . $extension;
        $target = $directory . DIRECTORY_SEPARATOR . $name;
        $stream = @fopen($target, 'xb');
        if ($stream === false) {
            throw new RuntimeException('File upload tidak dapat dibuat.');
        }

        $encoded = false;
        try {
            $encoded = match ($extension) {
                'jpg' => @imagejpeg($image, $stream, 90),
                'png' => @imagepng($image, $stream),
                'webp' => @imagewebp($image, $stream, 90),
            } && ftell($stream) > 0;
        } finally {
            fclose($stream);
            if (! $encoded && ! $this->remove(self::PREFIX . $name)) {
                log_message('error', 'Gagal membersihkan upload gambar {path}.', ['path' => self::PREFIX . $name]);
            }
        }
        if (! $encoded) {
            throw new RuntimeException('Gambar tidak dapat disimpan.');
        }

        return self::PREFIX . $name;
    }

    public function remove(string $path): bool
    {
        if (! $this->isUploadPath($path)) {
            return false;
        }
        $file = $this->directory() . DIRECTORY_SEPARATOR . basename($path);

        return ! is_link($file) && (! file_exists($file) || @unlink($file));
    }

    private function directory(bool $create = false): string
    {
        $root = realpath(WRITEPATH);
        $parent = $root . DIRECTORY_SEPARATOR . 'uploads';
        $directory = $parent . DIRECTORY_SEPARATOR . 'products';
        if ($root === false || is_link($parent) || is_link($directory)) {
            throw new RuntimeException('Lokasi upload tidak valid.');
        }
        if ($create && ! is_dir($directory) && ! @mkdir($directory, 0750, true) && ! is_dir($directory)) {
            throw new RuntimeException('Direktori upload tidak dapat dibuat.');
        }
        if (is_dir($directory) && realpath($directory) !== $directory) {
            throw new RuntimeException('Lokasi upload tidak valid.');
        }

        return $directory;
    }
}
