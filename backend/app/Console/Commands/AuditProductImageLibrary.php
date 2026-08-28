<?php

namespace App\Console\Commands;

use App\Models\ProductImageAsset;
use Illuminate\Console\Command;

class AuditProductImageLibrary extends Command
{
    protected $signature = 'store:image-library-audit';

    protected $description = 'Audit C-Net Store reusable product image assets';

    public function handle(): int
    {
        $assets = ProductImageAsset::query()->where('is_active', true)->orderBy('id')->get();
        $errors = [];

        $duplicateSlugs = ProductImageAsset::query()
            ->select('slug')->groupBy('slug')->havingRaw('COUNT(*) > 1')->pluck('slug');
        $duplicatePaths = ProductImageAsset::query()
            ->select('image_path')->groupBy('image_path')->havingRaw('COUNT(*) > 1')->pluck('image_path');

        foreach ($duplicateSlugs as $slug) {
            $errors[] = "Duplicate slug: {$slug}";
        }

        foreach ($duplicatePaths as $path) {
            $errors[] = "Duplicate image path: {$path}";
        }

        foreach ($assets as $asset) {
            $path = storage_path('app/public/'.$asset->image_path);

            if (! is_file($path)) {
                $errors[] = "Missing file: {$asset->image_path}";
                continue;
            }

            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'webp') {
                $errors[] = "Not WebP: {$asset->image_path}";
            }

            $size = filesize($path);
            if ($size === false || $size < 4096 || $size > 512000) {
                $errors[] = "Invalid file size: {$asset->image_path}";
            }

            $dimensions = getimagesize($path);
            if ($dimensions === false) {
                $errors[] = "Unreadable image: {$asset->image_path}";
            } else {
                [$width, $height] = $dimensions;
                if ($width < 800 || $height < 800 || abs($width - $height) > max($width, $height) * 0.05) {
                    $errors[] = "Invalid dimensions: {$asset->image_path} ({$width}x{$height})";
                }
            }

            $keywords = is_array($asset->keywords) ? $asset->keywords : [];
            if (count($keywords) < 2 || ! preg_match('/\p{Devanagari}/u', implode(' ', $keywords))) {
                $errors[] = "Missing bilingual keywords: {$asset->slug}";
            }
        }

        $groupCount = ProductImageAsset::query()->where('is_active', true)->distinct()->count('group_name');
        $this->table(
            ['Metric', 'Result'],
            [
                ['Active assets', $assets->count()],
                ['Active groups', $groupCount],
                ['Duplicate slugs', $duplicateSlugs->count()],
                ['Duplicate paths', $duplicatePaths->count()],
                ['Validation errors', count($errors)],
            ]
        );

        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->error($error);
            }
            $this->error('CNET_IMAGE_LIBRARY_AUDIT=FAIL');

            return self::FAILURE;
        }

        $this->info('CNET_IMAGE_LIBRARY_AUDIT=PASS');

        return self::SUCCESS;
    }
}
