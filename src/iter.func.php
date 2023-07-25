<?php

namespace iter\func;

/**
 * Returns a callable which extracts a given index from an array.
 *
 * Example:
 *
 *     $array = [ 'foo' => 42 ];
 *
 *     func\index('foo')($array);
 *     => 42
 *
 *
 * @param array-key $index
 *
 * @return callable(array):mixed
 */
function index($index) {
    return function($array) use ($index) {
        return $array[$index];
    };
}

/**
 * Returns a callable which returns an item from a nested array corresponding
 * to the given path.
 *
 * Examples:
 *
 *     $array = [
 *         'foo' => [
 *             'bar' => [
 *                 'baz' => 42
 *             ]
 *         ]
 *     ];
 *
 *     $getIndexFooBar = func\nested_index('foo', 'bar');
 *     $getIndexFooBarBaz = func\nested_index('foo', 'bar', 'baz');
 *
 *     $getIndexFooBar($array)
 *     => ['baz' => 42]
 *
 *     $getIndexFooBarBaz($array)
 *     => 42
 *
 * @param array-key ...$indices Path of indices
 *
 * @return callable(array):mixed
 */
function nested_index(...$indices) {
    return function($array) use ($indices) {
        foreach ($indices as $index) {
            $array = $array[$index];
        }

        return $array;
    };
}

/**
 * Returns a callable which returns a given property from an object.
 *
 * Example:
 *
 *    $object = new \stdClass();
 *    $object->foo = 42;
 *
 *    func\property('foo')($object);
 *    => 42
 *
 * @param string $propertyName
 *
 * @return callable(object):mixed
 */
function property($propertyName) {
    return function($object) use ($propertyName) {
        return $object->$propertyName;
    };
}

/**
 * Returns a callable which calls a method on an object, optionally with some
 * provided arguments.
 *
 * Example:
 *
 *     class Foo {
 *         public function bar($a, $b) {
 *             return $a + $b;
 *         }
 *     }
 *
 *     $foo = new Foo();
 *
 *     func\method('bar', [1, 2])($foo);
 *     => 3
 *
 * @param string $methodName
 * @param mixed[] $args
 *
 * @return callable(object):mixed
 */
function method($methodName, $args = []) {
    return function($object) use ($methodName, $args) {
        return $object->$methodName(...$args);
    };
}

/**
 * Returns a callable which applies the specified operator to the argument.
 *
 * Examples:
 *
 *     $addOne = func\operator('+', 1);
 *     $addOne(41);
 *     => 42
 *
 *     $modulo2 = func\operator('%', 2);
 *     $modulo2(42);
 *     => 0
 *
 * @param string $operator
 * @param mixed $arg The right-hand argument for the operator
 *
 * @return callable
 */
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
        '**'  => function($a, $b) { return $a **  $b; },
        '<=>' => function($a, $b) { return $a <=> $b; },
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

/**
 * Takes a callable which returns a boolean, and returns another function that
 * returns the opposite for all values.
 *
 * Example:
 *     $isEven = function($x) {
 *         return $x % 2 === 0;
 *     };
 *
 *     $isOdd = func\not($isEven);
 *
 *     $isEven(42);
 *     => true
 *
 *     $isOdd(42);
 *     => false
 *
 * @param callable(...mixed):bool $function
 *
 * @return callable(...mixed):bool
 */
function not($function) {
    return function(...$args) use ($function) {
        return !$function(...$args);
    };
}
