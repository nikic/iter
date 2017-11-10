<?php

namespace iter;

use Traversable;
use Countable;

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
 * @param int|float $start First number (inclusive)
 * @param int|float $end   Last number (inclusive, but doesn't have to be part of
 *                         resulting range if $step steps over it)
 * @param int|float $step  Step between numbers (defaults to 1 if $start smaller
 *                         $end and to -1 if $start greater $end)
 *
 * @throws \InvalidArgumentException if step is not valid
 *
 * @return \Iterator
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
 * @param callable $function Mapping function: mixed function(mixed $value)
 * @param array|Traversable $iterable Iterable to be mapped over
 *
 * @return \Iterator
 */
function map(callable $function, $iterable) {
    _assertIterable($iterable, 'Second argument');
    foreach ($iterable as $key => $value) {
        yield $key => $function($value);
    }
}

/**
 * Applies a mapping function to all keys of an iterator.
 *
 * The function is passed the current iterator key and should return a
 * modified iterator key. The value is left as-is and not passed to the mapping
 * function.
 *
 * Examples:
 *
 *     iter\mapKeys('strtolower', ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4]);
 *     => iter('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4)
 *
 * @param callable $function Mapping function: mixed function(mixed $key)
 * @param array|Traversable $iterable Iterable those keys are to be mapped over
 *
 * @return \Iterator
 */
function mapKeys(callable $function, $iterable) {
    _assertIterable($iterable, 'Second argument');
    foreach ($iterable as $key => $value) {
        yield $function($key) => $value;
    }
}
/**
 *
 * Applies a function to each value in an iterator and flattens the result.
 *
 * The function is passed the current iterator value and should return an
 * iterator of new values. The result will be a concatenation of the iterators
 * returned by the mapping function.
 *
 * Examples:
 *
 *     iter\flatMap(function($v) { return [-$v, $v]; }, [1, 2, 3, 4, 5]);
 *     => iter(-1, 1, -2, 2, -3, 3, -4, 4, -5, 5)
 *
 * @param callable $function Mapping function: iterable function(mixed $value)
 * @param array|Traversable $iterable Iterable to be mapped over
 *
 * @return \Iterator
 */
function flatMap(callable $function, $iterable) {
    _assertIterable($iterable, 'Second argument');
    foreach ($iterable as $value) {
        foreach ($function($value) as $k => $v) {
            yield $k => $v;
        }
    }
}

/**
 * Reindexes an array by applying a function to all values of an iterator and
 * using the returned value as the new key/index.
 *
 * The function is passed the current iterator value and should return a new
 * key for that element. The value is left as-is. The original key is not passed
 * to the mapping function.
 *
 * Examples:
 *
 *     $users = [
 *         ['id' => 42, 'name' => 'foo'],
 *         ['id' => 24, 'name' => 'bar']
 *     ];
 *     iter\reindex(iter\fn\index('id'), $users)
 *     => iter(
 *         42 => ['id' => 42, 'name' => 'foo'],
 *         24 => ['id' => 24, 'name' => 'bar']
 *     )
 *
 * @param callable $function Mapping function mixed function(mixed $value)
 * @param array|Traversable $iterable Iterable to reindex
 *
 * @return \Iterator
 */
function reindex(callable $function, $iterable) {
    _assertIterable($iterable, 'Second argument');
    foreach ($iterable as $value) {
        yield $function($value) => $value;
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
 * @param callable $function Apply function: void function(mixed $value)
 * @param array|Traversable $iterable Iterator to apply on
 */
function apply(callable $function, $iterable) {
    _assertIterable($iterable, 'Second argument');
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
 * @param callable $predicate Predicate: bool function(mixed $value)
 * @param array|Traversable $iterable Iterable to filter
 *
 * @return \Iterator
 */
function filter(callable $predicate, $iterable) {
    _assertIterable($iterable, 'Second argument');
    foreach ($iterable as $key => $value) {
        if ($predicate($value)) {
            yield $key => $value;
        }
    }
}

/**
 * Enumerates pairs of [key, value] of an iterable.
 *
 * Examples:
 *
 *      iter\enumerate(['a', 'b']);
 *      => iter([0, 'a'], [1, 'b'])
 *
 *      $values = ['a', 'b', 'c', 'd'];
 *      $filter = function($t) { return $t[0] % 2 == 0; };
 *      iter\map(iter\fn\index(1), iter\filter($filter, iter\enumerate($values)));
 *      => iter('a', 'c')
 *
 * @param array|Traversable $iterable Iterable to enumerate
 *
 * @return \Iterator
 */
function enumerate($iterable) {
    _assertIterable($iterable, 'First argument');
    foreach ($iterable as $key => $value) {
        yield [$key, $value];
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
 * @param callable $function Reduction function:
 *                           mixed function(mixed $acc, mixed $value, mixed $key)
 * @param array|Traversable $iterable Iterable to reduce
 * @param mixed $startValue Start value for accumulator.
 *                          Usually identity value of $function.
 *
 * @return mixed Result of the reduction
 */
function reduce(callable $function, $iterable, $startValue = null) {
    _assertIterable($iterable, 'Second argument');

    $acc = $startValue;
    foreach ($iterable as $key => $value) {
        $acc = $function($acc, $value, $key);
    }
    return $acc;
}

/**
 * Intermediate values of reducing iterable using a function.
 *
 * The reduction function is passed an accumulator value and the current
 * iterator value and returns a new accumulator. The accumulator is initialized
 * to $startValue.
 *
 * Reductions yield each accumulator along the way.
 *
 * Examples:
 *
 *      reductions(fn\operator('+'), range(1, 5), 0)
 *      => iter(1, 3, 6, 10, 15)
 *      reductions(fn\operator('*'), range(1, 5), 1)
 *      => iter(1, 2, 6, 24, 120)
 *
 * @param callable $function Reduction function:
 *                           mixed function(mixed $acc, mixed $value, mixed $key)
 * @param array|Traversable $iterable   Iterable to reduce
 * @param mixed $startValue Start value for accumulator.
 *                          Usually identity value of $function.
 *
 * @return \Iterator Intermediate results of the reduction
 */
function reductions(callable $function, $iterable, $startValue = null) {
    _assertIterable($iterable, 'Second argument');

    $acc = $startValue;
    foreach ($iterable as $key => $value) {
        $acc = $function($acc, $value, $key);
        yield $acc;
    }
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
 * @param array|Traversable ...$iterables Iterables to zip
 *
 * @return \Iterator
 */
function zip(/* ...$iterables */) {
    $iterables = func_get_args();
    if (count($iterables) === 0) {
        return;
    }
    _assertAllIterable($iterables);

    $iterators = array_map('iter\\toIter', $iterables);
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
 * @param array|Traversable $keys   Iterable of keys
 * @param array|Traversable $values Iterable of values
 *
 * @return \Iterator
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
 * @param array|Traversable ...$iterables Iterables to chain
 *
 * @return \Iterator
 */
function chain(/* ...$iterables */) {
    $iterables = func_get_args();
    _assertAllIterable($iterables);
    foreach ($iterables as $iterable) {
        foreach ($iterable as $key => $value) {
            yield $key => $value;
        }
    }
}

/**
 * Returns the cartesian product of iterables that were passed as arguments.
 *
 * The resulting iterator will contain all the possible tuples of keys and
 * values.
 *
 * Please note that the iterables after the first must be rewindable.
 *
 * Examples:
 *
 *     iter\product(iter\range(1, 2), iter\rewindable\range(3, 4))
 *     => iter([1, 3], [1, 4], [2, 3], [2, 4])
 *
 * @param array|Traversable ...$iterables Iterables to combine
 *
 * @return \Iterator
 */
function product(/* ...$iterables */) {
    $iterables = func_get_args();
    _assertAllIterable($iterables);

    /** @var \Iterator[] $iterators */
    $iterators = array_map('iter\\toIter', $iterables);
    $numIterators = count($iterators);
    if (!$numIterators) {
        yield [] => [];
        return;
    }

    $keyTuple = $valueTuple = array_fill(0, $numIterators, null);

    $i = -1;
    while (true) {
        while (++$i < $numIterators - 1) {
            $iterators[$i]->rewind();
            if (!$iterators[$i]->valid()) {
                return;
            }
            $keyTuple[$i] = $iterators[$i]->key();
            $valueTuple[$i] = $iterators[$i]->current();
        }
        foreach ($iterators[$i] as $keyTuple[$i] => $valueTuple[$i]) {
            yield $keyTuple => $valueTuple;
        }
        while (--$i >= 0) {
            $iterators[$i]->next();
            if ($iterators[$i]->valid()) {
                $keyTuple[$i] = $iterators[$i]->key();
                $valueTuple[$i] = $iterators[$i]->current();
                continue 2;
            }
        }
        return;
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
 * @param array|Traversable $iterable Iterable to take the slice from
 * @param int $start Start offset
 * @param int $length Length (if not specified all remaining values from the
 *                    iterable are used)
 *
 * @throws \InvalidArgumentException if start or length are negative
 *
 * @return \Iterator
 */
function slice($iterable, $start, $length = INF) {
    _assertIterable($iterable, 'First argument');

    if ($start < 0) {
        throw new \InvalidArgumentException('Start offset must be non-negative');
    }
    if ($length < 0) {
        throw new \InvalidArgumentException('Length must be non-negative');
    }

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
 * Takes the first n items from an iterable.
 *
 * Examples:
 *
 *      iter\take(3, [1, 2, 3, 4, 5])
 *      => iter(1, 2, 3)
 *
 * @param int $num Number of elements to take from the start
 * @param array|Traversable $iterable Iterable to take the elements from
 *
 * @return \Iterator
 */
function take($num, $iterable) {
    return slice($iterable, 0, $num);
}

/**
 * Drops the first n items from an iterable.
 *
 * Examples:
 *
 *      iter\drop(3, [1, 2, 3, 4, 5])
 *      => iter(4, 5)
 *
 * @param int $num Number of elements to drop from the start
 * @param array|Traversable $iterable Iterable to drop the elements from
 *
 * @return \Iterator
 */
function drop($num, $iterable) {
    return slice($iterable, $num);
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
 * @param int   $num   Number of repetitions (defaults to INF)
 *
 * @throws \InvalidArgumentException if num is negative
 *
 * @return \Iterator
 */
function repeat($value, $num = INF) {
    if ($num < 0) {
        throw new \InvalidArgumentException(
            'Number of repetitions must be non-negative');
    }

    for ($i = 0; $i < $num; ++$i) {
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
 * @param array|Traversable $iterable Iterable to get keys from
 *
 * @return \Iterator
 */
function keys($iterable) {
    _assertIterable($iterable, 'Argument');
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
 * @param array|Traversable $iterable Iterable to get values from
 *
 * @return \Iterator
 */
function values($iterable) {
    _assertIterable($iterable, 'Argument');
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
 * @param callable $predicate Predicate: bool function(mixed $value)
 * @param array|Traversable $iterable Iterable to check against the predicate
 *
 * @return bool Whether the predicate matches any value
 */
function any(callable $predicate, $iterable) {
    _assertIterable($iterable, 'Second argument');
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
 * @param callable $predicate Predicate: bool function(mixed $value)
 * @param array|Traversable $iterable Iterable to check against the predicate
 *
 * @return bool Whether the predicate holds for all values
 */
function all(callable $predicate, $iterable) {
    _assertIterable($iterable, 'Second argument');
    foreach ($iterable as $value) {
        if (!$predicate($value)) {
            return false;
        }
    }

    return true;
}

/**
 * Searches an iterable until a predicate returns true, then returns
 * the value of the matching element.
 *
 * Examples:
 *
 *      iter\search(iter\fn\operator('===', 'baz'), ['foo', 'bar', 'baz'])
 *      => 'baz'
 *
 *      iter\search(iter\fn\operator('===', 'qux'), ['foo', 'bar', 'baz'])
 *      => null
 *
 * @param callable $predicate Predicate: bool function(mixed $value)
 * @param array|Traversable $iterable The iterable to search
 *
 * @return null|mixed
 */
function search(callable $predicate, $iterable) {
    _assertIterable($iterable, 'Second argument');
    foreach ($iterable as $value) {
        if ($predicate($value)) {
            return $value;
        }
    }

    return null;
}

/**
 * Takes items from an iterable until the predicate fails for the first time.
 *
 * This means that all elements before (and excluding) the first element on
 * which the predicate fails will be returned.
 *
 * Examples:
 *
 *      iter\takeWhile(fn\operator('>', 0), [3, 1, 4, -1, 5])
 *      => iter(3, 1, 4)
 *
 * @param callable $predicate Predicate: bool function(mixed $value)
 * @param array|Traversable $iterable Iterable to take values from
 *
 * @return \Iterator
 */
function takeWhile(callable $predicate, $iterable) {
    _assertIterable($iterable, 'Second argument');
    foreach ($iterable as $key => $value) {
        if (!$predicate($value)) {
            return;
        }

        yield $key => $value;
    }
}

/**
 * Drops items from an iterable until the predicate fails for the first time.
 *
 * This means that all elements after (and including) the first element on
 * which the predicate fails will be returned.
 *
 * Examples:
 *
 *      iter\dropWhile(fn\operator('>', 0), [3, 1, 4, -1, 5])
 *      => iter(-1, 5)
 *
 * @param callable $predicate Predicate: bool function(mixed $value)
 * @param array|Traversable $iterable Iterable to drop values from
 *
 * @return \Iterator
 */
function dropWhile(callable $predicate, $iterable) {
    _assertIterable($iterable, 'Second argument');
    $failed = false;
    foreach ($iterable as $key => $value) {
        if (!$failed && !$predicate($value)) {
            $failed = true;
        }

        if ($failed) {
            yield $key => $value;
        }
    }
}

/**
 * Takes an iterable containing any amount of nested iterables and returns
 * a flat iterable with just the values.
 *
 * The $level argument allows to limit flattening to a certain number of levels.
 *
 * Examples:
 *
 *      iter\flatten([1, [2, [3, 4]], [5]])
 *      => iter(1, 2, 3, 4, 5)
 *      iter\flatten([1, [2, [3, 4]], [5]], 1)
 *      => iter(1, 2, [3, 4], 5)
 *
 * @param array|Traversable $iterable Iterable to flatten
 * @param int               $levels   Number of levels to flatten
 *
 * @return \Iterator
 */
function flatten($iterable, $levels = INF) {
    _assertIterable($iterable, 'Argument');
    if ($levels < 0) {
        throw new \InvalidArgumentException(
            'Number of levels must be non-negative'
        );
    }

    if ($levels === 0) {
        // Flatten zero levels == do nothing
        foreach ($iterable as $k => $v) {
            yield $k => $v;
        }
    } else if ($levels === 1) {
        // Optimized implementation for flattening one level
        foreach ($iterable as $key => $value) {
            if (isIterable($value)) {
                foreach ($value as $k => $v) {
                    yield $k => $v;
                }
            } else {
                yield $key => $value;
            }
        }
    } else {
        // Otherwise flatten recursively
        foreach ($iterable as $key => $value) {
            if (isIterable($value)) {
                foreach (flatten($value, $levels - 1) as $k => $v) {
                    yield $k => $v;
                }
            } else {
                yield $key => $value;
            }
        }
    }
}

/**
 * Flips the keys and values of an iterable.
 *
 * Examples:
 *
 *      iter\flip(['a' => 1, 'b' => 2, 'c' => 3])
 *      => iter(1 => 'a', 2 => 'b', 3 => 'c')
 *
 * @param array|Traversable $iterable The iterable to flip
 *
 * @return \Iterator
 */
function flip($iterable) {
    _assertIterable($iterable, 'Argument');
    foreach ($iterable as $key => $value) {
        yield $value => $key;
    }
}

/**
 * Chunks an iterable into arrays of the specified size.
 *
 * Each chunk is an array (non-lazy), but the chunks are yielded lazily.
 *
 * Examples:
 *
 *      iter\chunk([1, 2, 3, 4, 5], 3)
 *      => iter([1, 2, 3], [4, 5])
 *
 * @param array|Traversable $iterable The iterable to chunk
 * @param int $size The size of each chunk
 * @param bool $preserveKeys Whether to preserve keys from the input iterable
 *
 * @throws \InvalidArgumentException if the chunk size is not positive
 *
 * @return \Iterator An iterator of arrays
 */
function chunk($iterable, $size, $preserveKeys = true) {
    _assertIterable($iterable, 'First argument');

    if ($size <= 0) {
        throw new \InvalidArgumentException('Chunk size must be positive');
    }

    $chunk = [];
    $count = 0;
    foreach ($iterable as $key => $value) {
        if ($preserveKeys) {
            $chunk[$key] = $value;
        } else {
            $chunk[] = $value;
        }

        $count++;
        if ($count === $size) {
            yield $chunk;
            $count = 0;
            $chunk = [];
        }
    }

    if ($count !== 0) {
        yield $chunk;
    }
}

/**
 * Joins the elements of an iterable with a separator between them.
 *
 * Examples:
 *
 *      iter\join(', ', ['a', 'b', 'c'])
 *      => "a, b, c"
 *
 * @param string $separator Separator to use between elements
 * @param array|Traversable $iterable The iterable to join
 *
 * @return string
 */
function join($separator, $iterable) {
    _assertIterable($iterable, 'Second argument');

    $str = '';
    $first = true;
    foreach ($iterable as $value) {
        if ($first) {
            $str .= $value;
            $first = false;
        } else {
            $str .= $separator . $value;
        }
    }
    return $str;
}

/**
 * Returns the number of elements an iterable contains.
 *
 * This function is not recursive, it counts only the number of elements in the
 * iterable itself, not its children.
 *
 * If the iterable implements Countable its count() method will be used.
 *
 * Examples:
 *
 *      iter\count([1, 2, 3])
 *      => 3
 *
 *      iter\count(iter\flatten([1, 2, 3, [4, [[[5, 6], 7]]], 8]))
 *      => 8
 *
 * @param array|Traversable|Countable $iterable The iterable to count
 *
 * @return int
 */
function count($iterable) {
    if (\is_array($iterable) || $iterable instanceof Countable) {
        return \count($iterable);
    }
    if (!$iterable instanceof \Traversable) {
        throw new \InvalidArgumentException(
            'Argument must be iterable or implement Countable');
    }

    $count = 0;
    foreach ($iterable as $_) {
        ++$count;
    }
    return $count;
}

/**
 * Determines whether iterable is empty.
 *
 * If the iterable implements Countable, its count() method will be used.
 * Calling isEmpty() does not drain iterators, as only the valid() method will
 * be called.
 *
 * @param array|Traversable|Countable $iterable
 * @return bool
 */
function isEmpty($iterable) {
    if (\is_array($iterable) || $iterable instanceof \Countable) {
        return count($iterable) == 0;
    }

    if ($iterable instanceof \Iterator) {
        return !$iterable->valid();
    } else if ($iterable instanceof \IteratorAggregate) {
        return !$iterable->getIterator()->valid();
    } else {
        throw new \InvalidArgumentException(
            'Argument must be iterable or implement Countable');
    }
}

/**
 * Recursively applies a function, working on entire iterables rather than
 * individual values.
 *
 * The function will be called both on the passed iterable and all iterables it
 * contains, etc. The call sequence is in post-order (inner before outer).
 *
 * Examples:
 *
 *     iter\recurse('iter\toArray',
 *          new ArrayIterator([1, 2, new ArrayIterator([3, 4])]));
 *     => [1, 2, [3, 4]]
 *
 * @param callable $function
 * @param $iterable
 * @return mixed
 */
function recurse(callable $function, $iterable) {
    _assertIterable($iterable, 'Second argument');
    return $function(map(function($value) use($function) {
        return isIterable($value) ? recurse($function, $value) : $value;
    }, $iterable));
}

/**
 * Converts any iterable into an Iterator.
 *
 * Examples:
 *
 *      iter\toIter([1, 2, 3])
 *      => iter(1, 2, 3)
 *
 * @param array|Traversable $iterable The iterable to turn into an iterator
 *
 * @return \Iterator
 */
function toIter($iterable) {
    if ($iterable instanceof \Iterator) {
        return $iterable;
    }
    if ($iterable instanceof \IteratorAggregate) {
        return $iterable->getIterator();
    }
    if (is_array($iterable)) {
        return new \ArrayIterator($iterable);
    }
    throw new \InvalidArgumentException('Argument must be iterable');
}

/**
 * Converts an iterable into an array, without preserving keys.
 *
 * Not preserving the keys is useful, because iterators do not necessarily have
 * unique keys and/or the key type is not supported by arrays.
 *
 * Examples:
 *
 *      iter\toArray(new ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]))
 *      => [1, 2, 3]
 *
 *      iter\toArray(iter\chain(['a' => 1, 'b' => 2], ['a' => 3]))
 *      => [1, 2, 3]
 *
 * @param array|Traversable $iterable The iterable to convert to an array
 *
 * @return array
 */
function toArray($iterable) {
    _assertIterable($iterable, 'Argument');
    $array = [];
    foreach ($iterable as $value) {
        $array[] = $value;
    }
    return $array;
}

/**
 * Converts an iterable into an array and preserves its keys.
 *
 * If the keys are not unique, newer keys will overwrite older keys. If a key
 * is not a string or an integer, the usual array key casting rules (and
 * associated notices/warnings) apply.
 *
 * Examples:
 *
 *      iter\toArrayWithKeys(new ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]))
 *      => ['a' => 1, 'b' => 2, 'c' => 3]
 *
 *      iter\toArrayWithKeys(iter\chain(['a' => 1, 'b' => 2], ['a' => 3]))
 *      => ['a' => 3, 'b' => 2]
 *
 * @param array|Traversable $iterable The iterable to convert to an array
 *
 * @return array
 */
function toArrayWithKeys($iterable) {
    _assertIterable($iterable, 'Argument');
    $array = [];
    foreach ($iterable as $key => $value) {
        $array[$key] = $value;
    }
    return $array;
}

/**
 * Determines whether a value is an iterable.
 *
 * Only arrays and objects implementing Traversable are considered as iterable.
 * In particular objects that don't implement Traversable are not considered as
 * iterable, even though PHP would accept them in a foreach() loop.
 *
 * Examples:
 *
 *     iter\isIterable([1, 2, 3])
 *     => true
 *
 *     iter\isIterable(new ArrayIterator([1, 2, 3]))
 *     => true
 *
 *     iter\isIterable(new stdClass)
 *     => false
 *
 * @param mixed $value Value to check
 *
 * @return bool Whether the passed value is an iterable
 */
function isIterable($value) {
    return is_array($value) || $value instanceof \Traversable;
}

function _assertIterable($value, $what) {
    if (!isIterable($value)) {
        throw new \InvalidArgumentException("$what must be iterable");
    }
}

function _assertAllIterable($values) {
    foreach ($values as $num => $value) {
        _assertIterable($value, 'Argument ' . ($num + 1));
    }
}

/*
 * Python:
 * compress()
 * groupby()
 * tee()
 * izip_longest()
 * multi-map
 */
