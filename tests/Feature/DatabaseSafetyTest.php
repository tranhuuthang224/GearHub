<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSafetyTest extends TestCase
{
    /** Chặn RefreshDatabase xóa nhầm CSDL dev: bộ test phải chạy trên SQLite trong bộ nhớ. */
    public function test_tests_run_on_in_memory_sqlite(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
    }
}