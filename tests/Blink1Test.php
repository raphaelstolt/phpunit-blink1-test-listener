<?php

namespace Stolt\PHPUnit\Extension\Tests;

use Stolt\PHPUnit\Extension\Blink1;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use phpmock\MockBuilder;
use \RuntimeException;

class Blink1Test extends TestCase
{
    /**
     * @test
     * @dataProvider processProvider
     */
    public function factorsExpectedProcess($color, $expectedCommandLine)
    {
        $class = new \ReflectionClass('Stolt\PHPUnit\Extension\Blink1');
        $method = $class->getMethod('processFactory');
        $method->setAccessible(true);
        $process = $method->invokeArgs(
            new Blink1,
            [$color]
        );

        $this->assertEquals($expectedCommandLine, $process->getCommandLine());
    }

    /**
     * @return array
     */
    public static function processProvider(): array
    {
        return [
            'failure_process' => [
                'color' => Blink1::FAILURE_COLOR,
                'expected_command_line' => "'blink1-tool --rgb ff0000'",
            ],
            'success_process' => [
                'color' => Blink1::SUCCESS_COLOR,
                'expected_command_line' => "'blink1-tool --rgb 008000 --blink=3 > /dev/null 2>&1 &'",
            ],
            'incomplete_process' => [
                'color' => Blink1::INCOMPLETE_COLOR,
                'expected_command_line' => "'blink1-tool --rgb ffff00 --blink=3 > /dev/null 2>&1 &'",
            ],
        ];
    }

    /**
     * @test
     */
    public function factorsExpectedNonBlinkingFailureProcess()
    {
        $extension = new Blink1(10, false);
        $class = new \ReflectionClass($extension);
        $method = $class->getMethod('processFactory');
        $method->setAccessible(true);
        $process = $method->invokeArgs(
            $extension,
            [Blink1::FAILURE_COLOR]
        );

        $expectedCommandLine = "'blink1-tool --rgb ff0000 --blink=10 > /dev/null 2>&1 &'";
        $this->assertEquals($expectedCommandLine, $process->getCommandLine());
    }

    /**
     * @test
     * @dataProvider incompletePropertyProvider
     */
    public function incompleteTestStateIsDetected($property)
    {
        $extension = new Blink1(10, false);
        $class = new \ReflectionClass($extension);
        $method = $class->getMethod('isIncompleteTestResult');
        $property = $class->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($extension, ['testSomething']);

        $method->setAccessible(true);
        $isIncompleteTestState = $method->invokeArgs(
            $extension,
            []
        );

        $this->assertTrue($isIncompleteTestState);
    }

    /**
     * @return array
     */
    public static function incompletePropertyProvider(): array
    {
        return [
            'incompletes_property' => [
                'property' => 'incompletes',
            ],
            'skips_property' => [
                'property' => 'skips',
            ],
            'riskies_property' => [
                'property' => 'riskies',
            ],
        ];
    }

    /**
     * @test
     * @ticket 1 (https://github.com/raphaelstolt/phpunit-blink1-test-listener/issues/1)
     */
    public function throwsExpectedExceptionWhenBlinkToolNotPresent()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to locate blink1-tool.');

        $extension = new Blink1(10, false);
        $class = new \ReflectionClass($extension);
        $method = $class->getMethod('guardBlinkToolPresence');
        $method->setAccessible(true);
        $method->invokeArgs(
            $extension,
            [new Process(['non-existent-command'])]
        );
    }

    /**
     * @test
     * @ticket 2 (https://github.com/raphaelstolt/phpunit-blink1-test-listener/issues/2)
     */
    public function throwsExpectedExceptionWhenBlinkToolLedDeviceNotPresent()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to find a blink1 LED device.');

        $extension = new Blink1(10, false);

        $class = new \ReflectionClass($extension);
        $method = $class->getMethod('guardBlinkToolLedDevicePresence');
        $method->setAccessible(true);
        $method->invokeArgs(
            $extension,
            [new Process(['non-existent-command'])]
        );
    }

    /**
     * @test
     * @dataProvider guardProcessProvider
     */
    public function factorsExpectedGuardProcess($type, $expectedCommandLine)
    {
        $class = new \ReflectionClass('Stolt\PHPUnit\Extension\Blink1');
        $method = $class->getMethod('guardProcessFactory');
        $method->setAccessible(true);
        $process = $method->invokeArgs(
            new Blink1,
            [$type]
        );

        $this->assertEquals($expectedCommandLine, $process->getCommandLine());
    }

    /**
     * @return array
     */
    public static function guardProcessProvider(): array
    {
        return [
            'cli_guard_process' => [
                'type' => null,
                'expected_command_line' => "'blink1-tool --version'",
            ],
            'led_device_guard' => [
                'type' => Blink1::BLINK1_GUARD_LED_DEVICE,
                'expected_command_line' => "'blink1-tool --list'",
            ],
        ];
    }

    /**
     * @test
     * @dataProvider failurePropertyProvider
     */
    public function failureTestStateIsDetected($property)
    {
        $extension = new Blink1(10, false);
        $class = new \ReflectionClass($extension);
        $method = $class->getMethod('isFailureTestResult');
        $property = $class->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($extension, ['testSomething']);

        $method->setAccessible(true);
        $isFailureTestState = $method->invokeArgs(
            $extension,
            []
        );

        $this->assertTrue($isFailureTestState);
    }

    /**
     * @return array
     */
    public static function failurePropertyProvider(): array
    {
        return [
            'errors_property' => [
                'property' => 'errors',
            ],
            'failures_property' => [
                'property' => 'failures',
            ],
        ];
    }
}
