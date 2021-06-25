<?php /** @noinspection PhpDeprecationInspection */

namespace Peast\test;

if (class_exists("\PHPUnit\Framework\TestCase")) {
    if (method_exists('\PHPUnit\Framework\TestCase', 'expectException')) {
        abstract class TestCaseBase extends \PHPUnit\Framework\TestCase{}
    } else {
        abstract class TestCaseBase extends \PHPUnit\Framework\TestCase{
            /**
             * Polyfill the expectException method for low PHPUnit versions.
             */
            public function expectException($exception)
            {
                if (method_exists('PHPUnit_Framework_TestCase', 'expectException')) {
                    parent::expectException($exception);
                } else {
                    $this->setExpectedException($exception);
                }
            }
        }
    }
} else {
    abstract class TestCaseBase extends \PHPUnit_Framework_TestCase{
        /**
         * Polyfill the expectException method for low PHPUnit versions.
         */
        public function expectException($exception)
        {
            if (method_exists('PHPUnit_Framework_TestCase', 'expectException')) {
                parent::expectException($exception);
            } else {
                $this->setExpectedException($exception);
            }
        }
    }
}
