<?php

namespace iter {
    /**
     * Converts a generator function into a rewindable generator function.
     *
     * This is implemented simply be remembering the arguments with which the
     * generator function is later called and just calling it again if a
     * rewind() occurs.
     *
     * Example:
     *
     *      $rewindableMap = iter\makeRewindable('iter\\map');
     *      $res = $rewindableMap(fn\operator('*', 3), [1, 2, 3]);
     *      // $res is a rewindable iterator with elements [3, 6, 9]
     *
     * @param callable $function Generator function to make rewindable
     *
     * @return callable Rewindable generator function
     */
    function makeRewindable(callable $function) {
        return function() use ($function) {
            return new rewindable\_RewindableGenerator($function, func_get_args());
        };
    }

    /**
     * Call a generator function, but make the result rewindable.
     *
     * This function does basically the same thing as makeRewindable(), but it
     * directly calls the function, rather than returning a lambda. Useful if
     * you want to do a one-off call, rather than using the rewindable function
     * multiple times.
     *
     * Example:
     *
     *      $res = iter\callRewindable('iter\\map', fn\operator('*', 3), [1, 2, 3]);
     *      // $res is a rewindable iterator with elements [3, 6, 9]
     *
     * @param callable $function Generator function to call rewindably
     *
     * @return \Iterator Rewindable generator result
     */
    function callRewindable(callable $function /*, ... $args */) {
        return new rewindable\_RewindableGenerator($function, array_slice(func_get_args(), 1));
    }
}

namespace iter\rewindable {
    /**
     * These functions are just rewindable wrappers around the normal
     * non-rewindable functions from the iter namespace
     */

    function range()       { return new _RewindableGenerator('iter\range',       func_get_args()); }
    function map()         { return new _RewindableGenerator('iter\map',         func_get_args()); }
    function mapKeys()     { return new _RewindableGenerator('iter\mapKeys',     func_get_args()); }
    function flatMap()     { return new _RewindableGenerator('iter\flatMap',     func_get_args()); }
    function reindex()     { return new _RewindableGenerator('iter\reindex',     func_get_args()); }
    function filter()      { return new _RewindableGenerator('iter\filter',      func_get_args()); }
    function enumerate()   { return new _RewindableGenerator('iter\enumerate',   func_get_args()); }
    function toPairs()     { return new _RewindableGenerator('iter\toPairs',     func_get_args()); }
    function fromPairs()   { return new _RewindableGenerator('iter\fromPairs',   func_get_args()); }
    function reductions()  { return new _RewindableGenerator('iter\reductions',  func_get_args()); }
    function zip()         { return new _RewindableGenerator('iter\zip',         func_get_args()); }
    function zipKeyValue() { return new _RewindableGenerator('iter\zipKeyValue', func_get_args()); }
    function chain()       { return new _RewindableGenerator('iter\chain',       func_get_args()); }
    function product()     { return new _RewindableGenerator('iter\product',     func_get_args()); }
    function slice()       { return new _RewindableGenerator('iter\slice',       func_get_args()); }
    function take()        { return new _RewindableGenerator('iter\take',        func_get_args()); }
    function drop()        { return new _RewindableGenerator('iter\drop',        func_get_args()); }
    function repeat()      { return new _RewindableGenerator('iter\repeat',      func_get_args()); }
    function takeWhile()   { return new _RewindableGenerator('iter\takeWhile',   func_get_args()); }
    function dropWhile()   { return new _RewindableGenerator('iter\dropWhile',   func_get_args()); }
    function keys()        { return new _RewindableGenerator('iter\keys',        func_get_args()); }
    function values()      { return new _RewindableGenerator('iter\values',      func_get_args()); }
    function flatten()     { return new _RewindableGenerator('iter\flatten',     func_get_args()); }
    function flip()        { return new _RewindableGenerator('iter\flip',        func_get_args()); }
    function chunk()       { return new _RewindableGenerator('iter\chunk',       func_get_args()); }

    /**
     * This class is used for the internal implementation of rewindable
     * generators. Should not be used directly, instead use makeRewindable() or
     * callRewindable().
     *
     * @internal
     */
    class _RewindableGenerator implements \Iterator {
        protected $function;
        protected $args;
        /** @var \Generator */
        protected $generator;

        public function __construct(callable $function, array $args) {
            $this->function = $function;
            $this->args = $args;
            $this->generator = null;
        }

        public function rewind() {
            $this->generator = call_user_func_array($this->function, $this->args);
        }

        public function next() {
            if (!$this->generator) { $this->rewind(); }
            $this->generator->next();
        }

        public function valid() {
            if (!$this->generator) { $this->rewind(); }
            return $this->generator->valid();
        }

        public function key() {
            if (!$this->generator) { $this->rewind(); }
            return $this->generator->key();
        }

        public function current() {
            if (!$this->generator) { $this->rewind(); }
            return $this->generator->current();
        }

        public function send($value = null) {
            if (!$this->generator) { $this->rewind(); }
            return $this->generator->send($value);
        }

        public function __call($method, $args) {
            if ($method === 'throw') {
                if (!$this->generator) { $this->rewind(); }
                return call_user_func_array([$this->generator, 'throw'], $args);
            } else {
                // trigger normal undefined method error
                return call_user_func_array([$this, $method], $args);
            }
        }
    }
}
