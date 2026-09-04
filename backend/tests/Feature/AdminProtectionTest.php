<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminProtectionTest extends TestCase
{
    public function test_guest_cannot_open_admin_dashboard(): void { $this->get('/admin')->assertRedirect('/admin/login'); }

    public function test_guest_cannot_open_operations_pages(): void
    {
        foreach (['/admin/sellers', '/admin/orders', '/admin/customers', '/admin/delivery', '/admin/settlements', '/admin/support', '/admin/content'] as $path) {
            $this->get($path)->assertRedirect('/admin/login');
        }
    }

    public function test_guest_cannot_open_product_image_library(): void
    {
        $this->get('/admin/product-image-library')->assertRedirect('/admin/login');
    }

    public function test_guest_cannot_manage_product_catalog(): void
    {
        $this->get('/admin/products/export')->assertRedirect('/admin/login');
        $this->post('/admin/products/import')->assertRedirect('/admin/login');
        $this->post('/admin/products/activate-ready')->assertRedirect('/admin/login');
        $this->post('/admin/products/bulk-activate', ['product_ids' => [1]])->assertRedirect('/admin/login');
        $this->post('/admin/products/bulk-deactivate', ['product_ids' => [1]])->assertRedirect('/admin/login');
    }

    public function test_guest_cannot_use_seller_product_image_library_api(): void
    {
        $this->getJson('/api/v1/seller/product-image-library')->assertUnauthorized();
    }
}
