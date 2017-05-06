# PHP Native Mock
Helper class to mock or redefine native PHP functions in unit tests. Works using the [UOPZ module](https://github.com/krakjoe/uopz), developed and tested against the master branch.

[![Build Status](https://ci.jacekk.co.uk/buildStatus/icon?job=PHP%20Native%20Mock)](https://ci.jacekk.co.uk/job/PHP%20Native%20Mock)

This is largely a wrapper around the uopz functions with some extra bits thrown in to make it a bit easier in tests. It basically relies on dark magic and, like all magic, should only be used very carefully. Creating weird behaviour in applications is very easy, for example

~~~php
$this->redefineFunction('substr', function () {
    return 'Doge';
});
~~~

would have some very strange effects.

Most of the time when redefining a function sounds like a good idea - it's probably not, make sure there is no way a "normal" mocking approach won't work first.

## Installation
The library can be included via composer
~~~json
{
    "require-dev": {
        "betterphp/php-native-mock": "~1"
    }
}
~~~

## Documentation
Jenkins publishes a phpdoc [here](https://ci.jacekk.co.uk/view/Websites/job/PHP%20Native%20Mock/API_Docs/classes/betterphp.native_mock.native_mock.html)

## Testing
We use phpcs and phpunit for testing, run both before commiting anything
~~~
./vendor/bin/phpcs -p --standard=./ruleset.xml .
~~~
~~~
./vendor/bin/phpunit -c ./phpunit.xml
~~~

phpunit will do code coverage checking which requires xdebug, if it's not installed this will fail gracefully - not to worry.

A report of the test coverage is published [here by Jenkins](https://ci.jacekk.co.uk/job/PHP%20Native%20Mock/HTML_Code_Coverage/index.html)
