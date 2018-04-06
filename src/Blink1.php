<?php

namespace Stolt\PHPUnit\TestListener;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use SebastianBergmann\Timer\Timer;
use \Throwable;
use \RuntimeException;
use Symfony\Component\Process\Process;

class Blink1 implements TestListener
{
    const FAILURE_COLOR = 'ff0000';
    const SUCCESS_COLOR = '008000';
    const INCOMPLETE_COLOR = 'ffff00';

    const BLINK1_GUARD_LED_DEVICE = 'led';

    private $errors = [];
    private $warnings = [];
    private $endedSuites = 0;
    private $failures = [];
    private $incompletes = [];
    private $riskies = [];
    private $skips = [];
    private $suites = [];
    private $tests = [];
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

    public function addError(Test $test, Throwable $e, float $time): void
    {
        $this->errors[] = $test->getName();
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->failures[] = $test->getName();
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->warnings[] = $test->getName();
    }

    public function addIncompleteTest(Test $test, Throwable $e, float $time): void
    {
        $this->incompletes[] = $test->getName();
    }

    public function addSkippedTest(Test $test, Throwable $e, float $time): void
    {
        $this->skips[] = $test->getName();
    }

    public function addRiskyTest(Test $test, Throwable $e, float $time): void
    {
        $this->riskies[] = $test->getName();
    }

    public function startTest(Test $test): void
    {
    }

    public function endTest(Test $test, float $time): void
    {
        $this->tests[] = [
            'name' => $test->getName(),
            'assertions' => $test->getNumAssertions()
        ];
    }

    public function startTestSuite(TestSuite $suite): void
    {
        if (count($this->suites) === 0) {
            Timer::start();
        }
        $this->suites[] = $suite->getName();
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
        } catch (RuntimeException $e) {
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
        } catch (RuntimeException $e) {
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
            return new Process('blink1-tool --list');
        }
        return new Process('blink1-tool --version');
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
            return new Process("blink1-tool --rgb {$color} --blink={$amount} > /dev/null 2>&1 &");
        }

        if ($color === self::FAILURE_COLOR) {
            return new Process("blink1-tool --rgb {$color}");
        }
    }

    public function endTestSuite(TestSuite $suite): void
    {
        $this->endedSuites++;

        if (count($this->suites) <= $this->endedSuites) {
            $resultColor = self::SUCCESS_COLOR;

            if ($this->isFailureTestResult()) {
                $resultColor = self::FAILURE_COLOR;
            }

            if ($this->isIncompleteTestResult()) {
                $resultColor = self::INCOMPLETE_COLOR;
            }

            $this->blink($this->processFactory($resultColor));
        }
    }
}
