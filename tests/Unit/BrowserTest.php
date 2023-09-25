<?php

use Asciito\Browser\Browser;
use Facebook\WebDriver\Remote\RemoteWebDriver;

beforeEach(function () {
    $this->driver = Mockery::mock(RemoteWebDriver::class);

    $this->browser = new Browser($this->driver);
});

afterEach(function () {
    Mockery::close();
});

test('visit', function () {
    $this->driver->shouldReceive('navigate->to')
        ->with('https://laravel.com');

    $this->driver->shouldReceive('getTitle')
        ->andReturn('Laravel - The PHP Framework For Web Artisans');

    $this->driver->shouldReceive('getCurrentURL')
        ->andReturn('https://laravel.com');

    $browser = new Browser($this->driver);

    $browser->visit('https://laravel.com');

    expect($browser->getCurrentURL())->toBe('https://laravel.com');
    $browser->assertTitle('Laravel - The PHP Framework For Web Artisans');
});

test('blank page', function () {
    $this->driver->shouldReceive('navigate->to')
        ->with('about:blank');

    $this->driver->shouldReceive('getTitle')
        ->andReturn('about:blank');

    $this->driver->shouldReceive('navigate->to')->with('about:blank');
    $browser = new Browser($this->driver);

    $browser->blank();
    $browser->assertTitle('about:blank');
});


test('refresh method', function () {
    $this->driver->shouldReceive('navigate->refresh')->once();
    $browser = new Browser($this->driver);

    $browser->refresh();
});