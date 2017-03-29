<?php

namespace Stolt\PHPUnit\TestListener\Tests;

use Stolt\PHPUnit\TestListener\Blink1 as PHPUnitBlink1TestListener;
use PHPUnit_Framework_TestCase as PHPUnit;
use Symfony\Component\Process\Process;
use phpmock\MockBuilder;
use \RuntimeException;

class Blink1Test extends PHPUnit
{
    /**
     * @test
     * @dataProvider processProvider
     */
    public function factorsExpectedProcess($color, $expectedCommandLine)
    {
        $class = new \ReflectionClass('Stolt\PHPUnit\TestListener\Blink1');
        $method = $class->getMethod('processFactory');
        $method->setAccessible(true);
        $process = $method->invokeArgs(
            new PHPUnitBlink1TestListener,
            [$color]
        );

        $this->assertEquals($expectedCommandLine, $process->getCommandLine());
    }

    /**
     * @return array
     */
    public function processProvider()
    {
        return [
            'failure_process' => [
                'color' => PHPUnitBlink1TestListener::FAILURE_COLOR,
                'expected_command_line' => 'blink1-tool --rgb ff0000',
            ],
            'success_process' => [
                'color' => PHPUnitBlink1TestListener::SUCCESS_COLOR,
                'expected_command_line' => 'blink1-tool --rgb 008000 --blink=3 > /dev/null 2>&1 &',
            ],
            'incomplete_process' => [
                'color' => PHPUnitBlink1TestListener::INCOMPLETE_COLOR,
                'expected_command_line' => 'blink1-tool --rgb ffff00 --blink=3 > /dev/null 2>&1 &',
            ],
        ];
    }

    /**
     * @test
     */
    public function factorsExpectedNonBlinkingFailureProcess()
    {
        $listener = new PHPUnitBlink1TestListener(10, false);
        $class = new \ReflectionClass($listener);
        $method = $class->getMethod('processFactory');
        $method->setAccessible(true);
        $process = $method->invokeArgs(
            $listener,
            [PHPUnitBlink1TestListener::FAILURE_COLOR]
        );

        $expectedCommandLine = 'blink1-tool --rgb ff0000 --blink=10 > /dev/null 2>&1 &';
        $this->assertEquals($expectedCommandLine, $process->getCommandLine());
    }

    /**
     * @test
     * @dataProvider incompletePropertyProvider
     */
    public function incompleteTestStateIsDetected($property)
    {
        $listener = new PHPUnitBlink1TestListener(10, false);
        $class = new \ReflectionClass($listener);
        $method = $class->getMethod('isIncompleteTestResult');
        $property = $class->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($listener, ['testSomething']);

        $method->setAccessible(true);
        $isIncompleteTestState = $method->invokeArgs(
            $listener,
            []
        );

        $this->assertTrue($isIncompleteTestState);
    }

    /**
     * @return array
     */
    public function incompletePropertyProvider()
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

        $listener = new PHPUnitBlink1TestListener(10, false);
        $class = new \ReflectionClass($listener);
        $method = $class->getMethod('guardBlinkToolPresence');
        $method->setAccessible(true);
        $method->invokeArgs(
            $listener,
            [new Process('non-existent-command')]
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

        $listener = new PHPUnitBlink1TestListener(10, false);

        $class = new \ReflectionClass($listener);
        $method = $class->getMethod('guardBlinkToolLedDevicePresence');
        $method->setAccessible(true);
        $method->invokeArgs(
            $listener,
            [new Process('non-existent-command')]
        );
    }

    /**
     * @test
     * @dataProvider guardProcessProvider
     */
    public function factorsExpectedGuardProcess($type, $expectedCommandLine)
    {
        $class = new \ReflectionClass('Stolt\PHPUnit\TestListener\Blink1');
        $method = $class->getMethod('guardProcessFactory');
        $method->setAccessible(true);
        $process = $method->invokeArgs(
            new PHPUnitBlink1TestListener,
            [$type]
        );

        $this->assertEquals($expectedCommandLine, $process->getCommandLine());
    }

    /**
     * @return array
     */
    public function guardProcessProvider()
    {
        return [
            'cli_guard_process' => [
                'type' => null,
                'expected_command_line' => 'blink1-tool --version',
            ],
            'led_device_guard' => [
                'type' => PHPUnitBlink1TestListener::BLINK1_GUARD_LED_DEVICE,
                'expected_command_line' => 'blink1-tool --list',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider failurePropertyProvider
     */
    public function failureTestStateIsDetected($property)
    {
        $listener = new PHPUnitBlink1TestListener(10, false);
        $class = new \ReflectionClass($listener);
        $method = $class->getMethod('isFailureTestResult');
        $property = $class->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($listener, ['testSomething']);

        $method->setAccessible(true);
        $isFailureTestState = $method->invokeArgs(
            $listener,
            []
        );

        $this->assertTrue($isFailureTestState);
    }

    /**
     * @return array
     */
    public function failurePropertyProvider()
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
