<?php

namespace iter {
    /**
     * Creates a rewindable iterator from a non-rewindable iterator.
     *
     * This is implemented simply be remembering the arguments with which the
     * factory function is later called and just calling it again if a
     * rewind() occurs.
     *
     * Example:
     *
     *      $rewindableMap = iter\makeRewindable('iter\\map');
     *      $res = $rewindableMap(func\operator('*', 3), [1, 2, 3]);
     *      // $res is a rewindable iterator with elements [3, 6, 9]
     *
     * @template TKey
     * @template TValue
     *
     * @param callable(...mixed):\Iterator<TKey,TValue> $function Iterator factory function
     *
     * @return callable(...mixed):\Iterator<TKey,TValue> Rewindable iterator factory function
     */
    function makeRewindable(callable $function) {
        return function(...$args) use ($function) {
            return new rewindable\_RewindableIterator($function, $args);
        };
    }

    /**
     * Creates a rewindable iterator from a non-rewindable iterator.
     *
     * This function does basically the same thing as makeRewindable(),
     * but it directly calls the function, rather than returning a lambda.
     * Useful if you want to do a one-off call, rather than using the rewindable
     * function multiple times.
     *
     * Example:
     *
     *      $res = iter\callRewindable('iter\\map', func\operator('*', 3), [1, 2, 3]);
     *      // $res is a rewindable iterator with elements [3, 6, 9]
     *
     * @template TKey
     * @template TValue
     *
     * @param callable(...mixed):\Iterator<TKey,TValue> $function Iterator factory function
     * @param mixed ...$args Function arguments
     *
     * @return \Iterator<TKey,TValue> Rewindable generator result
     */
    function callRewindable(callable $function, ...$args) {
        return new rewindable\_RewindableIterator($function, $args);
    }
}

namespace iter\rewindable {

    use ReturnTypeWillChange;

    /**
     * These functions are just rewindable wrappers around the normal
     * non-rewindable functions from the iter namespace
     */

    function range()         { return new _RewindableIterator('iter\range',         func_get_args()); }
    function map()           { return new _RewindableIterator('iter\map',           func_get_args()); }
    function mapKeys()       { return new _RewindableIterator('iter\mapKeys',       func_get_args()); }
    function mapWithKeys()   { return new _RewindableIterator('iter\mapWithKeys',   func_get_args()); }
    function flatMap()       { return new _RewindableIterator('iter\flatMap',       func_get_args()); }
    function reindex()       { return new _RewindableIterator('iter\reindex',       func_get_args()); }
    function filter()        { return new _RewindableIterator('iter\filter',        func_get_args()); }
    function enumerate()     { return new _RewindableIterator('iter\enumerate',     func_get_args()); }
    function toPairs()       { return new _RewindableIterator('iter\toPairs',       func_get_args()); }
    function fromPairs()     { return new _RewindableIterator('iter\fromPairs',     func_get_args()); }
    function reductions()    { return new _RewindableIterator('iter\reductions',    func_get_args()); }
    function zip()           { return new _RewindableIterator('iter\zip',           func_get_args()); }
    function zipKeyValue()   { return new _RewindableIterator('iter\zipKeyValue',   func_get_args()); }
    function chain()         { return new _RewindableIterator('iter\chain',         func_get_args()); }
    function product()       { return new _RewindableIterator('iter\product',       func_get_args()); }
    function slice()         { return new _RewindableIterator('iter\slice',         func_get_args()); }
    function take()          { return new _RewindableIterator('iter\take',          func_get_args()); }
    function drop()          { return new _RewindableIterator('iter\drop',          func_get_args()); }
    function repeat()        { return new _RewindableIterator('iter\repeat',        func_get_args()); }
    function takeWhile()     { return new _RewindableIterator('iter\takeWhile',     func_get_args()); }
    function dropWhile()     { return new _RewindableIterator('iter\dropWhile',     func_get_args()); }
    function keys()          { return new _RewindableIterator('iter\keys',          func_get_args()); }
    function values()        { return new _RewindableIterator('iter\values',        func_get_args()); }
    function flatten()       { return new _RewindableIterator('iter\flatten',       func_get_args()); }
    function flip()          { return new _RewindableIterator('iter\flip',          func_get_args()); }
    function chunk()         { return new _RewindableIterator('iter\chunk',         func_get_args()); }
    function chunkWithKeys() { return new _RewindableIterator('iter\chunkWithKeys', func_get_args()); }

    /**
     * This class is used for the internal implementation of iterators that are
     * otherwise not rewindable. Should not be used directly, instead use
     * makeRewindable() or callRewindable().
     *
     * @template TKey
     * @template TValue
     *
     * @implements \Iterator<TKey,TValue>
     *
     * @internal
     */
    class _RewindableIterator implements \Iterator {
        /** @var callable */
        protected $function;

        /** @var mixed[] */
        protected $args;

        /** @var \Iterator<TKey,TValue> */
        protected $iterator;

        /**
         * @param callable(...mixed):\Iterator<TKey,TValue> $function
         * @param mixed[] $args
         */
        public function __construct(callable $function, array $args) {
            $this->function = $function;
            $this->args = $args;
            $this->iterator = null;
        }

        public function rewind(): void {
            $function = $this->function;
            $this->iterator = $function(...$this->args);
        }

        public function next(): void {
            if (!$this->iterator) { $this->rewind(); }
            $this->iterator->next();
        }

        public function valid(): bool {
            if (!$this->iterator) { $this->rewind(); }
            return $this->iterator->valid();
        }

        /**
         * @return mixed
         */
        #[ReturnTypeWillChange]
        public function key() {
            if (!$this->iterator) { $this->rewind(); }
            return $this->iterator->key();
        }

        /**
         * @return mixed
         */
        #[ReturnTypeWillChange]
        public function current() {
            if (!$this->iterator) { $this->rewind(); }
            return $this->iterator->current();
        }
    }

    /**
     * This class was used for the internal implementation of rewindable
     * generators. This has been deprecated in favor of the more general
     * _RewindableIterator class, and may be removed in a future version.
     *
     * @template TKey
     * @template TYield
     * @template TSend
     * @template TReturn
     *
     * @extends _RewindableIterator<TKey,TYield>
     *
     * @internal
     * @deprecated
     */
    class _RewindableGenerator extends _RewindableIterator {
        /** @var \Generator<TKey,TYield,TSend,TReturn> */
        protected $iterator;

        /**
         * @param callable(...mixed):\Generator<TKey,TYield,TSend,TReturn> $function
         * @param mixed[] $args
         */
        public function __construct(callable $function, array $args) {
            $this->function = $function;
            $this->args = $args;
            $this->iterator = null;
        }

        /**
         * @param mixed $value
         * @return TYield|null
         */
        public function send($value = null) {
            if (!$this->iterator) { $this->rewind(); }
            return $this->iterator->send($value);
        }

        /**
         * @param \Throwable $exception
         * @return TYield
         */
        public function throw($exception) {
            if (!$this->iterator) { $this->rewind(); }
            return $this->iterator->throw($exception);
        }
    }
}
