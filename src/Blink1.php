<?php

namespace Stolt\PHPUnit\TestListener;

use PHPUnit_Framework_TestListener as PHPUnitTestListenerInterface;
use PHPUnit_Framework_Test as Test;
use PHPUnit_Framework_TestSuite as TestSuite;
use PHPUnit_Framework_AssertionFailedError as AssertionFailedError;
use PHP_Timer as Timer;
use \Exception;
use \RuntimeException;
use Symfony\Component\Process\Process;

class Blink1 implements PHPUnitTestListenerInterface
{
    const FAILURE_COLOR = 'ff0000';
    const SUCCESS_COLOR = '008000';
    const INCOMPLETE_COLOR = 'ffff00';

    private $errors = [];
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

    public function addError(Test $test, Exception $e, $time)
    {
        $this->errors[] = $test->getName();
    }

    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
        $this->failures[] = $test->getName();
    }

    public function addIncompleteTest(Test $test, Exception $e, $time)
    {
        $this->incompletes[] = $test->getName();
    }

    public function addSkippedTest(Test $test, Exception $e, $time)
    {
        $this->skips[] = $test->getName();
    }

    public function addRiskyTest(Test $test, Exception $e, $time)
    {
        $this->riskies[] = $test->getName();
    }

    public function startTest(Test $test)
    {
    }

    public function endTest(Test $test, $time)
    {
        $this->tests[] = [
            'name' => $test->getName(),
            'assertions' => $test->getNumAssertions()
        ];
    }

    public function startTestSuite(TestSuite $suite)
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
            $process->run();
        } catch (RuntimeException $e) {
            echo PHP_EOL . PHP_EOL . 'Warning from ' . __CLASS__ . ': ' . $e->getMessage();
        }
        $process->run();
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
     * @return Symfony\Component\Process\Process
     */
    protected function guardProcessFactory()
    {
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

    public function endTestSuite(TestSuite $suite)
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
