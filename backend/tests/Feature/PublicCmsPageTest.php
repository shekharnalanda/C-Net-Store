<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCmsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_page_is_public_and_escaped(): void
    {
        $page = CmsPage::create([
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'content' => 'Safe policy <script>alert(1)</script>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->get('/pages/'.$page->slug)
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_draft_page_is_not_public(): void
    {
        $page = CmsPage::create([
            'title' => 'Draft',
            'slug' => 'draft',
            'content' => 'Not ready',
            'is_published' => false,
        ]);

        $this->get('/pages/'.$page->slug)->assertNotFound();
    }
}
