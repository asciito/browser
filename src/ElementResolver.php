<?php

namespace Asciito\Browser;

use Asciito\Browser\Exceptions\ElementNotFoundException;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Traits\Macroable;

class ElementResolver
{
    use Macroable;

    /**
     * @param  RemoteWebDriver  $driver The remote web driver instance.
     */
    public function __construct(protected RemoteWebDriver $driver, protected string $prefix = 'body')
    {
        //
    }

    /**
     * Find an element by the given selector or return null
     *
     * @param  string  $selector The selector to match.
     * @return RemoteWebElement|null The element that matches the given selector.
     */
    public function find(string $selector): RemoteWebElement|null
    {
        try {
            return $this->findOrFail($selector);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Find an element by its ID CSS selector.
     *
     * @param  string  $selector The selector to match.
     * @return RemoteWebElement|null The element that matches the given selector.
     */
    public function findById(string $selector): RemoteWebElement|null
    {
        if (preg_match('/^#[\w\-:]+$/', $selector)) {
            try {
                return $this->driver->findElement(WebDriverBy::id(substr($selector, 1)));
            } catch (Exception) {
                return null;
            }
        }

        return null;
    }

    /**
     * Find an element by its name CSS selector.
     *
     * @param string $selector The selector to match.
     * @return RemoteWebElement The element that matches the given selector.
     * @throws ElementNotFoundException
     */
    public function findOrFail(string $selector): RemoteWebElement
    {
        if (! is_null($element = $this->findById($selector))) {
            return $element;
        }

        try {
            return $this->driver->findElement(
                WebDriverBy::cssSelector($this->format($selector))
            );
        } catch (Exception) {
            throw new ElementNotFoundException($selector, $this->driver);
        }
    }

    /**
     * Get the first element that matches any of the given selectors.
     *
     * @param  array  $selectors The selectors to match.
     * @return RemoteWebElement The first element that matches any of the given selectors.
     *
     * @throws ElementNotFoundException If none of the selectors match.
     */
    public function firstOrFail(array $selectors): RemoteWebElement
    {
        foreach ($selectors as $selector) {
            try {
                return $this->findOrFail($selector);
            } catch (ElementNotFoundException $e) {
                //
            }
        }

        // If none of the selectors match, throw an exception.
        throw $e;
    }

    /**
     * Get all elements that match the given selector.
     *
     * @param  string  $selector The selector to match.
     * @return array The elements that match the given selector.
     */
    public function all(string $selector): array
    {
        return $this->driver->findElements(
            WebDriverBy::cssSelector($this->format($selector))
        );
    }

    /**
     * Get the first inout element that matches any of the given "field".
     *
     * @param  string  $field The field to match.
     * @return RemoteWebElement The input element that matches the given "field".
     *
     * @throws ElementNotFoundException If the input element is not found.
     */
    public function resolveForTyping(string $field): RemoteWebElement
    {
        if (! is_null($element = $this->findById($field))) {
            return $element;
        }

        return $this->firstOrFail([
            "input[name='$field']",
            "input[id='$field']",
            "input[type='$field']",
            "textarea[name='$field']",
            "textarea[id='$field']",
            "textarea[type='$field']",
            $field,
        ]);
    }

    /**
     * Format the given selector with the current prefix
     */
    public function format(string $selector): string
    {
        return trim($this->prefix.' '.$selector);
    }
}
