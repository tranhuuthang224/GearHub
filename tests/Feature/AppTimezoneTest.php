<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppTimezoneTest extends TestCase
{
    public function test_application_runs_on_vietnam_time(): void
    {
        $this->assertSame('Asia/Ho_Chi_Minh', config('app.timezone'));
        $this->assertSame(7 * 3600, now()->getOffset());
    }
}