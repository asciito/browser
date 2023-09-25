<?php

use Asciito\Browser\ElementResolver;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;

beforeEach(function () {
   $this->driver = mock(RemoteWebDriver::class);
});

afterEach(function () {
    Mockery::close();
});

test('resolve for typing resolves by id', function () {
    $this->driver->shouldReceive('findElement')
        ->andReturn(mock(RemoteWebElement::class));

    $resolver = new ElementResolver($this->driver);

    expect($resolver->resolveForTyping('#foo'))->toBeInstanceOf(RemoteWebElement::class);
});

test('resolve for typing falls back to selectors without id', function () {
    $this->driver->shouldReceive('findElement')
        ->once()
        ->andReturn(mock(RemoteWebElement::class));

    $resolver = new ElementResolver($this->driver);

    expect($resolver->resolveForTyping('foo'))->toBeInstanceOf(RemoteWebElement::class);
});

test('find by id with colon', function () {
    $this->driver->shouldReceive('findElement')
        ->once()
        ->andReturn(mock(RemoteWebElement::class));

    $resolver = new ElementResolver($this->driver);

    $class = new \ReflectionClass($resolver);
    $method = $class->getMethod('findById');
    $method->setAccessible(true);
    $result = $method->invoke($resolver, '#frmLogin:strCustomerLogin_userID');

    expect($result)->toBeInstanceOf(RemoteWebElement::class);
});
