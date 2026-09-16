<?php

namespace App\Services;

use App\Core\HttpException;

final class UploadService
{
    public function store(?array $file): ?string
    {
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $bad = static fn () => new HttpException(422, 'Foto harus JPG, PNG, atau WEBP maksimal 2 MB.', ['foto' => 'Pilih foto valid, maksimal 2 MB dan 16 megapiksel.']);
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
            throw $bad();
        }
        $allowed = ['image/jpeg' => ['jpg','jpeg'], 'image/png' => ['png'], 'image/webp' => ['webp']];
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $size = @getimagesize($file['tmp_name']);
        if (!isset($allowed[$mime]) || !in_array($extension, $allowed[$mime], true) || !$size || $size[0] * $size[1] > 16000000) {
            throw $bad();
        }
        // Re-encode to remove metadata/polyglot content. Never expose uploaded originals.
        $image = @imagecreatefromstring(file_get_contents($file['tmp_name']));
        if (!$image) {
            throw $bad();
        }
        $name = bin2hex(random_bytes(20)) . '.png';
        if (!imagepng($image, BASE_PATH . '/storage/uploads/' . $name)) {
            throw new \RuntimeException('Unable to save image');
        }
        return $name;
    }

    public function remove(?string $name): void
    {
        if ($name && preg_match('/^[a-f0-9]{40}\.png$/D', $name)) {
            $path = BASE_PATH . '/storage/uploads/' . $name;
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
