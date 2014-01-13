<?php

namespace iter\fn;

function index($index) {
    return function($array) use ($index) {
        return $array[$index];
    };
}

function property($propertyName) {
    return function($object) use ($propertyName) {
        return $object->$propertyName;
    };
}

function method($methodName, $args = []) {
    if (empty($args)) {
        return function($object) use ($methodName) {
            return $object->$methodName();
        };
    } else {
        return function($object) use ($methodName, $args) {
            return call_user_func_array([$object, $methodName], $args);
        };
    }
}

function operator($operator, $arg = null) {
    $functions = [
        'instanceof' => function($a, $b) { return $a instanceof $b; },
        '*'   => function($a, $b) { return $a *   $b; },
        '/'   => function($a, $b) { return $a /   $b; },
        '%'   => function($a, $b) { return $a %   $b; },
        '+'   => function($a, $b) { return $a +   $b; },
        '-'   => function($a, $b) { return $a -   $b; },
        '.'   => function($a, $b) { return $a .   $b; },
        '<<'  => function($a, $b) { return $a <<  $b; },
        '>>'  => function($a, $b) { return $a >>  $b; },
        '<'   => function($a, $b) { return $a <   $b; },
        '<='  => function($a, $b) { return $a <=  $b; },
        '>'   => function($a, $b) { return $a >   $b; },
        '>='  => function($a, $b) { return $a >=  $b; },
        '=='  => function($a, $b) { return $a ==  $b; },
        '!='  => function($a, $b) { return $a !=  $b; },
        '===' => function($a, $b) { return $a === $b; },
        '!==' => function($a, $b) { return $a !== $b; },
        '&'   => function($a, $b) { return $a &   $b; },
        '^'   => function($a, $b) { return $a ^   $b; },
        '|'   => function($a, $b) { return $a |   $b; },
        '&&'  => function($a, $b) { return $a &&  $b; },
        '||'  => function($a, $b) { return $a ||  $b; },
    ];

    if (!isset($functions[$operator])) {
        throw new \InvalidArgumentException("Unknown operator \"$operator\"");
    }

    $fn = $functions[$operator];
    if (func_num_args() === 1) {
        return $fn;
    } else {
        return function($a) use ($fn, $arg) {
            return $fn($a, $arg);
        };
    }
}

function not($function) {
    return function() use ($function) {
        return !call_user_func_array($function, func_get_args());
    };
}

/**
 * Returns a callable which returns an item from a (nested) associative array
 * corresponding to the given path.
 *
 * Useful in conjunction with iter\reindex.
 *
 * Examples:
 *     $security = [
 *         'seccode' => 'HT-R-A',
 *         'isin' => 'HRHT00RA0005',
 *         'issuer' => [
 *             'code' => 'HT',
 *             'name' => 'Hrvatski Telekom d.d.'
 *         ],
 *     ];
 *
 *     $path = path('issuer', 'code');
 *
 *     $path($security);
 *     => "HT"
 */
function path() {
    $path = func_get_args();

    return function($array) use ($path) {
        foreach ($path as $key) {
            if (!is_scalar($key)) {
                throw new \InvalidArgumentException("Path item not scalar.");
            }
            if (isset($array[$key])) {
                $array = $array[$key];
            } else {
                $path = implode(' > ', $path);
                $msg = sprintf("Path \"%s\" not found in array.", $path);
                throw new \InvalidArgumentException($msg);
            }
        }

        return $array;
    };
}