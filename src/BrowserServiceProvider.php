<?php

namespace Asciito\Browser;

use Asciito\LaravelPackage\Package\Package;
use Asciito\LaravelPackage\Package\PackageServiceProvider;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeDriverService;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class BrowserServiceProvider extends PackageServiceProvider
{
    public function registeringPackage(): void
    {
        $this->app->scoped(Browser::class, function () {
            $options = tap(new ChromeOptions(), function ($options) {
                $options->addArguments(config('browser.arguments'));

                if ($binary = config('browser.binary')) {
                    $options->setBinary($binary);
                }
            });

            $capabilities = DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $options);

            if ($binary = config('browser.driver.binary')) {
                putenv('WEBDRIVER_CHROME_DRIVER='.$binary);
            }

            $service = ChromeDriverService::createDefaultService();

            $driver = ChromeDriver::start($capabilities, $service);

            return new Browser($driver);
        });
    }

    /**
     * {@inheritDoc}
     */
    protected function configurePackage(Package $package): void
    {
        $package
            ->setName('browser')
            ->withConfig();
    }

    protected function getNamespace(): string
    {
        return __NAMESPACE__;
    }
}
