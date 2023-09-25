<?php

namespace Asciito\Browser\Concerns;

use Asciito\Browser\Exceptions\ElementNotFoundException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Illuminate\Support\Str;
use PHPUnit\Framework\Assert as PHPUnit;

trait MakesAssertions
{
    /**
     * Assert that the page title matches the given text.
     */
    public function assertTitle(string $title): static
    {
        PHPUnit::assertEquals(
            $title, $this->driver->getTitle(),
            "Expected title [$title] does not equal actual title [{$this->driver->getTitle()}]."
        );

        return $this;
    }

    /**
     * Assert that the page title contains the given text.
     */
    public function assertTitleContains(string $title): static
    {
        PHPUnit::assertTrue(
            Str::contains($this->driver->getTitle(), $title),
            "Did not see expected text [$title] within title [{$this->driver->getTitle()}]."
        );

        return $this;
    }

    /**
     * Assert that the given text is present on the page.
     */
    public function assertSee(string $text): static
    {
        return $this->assertSeeIn('', $text);
    }

    /**
     * Assert that the given text is not present on the page.
     */
    public function assertDontSee(string $text): static
    {
        return $this->assertDontSeeIn('', $text);
    }

    /**
     * Assert that the given text is present within the selector.
     */
    public function assertSeeIn(string $selector, string $text): static
    {
        $fullSelector = $this->resolver->format($selector);

        $element = $this->resolver->findOrFail($selector);

        PHPUnit::assertTrue(
            Str::contains($element->getText(), $text),
            "Did not see expected text [$text] within element [$fullSelector]."
        );

        return $this;
    }

    /**
     * Assert that the given text is not present within the selector.
     */
    public function assertDontSeeIn(string $selector, string $text): static
    {
        $fullSelector = $this->resolver->format($selector);

        $element = $this->resolver->findOrFail($selector);

        PHPUnit::assertFalse(
            Str::contains($element->getText(), $text),
            "Saw unexpected text [$text] within element [$fullSelector]."
        );

        return $this;
    }

    /**
     * Assert that any text is present within the selector.
     */
    public function assertSeeAnythingIn(string $selector): static
    {
        $fullSelector = $this->resolver->format($selector);

        $element = $this->resolver->findOrFail($selector);

        PHPUnit::assertTrue(
            $element->getText() !== '',
            "Saw unexpected text [''] within element [$fullSelector]."
        );

        return $this;
    }

    /**
     * Assert that no text is present within the selector.
     */
    public function assertSeeNothingIn(string $selector): static
    {
        $fullSelector = $this->resolver->format($selector);

        $element = $this->resolver->findOrFail($selector);

        PHPUnit::assertTrue(
            $element->getText() === '',
            "Did not see expected text [''] within element [$fullSelector]."
        );

        return $this;
    }

    /**
     * Assert that the given link is present on the page.
     */
    public function assertSeeLink(string $link): static
    {
        PHPUnit::assertTrue(
            $this->seeLink($link),
            "Did not see expected link [$link] on [{$this->getCurrentURL()}]."
        );

        return $this;
    }

    /**
     * Assert that the given link is not present on the page.
     */
    public function assertDontSeeLink(string $link): static
    {
        PHPUnit::assertFalse(
            $this->seeLink($link),
            "Saw unexpected link [$link]."
        );

        return $this;
    }

    public function seeLink(string $link): bool
    {
        $this->ensurejQueryIsAvailable();

        $link = str_replace("'", "\\\\'", $link);

        $script = <<<JS
            const link = jQuery.find(`a[href^='$link']`);
            return link.length > 0 && jQuery(link).is(':visible');
        JS;

        return $this->driver->executeScript($script);
    }

    /**
     * Assert that the given input field has the given value.
     */
    public function assertInputValue(string $field, string $value): static
    {
        PHPUnit::assertEquals(
            $value,
            $this->inputValue($field),
            "Expected value [$value] for the [$field] input does not equal the actual value [{$this->inputValue($field)}]."
        );

        return $this;
    }

    /**
     * Assert that the given input field does not have the given value.
     */
    public function assertInputValueIsNot(string $field, string $value): static
    {
        PHPUnit::assertNotEquals(
            $value,
            $this->inputValue($field),
            "Value [$value] for the [$field] input should not equal the actual value."
        );

        return $this;
    }

    /**
     * Get the value of the given input or text area field.
     */
    public function inputValue(string $field): string
    {
        $element = $this->resolver->resolveForTyping($field);

        return in_array($element->getTagName(), ['input', 'textarea'])
            ? $element->getAttribute('value')
            : $element->getText();
    }

    /**
     * Assert that the given input field is present.
     */
    public function assertInputPresent(string $field): static
    {
        $this->assertPresent(
            "input[name='$field'], textarea[name='$field'], select[name='$field']"
        );

        return $this;
    }

    /**
     * Assert that the given input field is not visible.
     */
    public function assertInputMissing(string $field): static
    {
        $this->assertMissing(
            "input[name='$field'], textarea[name='$field'], select[name='$field']"
        );

        return $this;
    }


    /**
     * Assert that the element matching the given selector has the given value.
     */
    public function assertValue(string $selector, string $value): static
    {
        $fullSelector = $this->resolver->format($selector);

        $this->ensureElementSupportsValueAttribute(
            $element = $this->resolver->findOrFail($selector),
            $fullSelector
        );

        $actual = $element->getAttribute('value');

        PHPUnit::assertEquals(
            $value,
            $actual,
            "Did not see expected value [$value] within element [$fullSelector]."
        );

        return $this;
    }

    /**
     * Assert that the element matching the given selector does not have the given value.
     */
    public function assertValueIsNot(string $selector, string $value): static
    {
        $fullSelector = $this->resolver->format($selector);

        $this->ensureElementSupportsValueAttribute(
            $element = $this->resolver->findOrFail($selector),
            $fullSelector
        );

        $actual = $element->getAttribute('value');

        PHPUnit::assertNotEquals(
            $value,
            $actual,
            "Saw unexpected value [{$value}] within element [{$fullSelector}]."
        );

        return $this;
    }

    /**
     * Ensure the given element supports the 'value' attribute.
     */
    public function ensureElementSupportsValueAttribute(RemoteWebElement $element, string $fullSelector): void
    {
        PHPUnit::assertTrue(in_array($element->getTagName(), [
            'textarea',
            'select',
            'button',
            'input',
            'li',
            'meter',
            'option',
            'param',
            'progress',
        ]), "This assertion cannot be used with the element [$fullSelector].");
    }

    /**
     * Assert that the element matching the given selector has the given value in the provided attribute.
     */
    public function assertAttribute(string $selector, string $attribute, $value): static
    {
        $fullSelector = $this->resolver->format($selector);

        $actual = $this->resolver->findOrFail($selector)->getAttribute($attribute);

        PHPUnit::assertNotNull(
            $actual,
            "Did not see expected attribute [{$attribute}] within element [{$fullSelector}]."
        );

        PHPUnit::assertEquals(
            $value,
            $actual,
            "Expected '$attribute' attribute [{$value}] does not equal actual value [$actual]."
        );

        return $this;
    }

    /**
     * Assert that the element matching the given selector contains the given value in the provided attribute.
     */
    public function assertAttributeContains(string $selector, string $attribute, string $value): static
    {
        $fullSelector = $this->resolver->format($selector);

        $actual = $this->resolver->findOrFail($selector)->getAttribute($attribute);

        PHPUnit::assertNotNull(
            $actual,
            "Did not see expected attribute [$attribute] within element [$fullSelector]."
        );

        PHPUnit::assertStringContainsString(
            $value,
            $actual,
            "Attribute '$attribute' does not contain [$value]. Full attribute value was [$actual]."
        );

        return $this;
    }

    /**
     * Assert that the element matching the given selector has the given value in the provided aria attribute.
     */
    public function assertAriaAttribute(string $selector, string $attribute, string $value): static
    {
        return $this->assertAttribute($selector, 'aria-'.$attribute, $value);
    }

    /**
     * Assert that the element matching the given selector has the given value in the provided data attribute.
     */
    public function assertDataAttribute(string $selector, string $attribute, string $value): static
    {
        return $this->assertAttribute($selector, 'data-'.$attribute, $value);
    }

    /**
     * Assert that the element matching the given selector is visible.
     */
    public function assertVisible(string $selector): static
    {
        $fullSelector = $this->resolver->format($selector);

        PHPUnit::assertTrue(
            $this->resolver->findOrFail($selector)->isDisplayed(),
            "Element [$fullSelector] is not visible."
        );

        return $this;
    }

    /**
     * Assert that the element matching the given selector is present.
     */
    public function assertPresent(string $selector): static
    {
        $fullSelector = $this->resolver->format($selector);

        PHPUnit::assertTrue(
            ! is_null($this->resolver->find($selector)),
            "Element [$fullSelector] is not present."
        );

        return $this;
    }

    /**
     * Assert that the element matching the given selector is not present in the source.
     */
    public function assertNotPresent(string $selector): static
    {
        $fullSelector = $this->resolver->format($selector);

        PHPUnit::assertTrue(
            is_null($this->resolver->find($selector)),
            "Element [$fullSelector] is present."
        );

        return $this;
    }

    /**
     * Assert that the element matching the given selector is not visible.
     */
    public function assertMissing(string $selector): static
    {
        $fullSelector = $this->resolver->format($selector);

        try {
            $missing = ! $this->resolver->findOrFail($selector)->isDisplayed();
        } catch (ElementNotFoundException $e) {
            $missing = true;
        }

        PHPUnit::assertTrue(
            $missing,
            "Saw unexpected element [$fullSelector]."
        );

        return $this;
    }
}