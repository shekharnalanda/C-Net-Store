<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AndroidAppDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_exposes_android_app_install_button(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Mobile App Install करें')
            ->assertSee(route('app.customer'))
            ->assertSee(route('app.seller'))
            ->assertSee(route('app.delivery'));
    }

    public function test_android_app_route_redirects_to_latest_official_release(): void
    {
        $this->get('/app/android')->assertRedirect(
            'https://github.com/shekharnalanda/C-Net-Store/releases/latest/download/C-Net-Store-Customer.apk'
        );
    }

    public function test_every_mobile_app_has_a_direct_download_route(): void
    {
        $this->get('/app/customer')->assertRedirect('https://github.com/shekharnalanda/C-Net-Store/releases/latest/download/C-Net-Store-Customer.apk');
        $this->get('/app/seller')->assertRedirect('https://github.com/shekharnalanda/C-Net-Store/releases/latest/download/C-Net-Store-Seller.apk');
        $this->get('/app/delivery')->assertRedirect('https://github.com/shekharnalanda/C-Net-Store/releases/latest/download/C-Net-Store-Delivery-Partner.apk');
    }
}
