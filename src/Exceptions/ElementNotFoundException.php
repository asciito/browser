<?php

namespace Asciito\Browser\Exceptions;

use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class ElementNotFoundException extends Exception
{
    public function __construct(protected string $selector, protected RemoteWebDriver $driver)
    {
        $message = 'The selector [%s] was not found in the current URL [%s]';

        parent::__construct(sprintf($message, $this->selector, $this->driver->getCurrentURL()));
    }

    /**
     * Get the current URL from where the element has no presence
     */
    public function getCurrentURL(): string
    {
        return $this->driver->getCurrentURL();
    }
}
