<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create the test application and refuse to run against non-SQLite databases.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        if ($app['config']->get('database.default') !== 'sqlite') {
            throw new RuntimeException(
                'Test suite refused to run against non-SQLite database connection [' . $app['config']->get('database.default') . ']. Clear stale config cache (php artisan config:clear) before running tests.'
            );
        }

        return $app;
    }
}
