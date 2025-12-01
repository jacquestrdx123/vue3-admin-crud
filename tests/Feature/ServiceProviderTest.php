<?php

namespace InertiaResource\Tests\Feature;

use InertiaResource\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class ServiceProviderTest extends TestCase
{
    public function test_config_is_merged(): void
    {
        $this->assertNotNull(Config::get('inertia-resource.user_model'));
        $this->assertEquals(\App\Models\User::class, Config::get('inertia-resource.user_model'));
    }

    public function test_config_can_be_published(): void
    {
        $this->artisan('vendor:publish', ['--tag' => 'inertia-resource-config'])
            ->assertSuccessful();
    }
}

