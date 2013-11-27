<?php

namespace iter;

require __DIR__ . '/iter.fn.php';

/**
 * Creates an iterable containing all numbers between the start and end value
 * (inclusive) with a certain step.
 *
 * Examples:
 *
 *     iter\range(0, 5)
 *     => iter(0, 1, 2, 3, 4, 5)
 *     iter\range(5, 0)
 *     => iter(5, 4, 3, 2, 1, 0)
 *     iter\range(0.0, 3.0, 0.5)
 *     => iter(0.0, 0.5, 1.0, 1.5, 2.0, 2.5, 3.0)
 *     iter\range(3.0, 0.0, -0.5)
 *     => iter(3.0, 2.5, 2.0, 1.5, 1.0, 0.5, 0.0)
 *
 * @param number $start First number (inclusive)
 * @param number $end   Last number (inclusive, but doesn't have to be part of
 *                      resulting range if $step steps over it)
 * @param number $step  Step between numbers (defaults to 1 if $start smaller
 *                      $end and to -1 if $start greater $end)
 *
 * @throws \InvalidArgumentException if step is not valid
 */
function range($start, $end, $step = null) {
    if ($start == $end) {
        yield $start;
    } elseif ($start < $end) {
        if (null === $step) {
            $step = 1;
        }
        if ($step <= 0) {
            throw new \InvalidArgumentException(
                'If start < end the step must be positive'
            );
        }

        for ($i = $start; $i <= $end; $i += $step) {
            yield $i;
        }
    } else {
        if (null === $step) {
            $step = -1;
        }
        if ($step >= 0) {
            throw new \InvalidArgumentException(
                'If start > end the step must be negative'
            );
        }

        for ($i = $start; $i >= $end; $i += $step) {
            yield $i;
        }
    }
}

/**
 * Applies a mapping function to all values of an iterator.
 *
 * The function is passed the current iterator value and should return a
 * modified iterator value. The key is left as-is and not passed to the mapping
 * function.
 *
 * Examples:
 *
 *     iter\map(iter\fn\operator('*', 2), [1, 2, 3, 4, 5]);
 *     => iter(2, 4, 6, 8, 10)
 *
 *     $column = map(fn\index('name'), $iter);
 *
 * @param callable $function The mapping function: mixed function(mixed $value)
 * @param mixed    $iterable The iterable to be mapped over
 */
function map(callable $function, $iterable) {
    foreach ($iterable as $key => $value) {
        yield $key => $function($value);
    }
}

/**
 * Applies a function to all values of an iterable.
 *
 * The function is passed the current iterator value. The reason why apply
 * exists additionally to map is that map is lazy, whereas apply is not (i.e.
 * you do not need to consume a resulting iterator for the function calls to
 * actually happen.)
 *
 * Examples:
 *
 *     iter\apply(iter\fn\method('rewind'), $iterators);
 *
 * @param $function
 * @param $iterable
 */
function apply(callable $function, $iterable) {
    foreach ($iterable as $value) {
        $function($value);
    }
}

/**
 * Filters an iterable using a predicate.
 *
 * The predicate is passed the iterator value, which is only retained if the
 * predicate returns a truthy value. The key is not passed to the predicate and
 * left as-is.
 *
 * Examples:
 *
 *     iter\filter(iter\fn\operator('<', 0), [0, -1, -10, 7, 20, -5, 7]);
 *     => iter(-1, -10, -5)
 *
 *     iter\filter(iter\fn\operator('instanceof', 'SomeClass'), $objects);
 *
 * @param callable $predicate The predicate: bool function(mixed $value)
 * @param mixed    $iterable  The iterable to filter
 */
function filter(callable $predicate, $iterable) {
    foreach ($iterable as $key => $value) {
        if ($predicate($value)) {
            yield $key => $value;
        }
    }
}

/**
 * Reduce iterable using a function.
 *
 * The reduction function is passed an accumulator value and the current
 * iterator value and returns a new accumulator. The accumulator is initialized
 * to $startValue.
 *
 * Examples:
 *
 *      reduce(fn\operator('+'), range(1, 5), 0)
 *      => 15
 *      reduce(fn\operator('*'), range(1, 5), 1)
 *      => 120
 *
 * @param callable $function Reduction function
 * @param mixed    $iterable Iterable to reduce
 * @param mixed    $startValue Start value for accumulator. Usually identity
 *                 value of $function.
 *
 * @return mixed Result of the reduction
 */
function reduce(callable $function, $iterable, $startValue = null) {
    $acc = $startValue;
    foreach ($iterable as $value) {
        $acc = $function($acc, $value);
    }
    return $acc;
}

/**
 * Zips the iterables that were passed as arguments.
 *
 * Afterwards keys and values will be arrays containing the keys/values of
 * the individual iterables. This function stops as soon as the first iterable
 * becomes invalid.
 *
 * Examples:
 *
 *     iter\zip([1, 2, 3], [4, 5, 6], [7, 8, 9])
 *     => iter([1, 4, 7], [2, 5, 8], [3, 6, 9])
 *
 * @param mixed[] ...$iterables The iterables to zip
 */
function zip(/* ...$iterables */) {
    $iterators = array_map('iter\\toIter', func_get_args());

    for (
        apply(fn\method('rewind'), $iterators);
        all(fn\method('valid'), $iterators);
        apply(fn\method('next'), $iterators)
    ) {
        yield toArray(map(fn\method('key'), $iterators))
           => toArray(map(fn\method('current'), $iterators));
    }
}

/**
 * Combines an iterable for keys and another for values into one iterator.
 *
 * Examples:
 *
 *     iter\zipKeyValue(['a', 'b', 'c'], [1, 2, 3])
 *     => iter('a' => 1, 'b' => 2, 'c' => 3)
 *
 * @param mixed $keys   Iterable of keys
 * @param mixed $values Iterable of values
 */
function zipKeyValue($keys, $values) {
    $keys = toIter($keys);
    $values = toIter($values);

    for (
        $keys->rewind(), $values->rewind();
        $keys->valid() && $values->valid();
        $keys->next(), $values->next()
    ) {
        yield $keys->current() => $values->current();
    }
}

/**
 * Chains the iterables that were passed as arguments.
 *
 * The resulting iterator will contain the values of the first iterable, then
 * the second, and so on.
 *
 * Examples:
 *
 *     iter\chain(iter\range(0, 5), iter\range(6, 10), iter\range(11, 15))
 *     => iter(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15)
 *
 * @param mixed[] ...$iterables The iterables to chain
 */
function chain(/* ...$iterables */) {
    foreach (func_get_args() as $iterable) {
        foreach ($iterable as $key => $value) {
            yield $key => $value;
        }
    }
}

/**
 * Takes a slice from an iterable.
 *
 * Examples:
 *
 *      iter\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5)
 *      => iter(0, 1, 2, 3, 4, 5)
 *      iter\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 3)
 *      => iter(0, 1, 2, 3)
 *
 * @param mixed $iterable The iterable to take the slice from
 * @param int   $start    The start offset
 * @param int   $length   The length (if not specified all remaining
 *                        iterable values are used)
 */
function slice($iterable, $start, $length = INF) {
    $i = 0;
    foreach ($iterable as $key => $value) {
        if ($i >= $start + $length) {
            break;
        }
        if ($i++ < $start) {
            continue;
        }
        yield $key => $value;
    }
}

/**
 * Takes n items from an iterable.
 *
 * Examples:
 *
 *      iter\take(3, [1, 2, 3, 4, 5])
 *      => iter(1, 2, 3)
 *
 * @param int   $length   The length
 * @param mixed $iterable The iterable to take the slice from
 */
function take($length, $iterable) {
    foreach (slice($iterable, 0, $length) as $key => $value) {
        yield $key => $value;
    }
}

/**
 * Drops n items from an iterable.
 *
 * Examples:
 *
 *      iter\drop(3, [1, 2, 3, 4, 5])
 *      => iter(4, 5)
 *
 * @param int   $length   The length
 * @param mixed $iterable The iterable to take the slice from
 */
function drop($length, $iterable) {
    foreach (slice($iterable, $length) as $key => $value) {
        yield $key => $value;
    }
}

/**
 * Repeat an element a given number of times. By default the element is repeated
 * indefinitely.
 *
 * Examples:
 *
 *     iter\repeat(42, 5)
 *     => iter(42, 42, 42, 42, 42)
 *     iter\repeat(1)
 *     => iter(1, 1, 1, 1, 1, 1, 1, 1, 1, ...)
 *
 * @param mixed $value Value to repeat
 * @param int   $n     Number of repetitions (defaults to INF)
 */
function repeat($value, $n = INF) {
    for ($i = 0; $i < $n; ++$i) {
        yield $value;
    }
}

/**
 * Returns the keys of an iterable.
 *
 * Examples:
 *
 *      iter\keys(['a' => 0, 'b' => 1, 'c' => 2])
 *      => iter('a', 'b', 'c')
 *
 * @param mixed $iterable Iterable to get keys from
 */
function keys($iterable) {
    foreach ($iterable as $key => $_) {
        yield $key;
    }
}

/**
 * Returns the values of an iterable, making the keys continuously indexed.
 *
 * Examples:
 *
 *      iter\values([17 => 1, 42 => 2, -2 => 100])
 *      => iter(0 => 1, 1 => 42, 2 => 100)
 *
 * @param mixed $iterable Iterable to get values from
 */
function values($iterable) {
    foreach ($iterable as $value) {
        yield $value;
    }
}

/**
 * Returns true if there is a value in the iterable that satisfies the
 * predicate.
 *
 * This function is short-circuiting, i.e. if the predicate matches for any one
 * element the remaining elements will not be considered anymore.
 *
 * Examples:
 *
 *      iter\all(fn\operator('>', 0), range(1, 10))
 *      => true
 *      iter\all(fn\operator('>', 0), range(-5, 5))
 *      => false
 *
 * @param callable $predicate Predicate
 * @param mixed    $iterable  Iterable to check against the predicate
 *
 * @return bool Whether the predicate holds for all values
 */
function any(callable $predicate, $iterable) {
    foreach ($iterable as $value) {
        if ($predicate($value)) {
            return true;
        }
    }

    return false;
}

/**
 * Returns true if all values in the iterable satisfy the predicate.
 *
 * This function is short-circuiting, i.e. if the predicate fails for one
 * element the remaining elements will not be considered anymore.
 *
 * Examples:
 *
 *      iter\all(fn\operator('>', 0), range(1, 10))
 *      => true
 *      iter\all(fn\operator('>', 0), range(-5, 5))
 *      => false
 *
 * @param callable $predicate Predicate
 * @param mixed    $iterable  Iterable to check against the predicate
 *
 * @return bool Whether the predicate holds for all values
 */
function all(callable $predicate, $iterable) {
    foreach ($iterable as $value) {
        if (!$predicate($value)) {
            return false;
        }
    }

    return true;
}

function count($iterable) {
    if (is_array($iterable) || $iterable instanceof \Countable) {
        return \count($iterable);
    } else {
        $count = 0;
        foreach ($iterable as $_) {
            ++$count;
        }
        return $count;
    }
}

function toIter($iterable) {
    if ($iterable instanceof \Iterator) {
        return $iterable;
    }
    if ($iterable instanceof \IteratorAggregate) {
        return $iterable->getIterator();
    }
    return call_user_func(function() use ($iterable) {
        foreach ($iterable as $key => $value) {
            yield $key => $value;
        }
    });
}

function toArray($iterable) {
    $array = [];
    foreach ($iterable as $value) {
        $array[] = $value;
    }
    return $array;
}

function toArrayWithKeys($iterable) {
    $array = [];
    foreach ($iterable as $key => $value) {
        $array[$key] = $value;
    }
    return $array;
}

/*
 * Python:
 * compress()
 * dropwhile()
 * groupby()
 * tee()
 * takewhile()
 * izip_longest()
 * multi-map
 */