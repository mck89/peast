<?php
namespace Peast\test;

if (class_exists("\PHPUnit\Framework\TestCase")) {
    abstract class TestCaseBase extends \PHPUnit\Framework\TestCase{}
} else {
    abstract class TestCaseBase extends \PHPUnit_Framework_TestCase{}
}