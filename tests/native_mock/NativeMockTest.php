<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use betterphp\native_mock\native_mock;

/**
 * @covers betterphp\native_mock\native_mock
 */
class NativeMockTest extends TestCase {

    use native_mock;

    public function setUp() {
        $this->nativeMockSetUp();
    }

    public function tearDown() {
        $this->nativeMockTearDown();
    }

    public function testRedefineNativeFunction() {
        $expected_value = 400;
        $function_name = 'strpos';

        $this->redefineFunction($function_name, function () use ($expected_value) {
            return $expected_value;
        });

        $this->assertSame($expected_value, $function_name('not used', 'not used'));
        $this->assertContains($function_name, $this->getRedefinedFunctions());
    }

    /**
     * @depends testRedefineNativeFunction
     */
    public function testRedefinedFunctionsResetAfterTest() {
        $test_string = 'such test, very string, wow.';
        $function_name = 'strpos';

        $this->assertSame(11, $function_name($test_string, 'very'));
        $this->assertNotContains($function_name, $this->getHookedFunctions());
    }

    public function testRedefineUserfunction() {
        $test_string = 'such test, very string, wow.';

        // Inner function are normally not a good idea but we need one to test with here
        function example_user_function() { //@codingStandardsIgnoreLine
            return 'nothing like the above';
        }

        $function_name = 'example_user_function';

        $this->redefineFunction($function_name, function () use ($test_string) {
            return $test_string;
        });

        $this->assertSame($test_string, $function_name());
        $this->assertContains($function_name, $this->getRedefinedFunctions());
    }

    public function testResetfunction() {
        $expected_value = 'crikey this isn\'t a file';
        $function_name = 'file_get_contents';

        $this->redefineFunction($function_name, function () use ($expected_value) {
            return $expected_value;
        });

        $this->assertSame($expected_value, $function_name(__FILE__));
        $this->assertContains($function_name, $this->getRedefinedFunctions());

        $this->resetFunction($function_name);

        $this->assertNotSame($expected_value, $function_name(__FILE__));
        $this->assertNotContains($function_name, $this->getRedefinedFunctions());
    }

    public function testRedefineNativeMethod() {
        $test_object = new \DateTime();
        $initial_value = $test_object->format('Y-m-d');
        $expected_value = 'this isn\'t a date!';

        $this->redefineMethod(\DateTime::class, 'format', function () use ($expected_value) {
            return $expected_value;
        });

        $actual_value = $test_object->format('Y-m-d');

        $this->assertNotSame($initial_value, $actual_value);
        $this->assertSame($expected_value, $actual_value);
        $this->assertContains([\DateTime::class, 'format'], $this->getRedefinedMethods());
    }

    public function testRedefinedMethodResetAfterTest() {
        $test_object = new \DateTime();

        $this->assertSame(date('Y-m-d'), $test_object->format('Y-m-d'));
        $this->assertNotContains([\DateTime::class, 'format'], $this->getRedefinedMethods());
    }

    public function testResetMethod() {
        $test_object = new \DateTime();
        $initial_value = $test_object->format('Y-m-d');
        $expected_value = 'very format, many date, wow.';

        $this->redefineMethod(\DateTime::class, 'format', function () use ($expected_value) {
            return $expected_value;
        });

        $this->assertSame($expected_value, $test_object->format('Y-m-d'));
        $this->assertContains([\DateTime::class, 'format'], $this->getRedefinedMethods());

        $this->resetMethod(\DateTime::class, 'format');

        $this->assertSame($initial_value, $test_object->format('Y-m-d'));
        $this->assertNotContains([\DateTime::class, 'format'], $this->getRedefinedMethods());
    }

    public function testHookNativeFunction() {
        $read_files = [];

        $function_name = 'file_get_contents';

        $this->setFunctionHook($function_name, function ($file_name) use (&$read_files) {
            $read_files[] = $file_name;
        });

        $function_name(__FILE__);

        $this->assertContains(__FILE__, $read_files);
        $this->assertContains($function_name, $this->getHookedFunctions());
    }

    public function testHookFunctionRemovedAfterTest() {
        $function_name = 'file_get_contents';

        $this->assertSame(null, uopz_get_hook($function_name));
        $this->assertNotContains($function_name, $this->getHookedFunctions());
    }

    public function testHookUserFunction() {
        $expected_value = 'What an interesting value';

        // Inner function are normally not a good idea but we need one to test with here
        function another_example_user_function() { //@codingStandardsIgnoreLine
            return 'nothing of much interest';
        }

        $function_name = 'another_example_user_function';

        $this->setFunctionHook($function_name, function () use ($expected_value) {
            define('ANOTHER_TEST_CONSTANT', $expected_value);
        });

        $function_name();

        $this->assertSame($expected_value, ANOTHER_TEST_CONSTANT);
        $this->assertContains($function_name, $this->getHookedFunctions());
    }

    public function testRemoveFunctionHook() {
        $expected_value = 'Some kind of value to expect';
        $test_variable = null;

        $function_name = 'file_get_contents';

        $this->setFunctionHook($function_name, function () use ($expected_value, &$test_variable) {
            $test_variable = $expected_value;
        });

        $function_name(__FILE__);

        $this->assertSame($expected_value, $test_variable);
        $this->assertContains($function_name, $this->getHookedFunctions());

        $test_variable = null;

        $this->removeFunctionHook($function_name);

        $function_name(__FILE__);

        $this->assertSame(null, $test_variable);
        $this->assertNotContains($function_name, $this->getHookedFunctions());
    }

    public function testHookMethod() {
        $test_object = new \DateTime();
        $formats_used = [];

        $this->setMethodHook(\DateTime::class, 'format', function ($format) use (&$formats_used) {
            $formats_used[] = $format;
        });

        $test_format = 'Y-m-d';

        $test_object->format($test_format);

        $this->assertContains($test_format, $formats_used);
        $this->assertContains([\DateTime::class, 'format'], $this->getHookedMethods());
    }

    public function testHookMethodRemovedAfterTest() {
        $method = [\DateTime::class, 'format'];

        $this->assertSame(null, uopz_get_hook($method[0], $method[1]));
        $this->assertNotContains($method, $this->getHookedMethods());
    }

    public function testRemoveMethodnHook() {
        $expected_value = 'Some kind of value to expect';
        $test_variable = null;

        $method = [\DateTime::class, 'format'];
        list($class_name, $method_name) = $method;

        $test_object = new $class_name();
        $test_format = 'Y-m-d';

        $this->setMethodHook($class_name, $method_name, function () use ($expected_value, &$test_variable) {
            $test_variable = $expected_value;
        });

        $test_object->$method_name($test_format);

        $this->assertSame($expected_value, $test_variable);
        $this->assertContains($method, $this->getHookedMethods());

        $test_variable = null;

        $this->removeMethodHook($class_name, $method_name);

        $test_object->$method_name($test_format);

        $this->assertSame(null, $test_variable);
        $this->assertNotContains($method, $this->getHookedMethods());
    }

}
