<?php

if (! function_exists('product_image_placeholder_url')) {
    function product_image_placeholder_url(): string
    {
        return base_url('assets/images/placeholders/product-placeholder.svg');
    }
}

if (! function_exists('normalize_product_image_value')) {
    function normalize_product_image_value(?string $value): ?string
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $normalized) === 1) {
            return $normalized;
        }

        return ltrim(str_replace('\\', '/', $normalized), '/');
    }
}

if (! function_exists('product_image_url')) {
    function product_image_url(?string $value): string
    {
        $normalized = normalize_product_image_value($value);
        if ($normalized === null) {
            return product_image_placeholder_url();
        }

        if (preg_match('#^https?://#i', $normalized) === 1) {
            return $normalized;
        }

        return base_url($normalized);
    }
}

if (! function_exists('product_upload_directory')) {
    function product_upload_directory(): string
    {
        return rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products';
    }
}
