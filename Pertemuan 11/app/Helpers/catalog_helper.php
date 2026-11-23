<?php

use App\Models\ProductModel;

function catalog_image_url(?string $image): ?string
{
    if ($image === null || ! preg_match('~\A(?:[a-z0-9_-]+/)*[a-z0-9_-]+\.(?:webp|png|jpe?g|gif|avif)\z~i', $image)) {
        return null;
    }

    $public = realpath(FCPATH);
    $file = realpath(FCPATH . $image);
    if ($public === false || $file === false || ! str_starts_with($file, $public . DIRECTORY_SEPARATOR) || ! is_file($file)) {
        return null;
    }

    return base_url($image);
}

function catalog_price(string|int|float $price): string
{
    $formatted = number_format((float) $price, 2, ',', '.');

    return 'Rp ' . (str_ends_with($formatted, ',00') ? substr($formatted, 0, -3) : $formatted);
}

function catalog_status_label(string $status): string
{
    return $status === ProductModel::STATUS_AVAILABLE ? 'Tersedia' : 'Tidak Tersedia';
}
