<?php

namespace Asciito\Browser;

use Asciito\Browser\Concerns\InteractsWithElements;
use Asciito\Browser\Concerns\MakesAssertions;
use Asciito\Browser\Concerns\WaitsForElements;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Traits\Macroable;

class Browser
{
    use MakesAssertions;
    use InteractsWithElements;
    use WaitsForElements;
    use Macroable {
            __call as macroCall;
        }

    public static int $waitSeconds = 5;

    /**
     * @param  RemoteWebDriver  $driver The remote web driver instance.
     * @param  ElementResolver|null  $resolver The element resolver instance.
     */
    public function __construct(protected RemoteWebDriver $driver, protected ?ElementResolver $resolver = null)
    {
        $this->resolver = $resolver ?: new ElementResolver($driver);
    }

    /**
     * Visit the given URL
     */
    public function visit(string $url): static
    {
        $this->driver->navigate()->to($url);

        return $this;
    }

    /**
     * Refresh the page.
     */
    public function refresh(): static
    {
        $this->driver->navigate()->refresh();

        return $this;
    }

    /**
     * Navigate to the previous page.
     */
    public function back(): static
    {
        $this->driver->navigate()->back();

        return $this;
    }

    /**
     * Navigate to the next page.
     */
    public function forward(): static
    {
        $this->driver->navigate()->forward();

        return $this;
    }

    /**
     * Navigate to the "about:blank" page
     */
    public function blank(): static
    {
        $this->driver->navigate()->to('about:blank');

        return $this;
    }

    /**
     * Pause for the given amount of milliseconds.
     */
    public function pause(int $milliseconds): static
    {
        usleep($milliseconds * 1000);

        return $this;
    }

    /**
     * Ensure that jQuery is available on the page.
     */
    public function ensurejQueryIsAvailable(): void
    {
        if ($this->driver->executeScript('return window.jQuery == null')) {
            $this->driver->executeScript(file_get_contents(__DIR__.'/../bin/jquery.js'));
        }
    }

    /**
     * Pause for the given amount of milliseconds if the given condition is true.
     */
    public function pauseIf(bool $boolean, int $milliseconds): static
    {
        if ($boolean) {
            return $this->pause($milliseconds);
        }

        return $this;
    }

    /**
     * Pause for the given amount of milliseconds unless the given condition is true.
     */
    public function pauseUnless(bool $boolean, int $milliseconds): static
    {
        if (! $boolean) {
            return $this->pause($milliseconds);
        }

        return $this;
    }

    /**
     * Get the current URL that the browser is on.
     */
    public function getCurrentURL(): string
    {
        return $this->driver->getCurrentURL();
    }

    /**
     * Close the Browser
     */
    public function close(): void
    {
        $this->driver->quit();
    }

    public function __call($method, $parameters): mixed
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->resolver->{$method}(...$parameters);
    }
}
