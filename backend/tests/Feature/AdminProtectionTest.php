<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminProtectionTest extends TestCase
{
    public function test_guest_cannot_open_admin_dashboard(): void { $this->get('/admin')->assertRedirect('/admin/login'); }
}
