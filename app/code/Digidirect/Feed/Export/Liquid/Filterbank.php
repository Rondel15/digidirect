<?php

namespace Digidirect\Feed\Export\Liquid;

/**
 * @codingStandardsIgnoreFile
 * @SuppressWarnings(PHPMD)
 */
class Filterbank
{
    /**
     * The registerd filter objects
     *
     * @var array
     */
    public $filters;

    /**
     * A map of all filters and the class that contain them (in the case of methods)
     *
     * @var unknown_type
     */
    public $methodMap;

    /**
     * Reference to the current context object
     *
     * @var Context
     */
    public $context;

    /**
     * Filterbank constructor.
     * @param $context
     */
    public function __construct(&$context)
    {
        $this->context = $context;
    }

    /**
     * Adds a filter to the bank
     *
     * @param mixed $filter Can either be an object, the name of a class (in which case the
     * filters will be called statically) or the name of a function.
     * @return bool
     */
    public function addFilter($filter)
    {
        // if the passed filter was an object, store the object for future reference.
        if (is_object($filter)) {
            $methods = array_flip(get_class_methods($filter));

            foreach ($methods as $method => $null) {
                $this->methodMap[$method] = $filter;
            }

            return true;
        }

        return false;
    }

    /**
     * Invokes the filter with the given name
     *
     * @param string $name The name of the filter
     * @param string $value The value to filter
     * @param array $args The additional arguments for the filter
     * @return string
     */
    public function invoke($name, $value, $args)
    {
        if (!is_array($args)) {
            $args = [];
        }

        array_unshift($args, $value);
        // consult the mapping
        if (isset($this->methodMap[$name])) {

            $class = $this->methodMap[$name];

            // if we have a registered object for the class, use that instead
            if (isset($this->filters[$class])) {
                $class = &$this->filters[$class];
            }
            return call_user_func_array([$class, $name], $args);
        }

        return $value;
    }
}
