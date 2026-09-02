<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = [
            [
                'loc' => url('/'),
                'lastmod' => now()->toDateString(),
                'priority' => '1.0',
            ],
            [
                'loc' => url('/shop'),
                'lastmod' => now()->toDateString(),
                'priority' => '0.9',
            ],
        ];

        $businessQuery = Business::query();

        if ($this->applyPublicFilter($businessQuery, 'businesses')) {
            $businessQuery->orderBy('id')->each(
                function (Business $business) use (&$urls): void {
                    $urls[] = [
                        'loc' => url('/stores/'.$business->getRouteKey()),
                        'lastmod' => optional($business->updated_at)
                            ?->toDateString() ?? now()->toDateString(),
                        'priority' => '0.8',
                    ];
                }
            );
        }

        $productQuery = Product::query();

        if ($this->applyPublicFilter($productQuery, 'products')) {
            $productQuery->orderBy('id')->each(
                function (Product $product) use (&$urls): void {
                    $urls[] = [
                        'loc' => url('/products/'.$product->getRouteKey()),
                        'lastmod' => optional($product->updated_at)
                            ?->toDateString() ?? now()->toDateString(),
                        'priority' => '0.7',
                    ];
                }
            );
        }

        $xml = view('seo.sitemap', compact('urls'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function applyPublicFilter(
        Builder $query,
        string $table
    ): bool {
        if (Schema::hasColumn($table, 'approval_status')) {
            $query->where('approval_status', 'approved');
            return true;
        }

        if (Schema::hasColumn($table, 'verification_status')) {
            $query->whereIn(
                'verification_status',
                ['approved', 'verified']
            );
            return true;
        }

        if (Schema::hasColumn($table, 'is_active')) {
            $query->where('is_active', true);
            return true;
        }

        if (Schema::hasColumn($table, 'status')) {
            $query->whereIn(
                'status',
                ['active', 'approved', 'published']
            );
            return true;
        }

        // Unknown visibility structure: do not expose records.
        $query->whereRaw('1 = 0');

        return false;
    }
}
