<?php

namespace iter {
    function curry(callable $fn, $num = 1) {
        if ($num <= 0) {
            return $fn;
        }

        return function($arg1) use ($fn, $num) {
            return curry(function(...$args) use ($arg1, $fn) {
                return $fn($arg1, ...$args);
            }, $num - 1);
        };
    }

    function compose(...$fns) {
        return pipe(...array_reverse($fns));
    }

    function pipe(...$fns) {
        return function($arg) use ($fns) {
            return reduce(function($arg, $fn) {
                return $fn($arg);
            }, $fns, $arg);
        };
    }
}

namespace iter\curried {
    use function iter\curry;

    function map()       { return curry('iter\map')(func_get_arg(0)); }
    function mapKeys()   { return curry('iter\mapKeys')(func_get_arg(0)); }
    function flatMap()   { return curry('iter\flatMap')(func_get_arg(0)); }
    function reindex()   { return curry('iter\reindex')(func_get_arg(0)); }
    function apply()     { return curry('iter\apply')(func_get_arg(0)); }
    function filter()    { return curry('iter\filter')(func_get_arg(0)); }
    function take()      { return curry('iter\take')(func_get_arg(0)); }
    function drop()      { return curry('iter\drop')(func_get_arg(0)); }
    function any()       { return curry('iter\any')(func_get_arg(0)); }
    function all()       { return curry('iter\all')(func_get_arg(0)); }
    function search()    { return curry('iter\search')(func_get_arg(0)); }
    function takeWhile() { return curry('iter\takeWhile')(func_get_arg(0)); }
    function dropWhile() { return curry('iter\dropWhile')(func_get_arg(0)); }
    function join()      { return curry('iter\join')(func_get_arg(0)); }
    function recurse()   { return curry('iter\recurse')(func_get_arg(0)); }
}
