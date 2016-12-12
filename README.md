# PHP Native Mock
Helper class to mock or redefine native PHP functions in unit tests. Works using the [UOPZ module](https://github.com/krakjoe/uopz), developed and tested against the master branch.

[![Build Status](https://ci.jacekk.co.uk/buildStatus/icon?job=PHP%20Native%20Mock)](https://ci.jacekk.co.uk/job/PHP%20Native%20Mock)

## Installation
The library can be included via composer by adding a custom repo and the project name
~~~json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/betterphp/php-native-mock.git"
        }
    ],
    "require-dev": {
        "betterphp/php-native-mock": "dev-master"
    }
}
~~~
This will pull from the master branch whenever you run `composer update`, proper versioning is on the to-do list.

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
