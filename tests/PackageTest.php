<?php

namespace jabsa\LaravelLogs\Tests;

use Orchestra\Testbench\TestCase;
use jabsa\LaravelLogs\LaravelLogsServiceProvider;
use jabsa\LaravelLogs\Facades\LaravelLogs;

class PackageTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelLogsServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'LaravelLogs' => LaravelLogs::class,
        ];
    }

    /** @test */
    public function it_can_run_the_hello_method()
    {
        $this->assertEquals('Hello from LaravelLogs!', LaravelLogs::hello());
    }
}