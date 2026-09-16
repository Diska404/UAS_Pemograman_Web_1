<?php

namespace App\Helpers;

use App\Core\HttpException;

final class Validator
{
    public static function validate(array $input, array $fields): array
    {
        $clean = [];
        $errors = [];
        foreach ($fields as $name => $field) {
            $raw = $input[$name] ?? '';
            if (!is_scalar($raw) && $raw !== null) {
                $errors[$name] = 'Format nilai tidak valid.';
                continue;
            }
            $value = trim((string) $raw);
            if (($field['required'] ?? true) && $value === '') {
                $errors[$name] = 'Kolom ini wajib diisi.';
                continue;
            }
            $type = $field['type'] ?? 'text';
            if ($value !== '') {
                if (mb_strlen($value) > ($field['max'] ?? 255)) {
                    $errors[$name] = 'Nilai terlalu panjang.';
                }
                if ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$name] = 'Alamat email tidak valid.';
                }
                if (in_array($type, ['number', 'select'], true)) {
                    if (!preg_match('/^\d+$/D', $value) || (float) $value > 1000000000 || (int) $value < ($field['min'] ?? 1)) {
                        $errors[$name] = 'Masukkan bilangan bulat yang valid (maks. 1 miliar).';
                    } else {
                        $value = (int) $value;
                    }
                }
                if ($type === 'date') {
                    $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                    if (!$date || $date->format('Y-m-d') !== $value || $value < '2000-01-01' || $value > date('Y-m-d')) {
                        $errors[$name] = 'Tanggal harus valid antara 2000-01-01 dan hari ini.';
                    }
                }
            }
            $clean[$name] = $value === '' ? null : $value;
        }
        if ($errors) {
            throw new HttpException(422, 'Periksa kembali isian formulir.', $errors);
        }
        return $clean;
    }

    public static function password(mixed $password, mixed $confirmation = null): string
    {
        if (!is_string($password) || strlen($password) < 10 || strlen($password) > 72) {
            throw new HttpException(422, 'Password harus 10–72 karakter.', ['password' => 'Gunakan 10–72 karakter.']);
        }
        if ($confirmation !== null && $confirmation !== $password) {
            throw new HttpException(422, 'Konfirmasi password tidak cocok.', ['password_confirmation' => 'Konfirmasi harus sama.']);
        }
        return $password;
    }
}
