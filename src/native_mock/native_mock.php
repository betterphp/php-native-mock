<?php

declare(strict_types=1);

namespace betterphp\native_mock;

trait native_mock {

    private $redefined_functions;
    private $redefined_methods;
    private $hooked_functions;
    private $hooked_methods;

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
        $this->hooked_functions = [];
        $this->hooked_methods = [];
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

        foreach ($this->hooked_functions as $function_name) {
            $this->removeFunctionHook($function_name);
        }

        foreach ($this->hooked_methods as $method) {
            list($class_name, $method_name) = $method;

            $this->removeMethodHook($class_name, $method_name);
        }
    }

    /**
     * Gets a list of functions that have been redefined
     *
     * @return array A list of function names
     */
    protected function getRedefinedFunctions(): array {
        return $this->redefined_functions;
    }

    /**
     * Gets a list of methods that have been redefined
     *
     * Each entry is an array containing two properties, the class then method name
     *
     * @return array A list of methods
     */
    protected function getRedefinedMethods(): array {
        return $this->redefined_methods;
    }

    /**
     * Gets a list of functions that have had hooks set
     *
     * @return array A list of function names
     */
    protected function getHookedFunctions(): array {
        return $this->hooked_functions;
    }

    /**
     * Gets a list of methods that have been hooked
     *
     * Each entry is an array containing two properties, the class then method name
     *
     * @return array A list of methods
     */
    protected function getHookedMethods(): array {
        return $this->hooked_methods;
    }

    /**
     * Used to get a function to be used by the diable function feature
     *
     * @param mixed $return_value The value that should be returned
     * @param array $capture_params A list of variables to store the function parameters in
     *
     * @return \Closure The function
     */
    private function getDisabledFunctionClosure(&$return_value, array &$capture_params): \Closure {
        return function (...$params) use ($return_value, &$capture_params) {
            if ($capture_params !== null) {
                $total_params = count($params);

                for ($i = 0; $i < $total_params; ++$i) {
                    $capture_params[$i] = $params[$i];
                }
            }

            return $return_value;
        };
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
     * Disables a build-in or user defined function
     *
     * $capture_params should be set to an array of variable references in the order of the function parameters
     * For example for the scandir() function something like this could be done
     *
     * $this->disableFunction('scandir', ['file1.txt', 'file2.txt'], [&$directory, &$sort_order]);
     *
     * @param string $function_name The name of the function
     * @param mixed $return_value The value that the function should be returned, defaults to null
     * @param array $capture_params An array of variables to store the function params in
     *
     * @return void
     */
    protected function disableFunction(string $function_name, $return_value = null, array $capture_params = null) {
        $this->redefineFunction($function_name, $this->getDisabledFunctionClosure($return_value, $capture_params));
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
     * Disabled a built-in or user defined method
     *
     * $capture_params should be set to an array of variable references in the order of the function parameters
     * For example for the DateTime::format() method something like this could be done
     *
     * $this->disableMethod(\DateTime::class, 'format', '10/10/2010', [&$actual_format]);
     *
     * @param string $class_name The name of the class
     * @param string $method_name The name of the method in the class
     * @param mixed $return_value The value that the function should be returned, defaults to null
     * @param array $capture_params An array of variables to store the function params in
     *
     * @return void
     */
    protected function disableMethod(
        string $class_name,
        string $method_name,
        $return_value = null,
        array $capture_params = null
    ) {
        $this->redefineMethod(
            $class_name,
            $method_name,
            $this->getDisabledFunctionClosure($return_value, $capture_params)
        );
    }

    /**
     * Resets a method to it's original state
     *
     * @param string $class_name The name of the class that the method belongs to
     * @param string $method_name The name of the method
     *
     * @return void
     */
    protected function resetMethod(string $class_name, string $method_name) {
        uopz_unset_return($class_name, $method_name);

        $this->redefined_methods = array_filter(
            $this->redefined_methods,
            function ($redefined) use ($class_name, $method_name) {
                return $class_name !== $redefined[0] && $method_name !== $redefined[1];
            }
        );
    }

    /**
     * Sets a hook to be called when a function is executed
     *
     * @param string $function_name The name of the function to hook
     * @param \Closure $hook The hook to call
     *
     * @return void
     */
    protected function setFunctionHook(string $function_name, \Closure $hook) {
        uopz_set_hook($function_name, $hook);

        $this->hooked_functions[] = $function_name;
    }

    /**
     * Removes a hook from a function
     *
     * @param string $function_name The name of the function
     *
     * @return void
     */
    protected function removeFunctionHook(string $function_name) {
        uopz_unset_hook($function_name);

        $this->hooked_functions = array_filter(
            $this->hooked_functions,
            function ($hooked) use ($function_name) {
                return $hooked !== $function_name;
            }
        );
    }

    /**
     * Sets a hook to be called when a method is executed
     *
     * The hook is executed from the object context, so $this works
     *
     * @param string $class_name The name of the class
     * @param string $method_name The name of the method
     * @param \Closure $hook The hook to execute
     *
     * @return void
     */
    protected function setMethodHook(string $class_name, string $method_name, \Closure $hook) {
        uopz_set_hook($class_name, $method_name, $hook);

        $this->hooked_methods[] = [$class_name, $method_name];
    }

    /**
     * Removes a hook from a method
     *
     * @param string $class_name The name of the class
     * @param string $method_name The name of the function
     *
     * @return void
     */
    protected function removeMethodHook(string $class_name, string $method_name) {
        uopz_unset_hook($class_name, $method_name);

        $this->hooked_methods = array_filter(
            $this->hooked_methods,
            function ($hooked) use ($class_name, $method_name) {
                return $hooked[0] !== $class_name && $hooked[1] !== $method_name;
            }
        );
    }

}
