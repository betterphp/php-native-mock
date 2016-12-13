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

        $this->redefineFunction('strpos', function () use ($expected_value) {
            return $expected_value;
        });

        $this->assertSame($expected_value, strpos('not used', 'not used'));
    }

    /**
     * @depends testRedefineNativeFunction
     */
    public function testRedefinedFunctionsResetAfterTest() {
        $test_string = 'such test, very string, wow.';

        $this->assertSame(11, strpos($test_string, 'very'));
    }

    public function testRedefineUserfunction() {
        $test_string = 'such test, very string, wow.';

        // Inner function are normally not a good idea but we need one to test with here
        function example_user_function() { //@codingStandardsIgnoreLine
            return 'nothing like the above';
        }

        $this->redefineFunction('example_user_function', function () use ($test_string) {
            return $test_string;
        });

        $this->assertSame($test_string, example_user_function());
    }

    public function testResetfunction() {
        $expected_value = 'crikey this isn\'t a file';

        $this->redefineFunction('file_get_contents', function () use ($expected_value) {
            return $expected_value;
        });

        $this->assertSame($expected_value, file_get_contents(__FILE__));

        $this->resetFunction('file_get_contents');

        $this->assertNotSame($expected_value, file_get_contents(__FILE__));
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
    }

    public function testRedefinedMethodResetAfterTest() {
        $test_object = new \DateTime();

        $this->assertSame(date('Y-m-d'), $test_object->format('Y-m-d'));
    }

    public function testResetMethod() {
        $test_object = new \DateTime();
        $initial_value = $test_object->format('Y-m-d');
        $expected_value = 'very format, many date, wow.';

        $this->redefineMethod(\DateTime::class, 'format', function () use ($expected_value) {
            return $expected_value;
        });

        $this->assertSame($expected_value, $test_object->format('Y-m-d'));

        $this->resetMethod(\DateTime::class, 'format');

        $this->assertSame($initial_value, $test_object->format('Y-m-d'));
    }

}
