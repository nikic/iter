<?php

namespace iter;

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
function range($start, $end, $step = null): \Iterator {
    if ($start == $end) {
        yield $start;
    } elseif ($start < $end) {
        if (null === $step) {
            $step = 1;
        } elseif ($step <= 0) {
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
        } elseif ($step >= 0) {
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
 *     iter\map(iter\func\operator('*', 2), [1, 2, 3, 4, 5]);
 *     => iter(2, 4, 6, 8, 10)
 *
 *     $column = map(iter\func\index('name'), $iter);
 *
 * @param callable $function Mapping function: mixed function(mixed $value)
 * @param iterable $iterable Iterable to be mapped over
 *
 * @return \Iterator
 */
function map(callable $function, iterable $iterable): \Iterator {
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
 * @param iterable $iterable Iterable those keys are to be mapped over
 *
 * @return \Iterator
 */
function mapKeys(callable $function, iterable $iterable): \Iterator {
    foreach ($iterable as $key => $value) {
        yield $function($key) => $value;
    }
}

/**
 * Applies a mapping function to all values of an iterator, passing both the key and the value into the callback.
 *
 * The function is passed the current iterator value and key and should return a
 * modified iterator value. The key is left as-is but passed to the mapping
 * function as the second parameter.
 *
 * Examples:
 *
 *     iter\mapWithKeys(iter\func\operator('*'), range(0, 5));
 *     => iter(0, 1, 4, 9, 16, 25)
 *
 *     iter\mapWithKeys(
 *         function ($v, $k) { return sprintf('%s%s', $k, $v); },
 *         ['foo' => 'bar', 'bing' => 'baz']
 *     );
 *     => iter(['foo' => 'foobar', 'bing' => 'bingbaz'])
 *
 * @param callable $function Mapping function: mixed function(mixed $value, mixed $key)
 * @param iterable $iterable Iterable to be mapped over
 *
 * @return \Iterator
 */
function mapWithKeys(callable $function, iterable $iterable): \Iterator {
    foreach ($iterable as $key => $value) {
        yield $key => $function($value, $key);
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
 * @param callable $function Mapping function: \Iterator function(mixed $value)
 * @param iterable $iterable Iterable to be mapped over
 *
 * @return \Iterator
 */
function flatMap(callable $function, iterable $iterable): \Iterator {
    foreach ($iterable as $value) {
        yield from $function($value);
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
 *     iter\reindex(iter\func\index('id'), $users)
 *     => iter(
 *         42 => ['id' => 42, 'name' => 'foo'],
 *         24 => ['id' => 24, 'name' => 'bar']
 *     )
 *
 * @param callable $function Mapping function mixed function(mixed $value)
 * @param iterable $iterable Iterable to reindex
 *
 * @return \Iterator
 */
function reindex(callable $function, iterable $iterable): \Iterator {
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
 *     iter\apply(iter\func\method('rewind'), $iterators);
 *
 * @param callable $function Apply function: void function(mixed $value)
 * @param iterable $iterable Iterator to apply on
 */
function apply(callable $function, iterable $iterable): void {
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
 *     iter\filter(iter\func\operator('<', 0), [0, -1, -10, 7, 20, -5, 7]);
 *     => iter(-1, -10, -5)
 *
 *     iter\filter(iter\func\operator('instanceof', 'SomeClass'), $objects);
 *
 * @param callable $predicate Predicate: bool function(mixed $value)
 * @param iterable $iterable Iterable to filter
 *
 * @return \Iterator
 */
function filter(callable $predicate, iterable $iterable): \Iterator {
    foreach ($iterable as $key => $value) {
        if ($predicate($value)) {
            yield $key => $value;
        }
    }
}

/**
 * Alias of toPairs().
 *
 * @param iterable $iterable Iterable to enumerate
 *
 * @return \Iterator
 */
function enumerate(iterable $iterable): \Iterator {
    return toPairs($iterable);
}

/**
 * Converts an iterable of key => value into an iterable of [key, value] pairs.
 *
 * Examples:
 *
 *      iter\toPairs(['a', 'b']);
 *      => iter([0, 'a'], [1, 'b'])
 *
 *      $values = ['a', 'b', 'c', 'd'];
 *      $filter = function($t) { return $t[0] % 2 == 0; };
 *      iter\fromPairs(iter\filter($filter, iter\toPairs($values)));
 *      => iter('a', 'c')
 *
 * @param iterable $iterable Iterable to convert to pairs
 *
 * @return \Iterator
 */
function toPairs(iterable $iterable): \Iterator {
    foreach ($iterable as $key => $value) {
        yield [$key, $value];
    }
}

/**
 * Converts an iterable of [key, value] pairs into a key => value iterable.
 *
 * This acts as an inverse to the toPairs() function.
 *
 * Examples:
 *
 *      iter\fromPairs([['a', 1], ['b', 2]])
 *      => iter('a' => 1, 'b' => 2)
 *
 *      $map = ['a' => 1, 'b' => 2];
 *      iter\fromPairs(iter\toPairs($map))
 *      => iter('a' => 1, 'b' => 2)
 *
 * @param iterable $iterable Iterable of [key, value] pairs
 *
 * @return \Iterator
 */
function fromPairs(iterable $iterable): \Iterator {
    foreach ($iterable as [$key, $value]) {
        yield $key => $value;
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
 *      iter\reduce(iter\func\operator('+'), range(1, 5), 0)
 *      => 15
 *      iter\reduce(iter\func\operator('*'), range(1, 5), 1)
 *      => 120
 *
 * @param callable $function Reduction function:
 *                           mixed function(mixed $acc, mixed $value, mixed $key)
 * @param iterable $iterable Iterable to reduce
 * @param mixed $startValue Start value for accumulator.
 *                          Usually identity value of $function.
 *
 * @return mixed Result of the reduction
 */
function reduce(callable $function, iterable $iterable, $startValue = null) {
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
 *      iter\reductions(iter\func\operator('+'), range(1, 5), 0)
 *      => iter(1, 3, 6, 10, 15)
 *      iter\reductions(iter\func\operator('*'), range(1, 5), 1)
 *      => iter(1, 2, 6, 24, 120)
 *
 * @param callable $function Reduction function:
 *                           mixed function(mixed $acc, mixed $value, mixed $key)
 * @param iterable $iterable Iterable to reduce
 * @param mixed $startValue Start value for accumulator.
 *                          Usually identity value of $function.
 *
 * @return \Iterator Intermediate results of the reduction
 */
function reductions(callable $function, iterable $iterable, $startValue = null): \Iterator {
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
 * @param iterable[] ...$iterables Iterables to zip
 *
 * @return \Iterator
 */
function zip(iterable ...$iterables): \Iterator {
    if (\count($iterables) === 0) {
        return;
    }

    $iterators = array_map('iter\\toIter', $iterables);
    for (
        apply(func\method('rewind'), $iterators);
        all(func\method('valid'), $iterators);
        apply(func\method('next'), $iterators)
    ) {
        yield toArray(map(func\method('key'), $iterators))
           => toArray(map(func\method('current'), $iterators));
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
 * @param iterable $keys Iterable of keys
 * @param iterable $values Iterable of values
 *
 * @return \Iterator
 */
function zipKeyValue(iterable $keys, iterable $values): \Iterator {
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
 * @param iterable[] ...$iterables Iterables to chain
 *
 * @return \Iterator
 */
function chain(iterable ...$iterables): \Iterator {
    foreach ($iterables as $iterable) {
        yield from $iterable;
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
 * @param iterable[] ...$iterables Iterables to combine
 *
 * @return \Iterator
 */
function product(iterable ...$iterables): \Iterator {
    /** @var \Iterator[] $iterators */
    $iterators = array_map('iter\\toIter', $iterables);
    $numIterators = \count($iterators);
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
 * @param iterable $iterable Iterable to take the slice from
 * @param int $start Start offset
 * @param int $length Length (if not specified all remaining values from the
 *                    iterable are used)
 *
 * @return \Iterator
 */
function slice(iterable $iterable, int $start, $length = INF): \Iterator {
    if ($start < 0) {
        throw new \InvalidArgumentException('Start offset must be non-negative');
    }
    if ($length < 0) {
        throw new \InvalidArgumentException('Length must be non-negative');
    }
    if ($length === 0) {
        return;
    }

    $i = 0;
    foreach ($iterable as $key => $value) {
        if ($i++ < $start) {
            continue;
        }
        yield $key => $value;
        if ($i >= $start + $length) {
            break;
        }
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
 * @param iterable $iterable Iterable to take the elements from
 *
 * @return \Iterator
 */
function take(int $num, iterable $iterable): \Iterator {
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
 * @param iterable $iterable Iterable to drop the elements from
 *
 * @return \Iterator
 */
function drop(int $num, iterable $iterable): \Iterator {
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
function repeat($value, $num = INF): \Iterator {
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
 * @param iterable $iterable Iterable to get keys from
 *
 * @return \Iterator
 */
function keys(iterable $iterable): \Iterator {
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
 * @param iterable $iterable Iterable to get values from
 *
 * @return \Iterator
 */
function values(iterable $iterable): \Iterator {
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
 *      iter\all(iter\func\operator('>', 0), range(1, 10))
 *      => true
 *      iter\all(iter\func\operator('>', 0), range(-5, 5))
 *      => false
 *
 * @param callable $predicate Predicate: bool function(mixed $value)
 * @param iterable $iterable Iterable to check against the predicate
 *
 * @return bool Whether the predicate matches any value
 */
function any(callable $predicate, iterable $iterable): bool {
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
 *      iter\all(iter\func\operator('>', 0), range(1, 10))
 *      => true
 *      iter\all(iter\func\operator('>', 0), range(-5, 5))
 *      => false
 *
 * @param callable $predicate Predicate: bool function(mixed $value)
 * @param iterable $iterable Iterable to check against the predicate
 *
 * @return bool Whether the predicate holds for all values
 */
function all(callable $predicate, iterable $iterable): bool {
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
 *      iter\search(iter\func\operator('===', 'baz'), ['foo', 'bar', 'baz'])
 *      => 'baz'
 *
 *      iter\search(iter\func\operator('===', 'qux'), ['foo', 'bar', 'baz'])
 *      => null
 *
 * @param callable $predicate Predicate: bool function(mixed $value)
 * @param iterable $iterable The iterable to search
 *
 * @return null|mixed
 */
function search(callable $predicate, iterable $iterable) {
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
 *      iter\takeWhile(iter\func\operator('>', 0), [3, 1, 4, -1, 5])
 *      => iter(3, 1, 4)
 *
 * @param callable $predicate Predicate: bool function(mixed $value)
 * @param iterable $iterable Iterable to take values from
 *
 * @return \Iterator
 */
function takeWhile(callable $predicate, iterable $iterable): \Iterator {
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
 *      iter\dropWhile(iter\func\operator('>', 0), [3, 1, 4, -1, 5])
 *      => iter(-1, 5)
 *
 * @param callable $predicate Predicate: bool function(mixed $value)
 * @param iterable $iterable Iterable to drop values from
 *
 * @return \Iterator
 */
function dropWhile(callable $predicate, iterable $iterable): \Iterator {
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
 * @param iterable $iterable Iterable to flatten
 * @param int $levels Number of levels to flatten
 *
 * @return \Iterator
 */
function flatten(iterable $iterable, $levels = INF): \Iterator {
    if ($levels < 0) {
        throw new \InvalidArgumentException(
            'Number of levels must be non-negative'
        );
    }

    if ($levels === 0) {
        // Flatten zero levels == do nothing
        yield from $iterable;
    } else if ($levels === 1) {
        // Optimized implementation for flattening one level
        foreach ($iterable as $key => $value) {
            if (isIterable($value)) {
                yield from $value;
            } else {
                yield $key => $value;
            }
        }
    } else {
        // Otherwise flatten recursively
        foreach ($iterable as $key => $value) {
            if (isIterable($value)) {
                yield from flatten($value, $levels - 1);
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
 * @param iterable $iterable The iterable to flip
 *
 * @return \Iterator
 */
function flip(iterable $iterable): \Iterator {
    foreach ($iterable as $key => $value) {
        yield $value => $key;
    }
}

/**
 * Chunks an iterable into arrays of the specified size.
 *
 * Each chunk is an array (non-lazy), but the chunks are yielded lazily.
 * By default keys are not preserved.
 *
 * Examples:
 *
 *      iter\chunk([1, 2, 3, 4, 5], 2)
 *      => iter([1, 2], [3, 4], [5])
 *
 * @param iterable $iterable The iterable to chunk
 * @param int $size The size of each chunk
 * @param bool $preserveKeys Whether to preserve keys from the input iterable
 *
 * @return \Iterator An iterator of arrays
 */
function chunk(iterable $iterable, int $size, bool $preserveKeys = false): \Iterator {
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
 * The same as chunk(), but preserving keys.
 *
 * Examples:
 *
 *     iter\chunkWithKeys(['a' => 1, 'b' => 2, 'c' => 3], 2)
 *     => iter(['a' => 1, 'b' => 2], ['c' => 3])
 *
 * @param iterable $iterable The iterable to chunk
 * @param int $size The size of each chunk
 *
 * @return \Iterator An iterator of arrays
 */
function chunkWithKeys(iterable $iterable, int $size): \Iterator {
    return chunk($iterable, $size, true);
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
 * @param iterable $iterable The iterable to join
 *
 * @return string
 */
function join(string $separator, iterable $iterable): string {
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
 * Splits a string by a separator
 *
 * Examples:
 *
 *      iter\split(', ', 'a, b, c')
 *      => iter('a', 'b', 'c')
 *
 * @param string $separator Separator to use between elements
 * @param string $data The string to split
 *
 * @return iterable
 */
function split(string $separator, string $data): iterable
{
    if (\strlen($separator) === 0) {
        throw new \InvalidArgumentException('Separator must be non-empty string');
    }

    return (function() use ($separator, $data) {
        $offset = 0;
        while (
            $offset < \strlen($data)
            && false !== $nextOffset = strpos($data, $separator, $offset)
        ) {
            yield \substr($data, $offset, $nextOffset - $offset);
            $offset = $nextOffset + \strlen($separator);
        }
        yield \substr($data, $offset);
    })();
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
 * @param iterable|\Countable $iterable The iterable to count
 *
 * @return int
 */
function count($iterable): int {
    if (\is_array($iterable) || $iterable instanceof \Countable) {
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
 * @param iterable|\Countable $iterable
 * @return bool
 */
function isEmpty($iterable): bool {
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
 * @param iterable $iterable
 * @return mixed
 */
function recurse(callable $function, iterable $iterable) {
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
 * @param iterable $iterable The iterable to turn into an iterator
 *
 * @return \Iterator
 */
function toIter(iterable $iterable): \Iterator {
    if (\is_array($iterable)) {
        return new \ArrayIterator($iterable);
    }

    if ($iterable instanceof \Iterator) {
        return $iterable;
    }
    if ($iterable instanceof \IteratorAggregate) {
        return $iterable->getIterator();
    }

    // Traversable, but not Iterator or IteratorAggregate
    $generator = function() use($iterable) {
        yield from $iterable;
    };
    return $generator();
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
 * @param iterable $iterable The iterable to convert to an array
 *
 * @return array
 */
function toArray(iterable $iterable): array {
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
 * @param iterable $iterable The iterable to convert to an array
 *
 * @return array
 */
function toArrayWithKeys(iterable $iterable): array {
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
/*
 * Python:
 * compress()
 * groupby()
 * tee()
 * izip_longest()
 * multi-map
 */
