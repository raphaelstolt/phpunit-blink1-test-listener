# PHPUnit blink(1) test listener
![Test](https://github.com/raphaelstolt/phpunit-blink1-test-listener/workflows/Test/badge.svg)
[![Version](http://img.shields.io/packagist/v/stolt/phpunit-blink1-test-listener.svg?style=flat)](https://packagist.org/packages/stolt/phpunit-blink1-test-listener)
![PHP Version](http://img.shields.io/badge/php-8.1+-ff69b4.svg)

This package provides an implementation of the [PHPUnit_Framework_TestListener](https://phpunit.de/manual/current/en/extending-phpunit.html#extending-phpunit.PHPUnit_Framework_TestListener) interface interacting with a [blink(1)](https://blink1.thingm.com/) USB notification LED light. It provides you a fast, visual feedback loop while TDDing with PHPUnit.

With this test listener a failing PHPUnit test run will turn the LED light __red__, while a successful one will make it blink __green__, and while incomplete, skipped, or risky tests will make it blink __yellow__.

#### Preconditions
This package assumes that the [blink1-tool](https://github.com/todbot/blink1#blink1-tool) is installed on your system to enable the communication with your `blink(1)` LED light. On Mac OS, the targeted system of this package, this can be done easily via `brew`.
``` bash
brew install blink1
```

#### Installation via Composer
``` bash
composer require --dev stolt/phpunit-blink1-test-listener
```

#### Configuration
To use the blink(1) test listener with its __default__ configuration add the following to your `phpunit.xml(.dist)` file.
``` xml
<extensions>
  <extension class="Stolt\PHPUnit\Extension\Blink1" />
</extensions>
```

It's possible to configure the blink amount (default is three) of the test state colors. Furthermore it's also possible to overwrite the behavior of the failure test state (default is a permanently turned on LED until a test state transition happens) to uniflow with the other test states.

``` xml
<extensions>
  <extension class="Stolt\PHPUnit\Extension\Blink1">
    <parameter name="blink-amount" value="2"/>
    <parameter name="blink-on-failure" value="false"/>
  </extension>
</extensions>
```

#### Running tests

``` bash
composer test
```

#### License
This package is licensed under the MIT license. Please see [LICENSE](LICENSE.md) for more details.

#### Changelog
Please see [CHANGELOG](CHANGELOG.md) for more details.

#### Code of Conduct
Please see [CONDUCT](CONDUCT.md) for more details.

#### Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for more details.
