# PHPUnit blink(1) test listener
[![Build Status](https://travis-ci.org/raphaelstolt/phpunit-blink1-test-listener.svg?branch=master)](https://travis-ci.org/raphaelstolt/phpunit-blink1-test-listener)
[![Version](http://img.shields.io/packagist/v/stolt/phpunit-blink1-test-listener.svg?style=flat)](https://packagist.org/packages/stolt/phpunit-blink1-test-listener)
![PHP Version](http://img.shields.io/badge/php-5.6+-ff69b4.svg)

This package provides an implementation of the [PHPUnit_Framework_TestListener](https://phpunit.de/manual/current/en/extending-phpunit.html#extending-phpunit.PHPUnit_Framework_TestListener) interface interacting with a [blink(1)](https://blink1.thingm.com/) USB notification LED light. It provides you a fast, visual feedback loop while TDDing with PHPUnit.

With this test listener a failing PHPUnit test run will turn the LED light __red__, while a successful one will make it blink __green__, and while incomplete, skipped, or risky tests will make it blink __yellow__.

#### Preconditions
This package assumes that the [blink1-tool](https://github.com/todbot/blink1#blink1-tool) is installed on your system to enable the communication with your `blink(1)` LED light. On Mac OS, the targeted system of this package, this can be done easily via `brew`.
``` bash
$ brew install blink1
```

#### Installation via Composer
``` bash
$ composer require --dev stolt/phpunit-blink1-test-listener
```

#### Configuration
To use the blink(1) test listener with its __default__ configuration add the following to your `phpunit.xml(.dist)` file.
``` xml
<listeners>
  <listener class="Stolt\PHPUnit\TestListener\Blink1" />
</listeners>
```

It's possible to configure the blink amount (default is three) of the test state colors. Furthermore it's also possible to overwrite the behavior of the failure test state (default is a permanently turned on LED until a test state transition happens) to uniflow with the other test states.

``` xml
<listeners>
  <listener class="Stolt\PHPUnit\TestListener\Blink1">
    <arguments>
      <integer>2</integer><!-- Blink two times. -->
      <boolean>false</boolean><!-- Blink on failure. -->
    </arguments>
  </listener>
</listeners>
```

#### Running tests
``` bash
$ composer test
```

#### License
This package is licensed under the MIT license. Please see [LICENSE](LICENSE.md) for more details.

#### Changelog
Please see [CHANGELOG](CHANGELOG.md) for more details.

#### Code of Conduct
Please see [CONDUCT](CONDUCT.md) for more details.

#### Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for more details.
