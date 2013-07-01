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
    return function($value) use ($function) {
        return !$function($value);
    };
}