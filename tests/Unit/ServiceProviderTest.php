<?php

namespace InertiaResource\Tests\Unit;

use InertiaResource\InertiaResourceServiceProvider;
use InertiaResource\Tests\TestCase;

it('registers the service provider', function () {
    $provider = new InertiaResourceServiceProvider($this->app);
    
    expect($provider)->toBeInstanceOf(InertiaResourceServiceProvider::class);
});

it('merges config file', function () {
    $config = config('inertia-resource');
    
    expect($config)->toBeArray();
    expect($config)->toHaveKey('user_model');
    expect($config)->toHaveKey('resource_paths');
    expect($config)->toHaveKey('route_prefix');
});

