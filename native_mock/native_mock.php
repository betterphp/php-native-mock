<?php

declare(strict_types=1);

namespace betterphp\native_mock;

trait native_mock {

    private $redefined_functions;

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

}
