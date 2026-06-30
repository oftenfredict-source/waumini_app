<?php

namespace Tests\Unit;

use App\Support\WauminiBrand;
use Tests\TestCase;

class WauminiBrandTest extends TestCase
{
    public function test_logo_url_is_returned_from_config_without_filesystem_check(): void
    {
        config(['waumini.logo' => 'waumini_link_logo.png']);

        $this->assertSame(
            url('/waumini_link_logo.png'),
            WauminiBrand::logoUrl()
        );
    }

    public function test_logo_url_is_null_when_logo_path_is_empty(): void
    {
        config(['waumini.logo' => '']);

        $this->assertNull(WauminiBrand::logoUrl());
    }
}
