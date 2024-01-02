<?php

declare(strict_types = 1);

namespace Stolt\PHPUnit\Extension;

use PHPUnit\Runner\AfterIncompleteTestHook;
use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\AfterRiskyTestHook;
use PHPUnit\Runner\AfterSkippedTestHook;
use PHPUnit\Runner\AfterTestErrorHook;
use PHPUnit\Runner\AfterTestFailureHook;
use PHPUnit\Runner\AfterTestWarningHook;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class Blink1 implements Extension
{
    const FAILURE_COLOR = 'ff0000';
    const SUCCESS_COLOR = '008000';
    const INCOMPLETE_COLOR = 'ffff00';

    const BLINK1_GUARD_LED_DEVICE = 'led';

    private $errors = [];
    private $warnings = [];
    private $failures = [];
    private $incompletes = [];
    private $riskies = [];
    private $skips = [];
    private $turnOnFailure = true;
    private $blinkAmount = 3;

    /**
     * Initialize.
     *
     * @return void
     */
    public function __construct($blinkAmount = 3, $turnOnFailure = true)
    {
        $this->blinkAmount = $blinkAmount;
        $this->turnOnFailure = $turnOnFailure;
    }

    public function executeAfterTestError(string $test, string $message, float $time): void
    {
        $this->errors[] = $test;
    }

    public function executeAfterTestWarning(string $test, string $message, float $time): void
    {
        $this->warnings[] = $test;
    }

    public function executeAfterTestFailure(string $test, string $message, float $time): void
    {
        $this->failures[] = $test;
    }

    public function executeAfterIncompleteTest(string $test, string $message, float $time): void
    {
        $this->incompletes[] = $test;
    }

    public function executeAfterRiskyTest(string $test, string $message, float $time): void
    {
        $this->riskies[] = $test;
    }

    public function executeAfterSkippedTest(string $test, string $message, float $time): void
    {
        $this->skips[] = $test;
    }

    /**
     * @return boolean
     */
    protected function isFailureTestResult()
    {
        return count($this->errors) > 0 ||
               count($this->failures) > 0;
    }

    /**
     * @return boolean
     */
    protected function isIncompleteTestResult()
    {
        return count($this->errors) === 0 &&
               count($this->failures) === 0 &&
               (count($this->incompletes) > 0 ||
                count($this->skips) > 0 ||
                count($this->riskies) > 0);
    }

    /**
     * @param Symfony\Component\Process\Process $process The blink(1) process/command.
     */
    protected function blink(Process $process)
    {
        try {
            $this->guardBlinkToolPresence($this->guardProcessFactory());
            $this->guardBlinkToolLedDevicePresence(
                $this->guardProcessFactory(self::BLINK1_GUARD_LED_DEVICE)
            );
            $process->run();
        } catch (RuntimeException $e) {
            echo PHP_EOL . PHP_EOL . 'Warning from ' . __CLASS__ . ': ' . $e->getMessage();
        }
    }

    /**
     * @param Symfony\Component\Process\Process $process The blink(1) guard process/command.
     * @throws RuntimeException
     */
    protected function guardBlinkToolLedDevicePresence(Process $process)
    {
        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new RuntimeException('Unable to find a blink1 LED device.');
        }
    }

    /**
     * @param Symfony\Component\Process\Process $process The blink(1) guard process/command.
     * @throws RuntimeException
     */
    protected function guardBlinkToolPresence(Process $process)
    {
        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new RuntimeException('Unable to locate blink1-tool.');
        }
    }

    /**
     * @param  string $guard The type of the guard.
     * @return Symfony\Component\Process\Process
     */
    protected function guardProcessFactory($guard = null)
    {
        if ($guard === self::BLINK1_GUARD_LED_DEVICE) {
            return new Process(['blink1-tool --list']);
        }
        return new Process(['blink1-tool --version']);
    }

    /**
     * @param  string $color The test state color.
     * @return Symfony\Component\Process\Process
     */
    protected function processFactory($color)
    {
        $amount = $this->blinkAmount;
        if ($color === self::SUCCESS_COLOR
            || $color === self::INCOMPLETE_COLOR
            || $color === self::FAILURE_COLOR && $this->turnOnFailure === false
        ) {
            return new Process(["blink1-tool --rgb {$color} --blink={$amount} > /dev/null 2>&1 &"]);
        }

        if ($color === self::FAILURE_COLOR) {
            return new Process(["blink1-tool --rgb {$color}"]);
        }
    }

    public function executeAfterLastTest(): void
    {
        $resultColor = self::SUCCESS_COLOR;

        if ($this->isFailureTestResult()) {
            $resultColor = self::FAILURE_COLOR;
        }

        if ($this->isIncompleteTestResult()) {
            $resultColor = self::INCOMPLETE_COLOR;
        }

        $this->blink($this->processFactory($resultColor));
    }

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        if ($configuration->noOutput()) {
            return;
        }

        if ($parameters->has('blink-amount')) {
            $this->blinkAmount = (int) $parameters->get('blink-amount');
        }

        if ($parameters->has('blink-on-failure')) {
            $this->turnOnFailure = (bool) $parameters->get('blink-on-failure');
        }
    }
}
