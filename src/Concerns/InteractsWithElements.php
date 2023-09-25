<?php

namespace Asciito\Browser\Concerns;

use Asciito\Browser\Exceptions\ElementNotFoundException;
use Facebook\WebDriver\Remote\RemoteWebElement;

trait InteractsWithElements
{
    /**
     * Get the element for the given selector.
     */
    public function element(string $selector): RemoteWebElement|null
    {
        return $this->resolver->find($selector);
    }

    /**
     * Get the elements for the given selector.
     *
     * @param  string  $selector The selector to match.
     * @return RemoteWebElement[] The elements for the given selector.
     */
    public function elements(string $selector): array
    {
        return $this->resolver->all($selector);
    }

    /**
     * Get the text for the given selector.
     *
     * @param  string  $selector The selector to match.
     * @return string The text for the given selector.
     *
     * @throws ElementNotFoundException
     */
    public function text(string $selector): string
    {
        return $this->resolver->findOrFail($selector)->getText();
    }

    public function type(string $selector, string $text): static
    {
        $this->resolver->resolveForTyping($selector)->sendKeys($text);

        return $this;
    }
}
