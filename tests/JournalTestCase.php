<?php

namespace Scrn\Journal\Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Orchestra\Testbench\TestCase;
use Scrn\Journal\JournalServiceProvider;

class JournalTestCase extends TestCase
{
    use InteractsWithDatabase;

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
//
//        $app['config']->set('journal', [
//            'table' => 'activity_log',
//            ''
//        ]);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        $this->artisan('migrate', ['--database' => 'testbench']);

        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        $this->withFactories(__DIR__ . '/factories');
    }

    protected function getPackageProviders($app)
    {
        return [JournalServiceProvider::class];
    }
}
