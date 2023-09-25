<?php

namespace Asciito\Browser\Concerns;

use Asciito\Browser\Exceptions\ElementNotFoundException;
use Carbon\Carbon;
use Closure;
use Exception;
use Facebook\WebDriver\Exception\TimeoutException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait WaitsForElements
{
    /**
     * Wait for the given selector to become visible.
     *
     * @return $this
     *
     * @throws TimeoutException
     */
    public function waitFor(string $selector, int $seconds = null): static
    {
        $message = $this->formatTimeOutMessage('Waited %s seconds for selector on the URL', $selector);

        return $this->waitUsing($seconds, callback: function () use ($selector) {
            return $this->resolver->findOrFail($selector);
        }, message: $message);
    }

    /**
     * Wait for the given selector to be removed.
     *
     * @return $this
     *
     * @throws TimeoutException
     */
    public function waitUntilMissing(string $selector, int $seconds = null): static
    {
        $message = $this->formatTimeOutMessage('Waited %s seconds for removal of selector', $selector);

        return $this->waitUsing($seconds, callback: function () use ($selector) {
            try {
                $missing = ! $this->resolver->findOrFail($selector)->isDisplayed();
            } catch (ElementNotFoundException $e) {
                $missing = true;
            }

            return $missing;
        }, message: $message);
    }

    /**
     * Wait for the given text to be removed.
     *
     * @return $this
     *
     * @throws TimeoutException
     */
    public function waitUntilMissingText(string $text, int $seconds = null): static
    {
        $text = Arr::wrap($text);

        $message = $this->formatTimeOutMessage('Waited %s seconds for removal of text', implode("', '", $text));

        return $this->waitUsing($seconds, callback: function () use ($text) {
            return ! Str::contains($this->resolver->findOrFail('')->getText(), $text);
        }, message: $message);
    }

    /**
     * Wait for the given text to become visible.
     *
     * @return $this
     *
     * @throws TimeoutException
     */
    public function waitForText(string $text, int $seconds = null): static
    {
        $text = Arr::wrap($text);

        $message = $this->formatTimeOutMessage('Waited %s seconds for text', implode("', '", $text));

        return $this->waitUsing($seconds, callback: function () use ($text) {
            return Str::contains($this->resolver->findOrFail('')->getText(), $text);
        }, message: $message);
    }

    /**
     * Wait for an input field to become visible.
     *
     * @return $this
     *
     * @throws TimeoutException
     */
    public function waitForInput(string $field, int $seconds = null): static
    {
        return $this->waitFor("input[name='$field'], textarea[name='$field'], select[name='$field']", $seconds);
    }

    /**
     * Wait until an element is enabled.
     *
     * @return $this
     *
     * @throws TimeoutException
     */
    public function waitUntilEnabled(string $selector, int $seconds = null): static
    {
        $message = $this->formatTimeOutMessage('Waited %s seconds for element to be enabled', $selector);

        return $this->waitUsing($seconds, callback: function () use ($selector) {
            return $this->resolver->findOrFail($selector)->isEnabled();
        }, message: $message);
    }

    /**
     * Wait until an element is disabled.
     *
     * @return $this
     *
     * @throws TimeoutException
     */
    public function waitUntilDisabled(string $selector, int $seconds = null): static
    {
        $message = $this->formatTimeOutMessage('Waited %s seconds for element to be disabled', $selector);

        return $this->waitUsing($seconds, callback: function () use ($selector) {
            return ! $this->resolver->findOrFail($selector)->isEnabled();
        }, message: $message);
    }

    /**
     * Wait for the given callback to be true.
     *
     * @return $this
     *
     * @throws TimeoutException
     */
    public function waitUsing(int $seconds = null, int $interval = 100, Closure $callback = null, string $message = null): static
    {
        $seconds = $seconds ?: static::$waitSeconds;

        $this->pause($interval);

        $started = Carbon::now();

        do {
            $this->pause($interval);

            try {
                if ($callback && $callback()) {
                    break;
                }
            } catch (Exception $e) {
                //
            }

            if ($started->lt(Carbon::now()->subSeconds($seconds))) {
                throw new TimeoutException($message
                    ? sprintf($message, $seconds)
                    : "Waited {$seconds} seconds for callback."
                );
            }
        } while (true);

        return $this;
    }

    /**
     * Prepare custom TimeoutException message for sprintf().
     */
    protected function formatTimeOutMessage(string $message, string $expected): string
    {
        return $message.' ['.str_replace('%', '%%', $expected).'].';
    }
}
