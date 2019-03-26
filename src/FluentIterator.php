<?php


namespace iter;


use phpDocumentor\Reflection\Types\Static_;
use Traversable;

class FluentIterator implements \IteratorAggregate
{
    /**
     * @var iterable
     */
    private $iterator;

    public function __construct(iterable $iterator)
    {
        $this->iterator = $iterator;
    }

    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * @see \iter\map()
     */
    public function map(callable $function): self
    {
        return new static(map($function, $this->iterator));
    }

    /**
     * @see \iter\mapKeys()
     */
    public function mapKeys(callable $function): self
    {
        return new static(mapKeys($function, $this->iterator));
    }

    /**
     * @see \iter\flatMap()
     */
    public function flatMap(callable $function): self
    {
        return new static(flatMap($function, $this->iterator));
    }

    /**
     * @see \iter\reindex()
     */
    public function reindex(callable $function): self
    {
        return new static(reindex($function, $this->iterator));
    }

    /**
     * @see \iter\apply()
     */
    public function apply(callable $function): void
    {
        apply($function, $this->iterator);
    }

    /**
     * @see \iter\apply()
     */
    public function filter(callable $function): self
    {
        return new static(filter($function, $this->iterator));
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
        return new static(toPairs($this->iterator));
    }

    /**
     * @see \iter\fromPairs()
     */
    public function fromPairs(): self
    {
        return new static(fromPairs($this->iterator));
    }

    /**
     * @see \iter\reduce()
     */
    public function reduce(callable $function, $startValue = null)
    {
        return reduce($function, $this->iterator, $startValue);
    }

    /**
     * @see \iter\reductions()
     */
    public function reductions(callable $function, $startValue = null): self
    {
        return new static(reductions($function, $this->iterator, $startValue));
    }

    /**
     * Combines this iterable for keys and another for values into one iterator.
     *
     * Examples:
     *
     *     new FluentIterator(['a', 'b', 'c'])->zipKeys([1, 2, 3])
     *     => iter('a' => 1, 'b' => 2, 'c' => 3)
     *
     * @param iterable $values Iterable of values
     *
     * @return \Iterator
     */
    public function zipValues(iterable $values): self
    {
        return new static(zipKeyValue($this->iterator, $values));
    }

    /**
     * Combines this iterable for values and another for keys into one iterator.
     *
     * Examples:
     *
     *     new FluentIterator(['a', 'b', 'c'])->zipKeys([1, 2, 3])
     *     => iter('a' => 1, 'b' => 2, 'c' => 3)
     *
     * @param iterable $values Iterable of values
     *
     * @return \Iterator
     */
    public function zipKeys(iterable $keys): self
    {
        return new static(zipKeyValue($keys, $this->iterator));
    }

    /**
     * @see \iter\chain()
     */
    public function chain(Iterable $iterable): self
    {
        return new static(chain($this->iterator, $iterable));
    }

    /**
     * @see \iter\slice()
     */
    public function slice(int $start, $length = INF): self
    {
        return new static(slice($this->iterator, $start, $length));
    }

    /**
     * @see \iter\take()
     */
    public function take(int $num): self
    {
        return new static(take($num, $this->iterator));
    }

    /**
     * @see \iter\drop()
     */
    public function drop(int $num): self
    {
        return new static(drop($num, $this->iterator));
    }

    /**
     * @see \iter\keys()
     */
    public function keys(): self
    {
        return new static(keys($this->iterator));
    }

    /**
     * @see \iter\values()
     */
    public function values(): self
    {
        return new static(values($this->iterator));
    }

    /**
     * @see \iter\any()
     */
    public function any(callable $predicate): bool
    {
        return any($predicate, $this->iterator);
    }

    /**
     * @see \iter\all()
     */
    public function all(callable $predicate): bool
    {
        return all($predicate, $this->iterator);
    }

    /**
     * @see \iter\search()
     */
    public function search(callable $predicate)
    {
        return search($predicate, $this->iterator);
    }

    /**
     * @see \iter\takeWhile()
     */
    public function takeWhile(callable $predicate): self
    {
        return new static(takeWhile($predicate, $this->iterator));
    }

    /**
     * @see \iter\dropWhile()
     */
    public function dropWhile(callable $predicate): self
    {
        return new static(dropWhile($predicate, $this->iterator));
    }

    /**
     * @see \iter\flatten()
     */
    public function flatten(int $levels = PHP_INT_MAX): self
    {
        return new static(flatten($this->iterator, $levels));
    }

    /**
     * @see \iter\flip()
     */
    public function flip(): self
    {
        return new static(flip($this->iterator));
    }

    /**
     * @see \iter\chunk()
     */
    public function chunk(int $size, bool $preserveKeys = true): self
    {
        return new static(chunk($this->iterator, $size, $preserveKeys));
    }

    /**
     * @see \iter\join()
     */
    public function join(string $separator): string
    {
        return join($separator, $this->iterator);
    }

    /**
     * @see \iter\count()
     */
    public function count(): int
    {
        return count($this->iterator);
    }

    /**
     * @see \iter\isEmpty()
     */
    public function isEmpty(): bool
    {
        return isEmpty($this->iterator);
    }

    /**
     * @see \iter\recurse()
     */
    public function recurse(callable $function)
    {
        return recurse($function, $this->iterator);
    }

    /**
     * @see \iter\toArray()
     */
    public function toArray(): array
    {
        return toArray($this->iterator);
    }

    /**
     * @see \iter\toArrayWithKeys()
     */
    public function toArrayWithKeys(): array
    {
        return toArrayWithKeys($this->iterator);
    }

    /**
     * Passes the iterator through a callable that takes an iterator and returns an iterator.
     * This allows applying functions to the iterator instead of its element.
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
        return new static(call_user_func($callable, $this));
    }
}