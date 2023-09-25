<?php

return [
    /*
    |--------------------------------------------------------------------------
    | The Browser Arguments
    |--------------------------------------------------------------------------
    |
    | This option is an array with all the arguments that are going to be pass
    | to the Browser instance everytime.
    |
    | To se the full list of options, please consider visiting the url:
    | https://peter.sh/experiments/chromium-command-line-switches/
    |
    */
    'arguments' => [
        '--headless=new',
        '--disable-gpu'
    ],
/*
    |--------------------------------------------------------------------------
    | The Browser Binary Path
    |--------------------------------------------------------------------------
    |
    | This option is the absolute path to Chrome Browser binary, if none is
    | provided, we will assume the default installation location.
    |
    */
    'binary' => env('BROWSER_BINARY'),

    /*
    |--------------------------------------------------------------------------
    | Driver Options
    |--------------------------------------------------------------------------
    |
    | This option holds the information to connect with the WebDriver, so if
    | no option is provided, we will assume the defaults of the WebDriver.
    |
    | The defaults are:
    | - url  = 'localhost'
    | - port = 9515
    | - assume the 'chromedriver' binary in on the $PATH
    |
    */
    'driver' => [
        'url' => null,
        'port' => null,
        'binary' => env('DRIVER_BINARY')
    ],
];