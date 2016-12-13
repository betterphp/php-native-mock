<?php

declare(strict_types=1);

namespace betterphp\native_mock;

trait native_mock {

    private $redefined_functions;
    private $redefined_methods;

    /**
     * Should be called from TestCase::setUp()
     *
     * @return void
     */
    protected function nativeMockSetUp() {
        if (!extension_loaded('uopz')) {
            $this->markTestSkipped('The uopz extension is required for this test https://github.com/krakjoe/uopz');
        }

        $this->redefined_functions = [];
        $this->redefined_methods = [];
    }

    /**
     * Should be called from TestCase::tearDown()
     *
     * @return void
     */
    protected function nativeMockTearDown() {
        foreach ($this->redefined_functions as $function_name) {
            $this->resetFunction($function_name);
        }

        foreach ($this->redefined_methods as $method) {
            list($class_name, $method_name) = $method;

            $this->resetMethod($class_name, $method_name);
        }
    }

    /**
     * Redefined a built-in or user defined function
     *
     * It's a good idea to make the new function accept either no parameters
     * or the same ones as the original.
     *
     * @param string $function_name The name of the function
     * @param \Closure $replacement The new function
     *
     * @return void
     */
    protected function redefineFunction(string $function_name, \Closure $replacement) {
        uopz_set_return($function_name, $replacement, true);

        $this->redefined_functions[] = $function_name;
    }

    /**
     * Resets a function to it's original value
     *
     * @param string $function_name The name of the function
     *
     * @return void
     */
    protected function resetFunction(string $function_name) {
        uopz_unset_return($function_name);

        $this->redefined_functions = array_filter(
            $this->redefined_functions,
            function ($redefined) use ($function_name) {
                return $redefined !== $function_name;
            }
        );
    }

    /**
     * Redefines a built-in or use defined method
     *
     * As with functions it's a good idea to either no parameters
     * or the same ones as the original
     *
     * Note that this will affect all instances of the class and
     * not just the one being tested
     *
     * @param string $class_name The name of the class
     * @param string $method_name The name of the method
     * @param \Closure $replacement The new method
     *
     * @return void
     */
    protected function redefineMethod(string $class_name, string $method_name, \Closure $replacement) {
        uopz_set_return($class_name, $method_name, $replacement, true);

        $this->redefined_methods[] = [$class_name, $method_name];
    }

    /**
     * Resets a method to it's original state
     *
     * @param string $class_name The name of the class that the method belongs to
     * @param string $method_name The name of the method
     *
     * @return void
     */
    public function resetMethod(string $class_name, string $method_name) {
        uopz_unset_return($class_name, $method_name);

        $this->redefined_methods = array_filter(
            $this->redefined_methods,
            function ($redefined) use ($class_name, $method_name) {
                return $class_name === $redefined[0] && $method_name === $redefined[1];
            }
        );
    }

}
