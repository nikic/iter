<?php


namespace iter;


class FluentIterator implements \IteratorAggregate
{
    /**
     * @var iterable
     */
    private $iterable;

    public function __construct(iterable $iterable)
    {
        $this->iterable = $iterable;
    }

    public function getIterator()
    {
        return $this->iterable;
    }

    /**
     * @see \iter\map()
     */
    public function map(callable $function): self
    {
        return new static(map($function, $this->iterable));
    }

    /**
     * @see \iter\mapKeys()
     */
    public function mapKeys(callable $function): self
    {
        return new static(mapKeys($function, $this->iterable));
    }

    /**
     * @see \iter\flatMap()
     */
    public function flatMap(callable $function): self
    {
        return new static(flatMap($function, $this->iterable));
    }

    /**
     * @see \iter\reindex()
     */
    public function reindex(callable $function): self
    {
        return new static(reindex($function, $this->iterable));
    }

    /**
     * @see \iter\apply()
     */
    public function apply(callable $function): void
    {
        apply($function, $this->iterable);
    }

    /**
     * @see \iter\filter()
     */
    public function filter(callable $function): self
    {
        return new static(filter($function, $this->iterable));
    }

    /**
     * @see \iter\enumerate()
     */
    public function enumerate(): self
    {
        return $this->toPairs();
    }

    /**
     * @see \iter\toPairs()
     */
    public function toPairs(): self
    {
        return new static(toPairs($this->iterable));
    }

    /**
     * @see \iter\fromPairs()
     */
    public function fromPairs(): self
    {
        return new static(fromPairs($this->iterable));
    }

    /**
     * @see \iter\reduce()
     */
    public function reduce(callable $function, $startValue = null)
    {
        return reduce($function, $this->iterable, $startValue);
    }

    /**
     * @see \iter\reductions()
     */
    public function reductions(callable $function, $startValue = null): self
    {
        return new static(reductions($function, $this->iterable, $startValue));
    }

    /**
     * Combines this iterable for keys and another for values into one iterator.
     *
     * Examples:
     *
     *     new FluentIterator(['a', 'b', 'c'])->zipValues([1, 2, 3])
     *     => iter('a' => 1, 'b' => 2, 'c' => 3)
     *
     * @param iterable $values Iterable of values
     *
     * @return \Iterator
     */
    public function zipValues(iterable $values): self
    {
        return new static(zipKeyValue($this->iterable, $values));
    }

    /**
     * Combines this iterable for values and another for keys into one iterator.
     *
     * Examples:
     *
     *     (new FluentIterator(['a', 'b', 'c']))->zipKeys([1, 2, 3])
     *     => iter('a' => 1, 'b' => 2, 'c' => 3)
     *
     * @param iterable $values Iterable of values
     *
     * @return \Iterator
     */
    public function zipKeys(iterable $keys): self
    {
        return new static(zipKeyValue($keys, $this->iterable));
    }

    /**
     * @see \iter\chain()
     */
    public function chain(iterable $iterable): self
    {
        return new static(chain($this->iterable, $iterable));
    }

    /**
     * @see \iter\slice()
     */
    public function slice(int $start, $length = INF): self
    {
        return new static(slice($this->iterable, $start, $length));
    }

    /**
     * @see \iter\take()
     */
    public function take(int $num): self
    {
        return new static(take($num, $this->iterable));
    }

    /**
     * @see \iter\drop()
     */
    public function drop(int $num): self
    {
        return new static(drop($num, $this->iterable));
    }

    /**
     * @see \iter\keys()
     */
    public function keys(): self
    {
        return new static(keys($this->iterable));
    }

    /**
     * @see \iter\values()
     */
    public function values(): self
    {
        return new static(values($this->iterable));
    }

    /**
     * @see \iter\any()
     */
    public function any(callable $predicate): bool
    {
        return any($predicate, $this->iterable);
    }

    /**
     * @see \iter\all()
     */
    public function all(callable $predicate): bool
    {
        return all($predicate, $this->iterable);
    }

    /**
     * @see \iter\search()
     */
    public function search(callable $predicate)
    {
        return search($predicate, $this->iterable);
    }

    /**
     * @see \iter\takeWhile()
     */
    public function takeWhile(callable $predicate): self
    {
        return new static(takeWhile($predicate, $this->iterable));
    }

    /**
     * @see \iter\dropWhile()
     */
    public function dropWhile(callable $predicate): self
    {
        return new static(dropWhile($predicate, $this->iterable));
    }

    /**
     * @see \iter\flatten()
     */
    public function flatten(int $levels = PHP_INT_MAX): self
    {
        return new static(flatten($this->iterable, $levels));
    }

    /**
     * @see \iter\flip()
     */
    public function flip(): self
    {
        return new static(flip($this->iterable));
    }

    /**
     * @see \iter\chunk()
     */
    public function chunk(int $size, bool $preserveKeys = true): self
    {
        return new static(chunk($this->iterable, $size, $preserveKeys));
    }

    /**
     * @see \iter\join()
     */
    public function join(string $separator): string
    {
        return join($separator, $this->iterable);
    }

    /**
     * @see \iter\count()
     */
    public function count(): int
    {
        return count($this->iterable);
    }

    /**
     * @see \iter\isEmpty()
     */
    public function isEmpty(): bool
    {
        return isEmpty($this->iterable);
    }

    /**
     * @see \iter\recurse()
     */
    public function recurse(callable $function)
    {
        return recurse($function, $this->iterable);
    }

    /**
     * @see \iter\toArray()
     */
    public function toArray(): array
    {
        return toArray($this->iterable);
    }

    /**
     * @see \iter\toArrayWithKeys()
     */
    public function toArrayWithKeys(): array
    {
        return toArrayWithKeys($this->iterable);
    }

    /**
     * Passes the iterator through a callable that takes an iterator and returns an iterator.
     * This allows applying functions to the iterator instead of its elements.
     * Example:
     * ```php
     * $fluent
     *     ->map(...)
     *     ->via(function(FluentIterator $iterator) {
     *         return slice($iterator, 1, 5);
     *     })
     *     ->take(5);
     *
     *
     *
     * ```
     * @param callable $callable
     * @return iterable
     */
    public function via(callable $callable): self
    {
        return new static($callable($this));
    }
}
