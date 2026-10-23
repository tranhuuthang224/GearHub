<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test không cần build asset: tránh lỗi "Vite manifest not found" khi dựng view
        $this->withoutVite();
    }
}