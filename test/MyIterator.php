<?php

namespace iter;

class MyIterator implements \Iterator {
    private $position = 0;
    private $array = [
        "firstelement",
        "secondelement",
        "lastelement",
    ];

    public function __construct() {
        $this->position = 0;
    }

    public function rewind() {
        $this->position = 0;
    }

    public function current() {
        return $this->array[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return isset($this->array[$this->position]);
    }
}
