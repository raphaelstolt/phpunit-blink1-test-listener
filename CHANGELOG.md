# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [v3.0.0] - 2024-01-02
### Changed
- Dropped support for eoled PHP version.
- Refactored Extension towards PHPUnit 10.

### Added
- Utilise PHPUnit's extension and hook system. Closes [#5](https://github.com/raphaelstolt/phpunit-blink1-test-listener/issues/5).

## [v2.0.0] - 2018-04-06
### Added
- Made test listener compatible with PHPUnit `7.x`.

### Changed
- Dropped PHP 5.6 and 7.0 support.

## [v1.1.0] - 2017-03-29
### Added
- Additional guard to check if `blink1-tool` CLI is available.
- Additional guard to check if `blink1-tool` LED device is available.

## v1.0.0 - 2017-01-30
- Initial release.

[Unreleased]: https://github.com/raphaelstolt/phpunit-blink1-test-listener/compare/v3.0.0...HEAD
[v3.0.0]: https://github.com/raphaelstolt/phpunit-blink1-test-listener/compare/v2.0.0...v3.0.0
[v2.0.0]: https://github.com/raphaelstolt/phpunit-blink1-test-listener/compare/v1.1.0...v2.0.0
[v1.1.0]: https://github.com/raphaelstolt/phpunit-blink1-test-listener/compare/v1.0.0...v1.1.0
