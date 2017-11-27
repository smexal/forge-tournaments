<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Modules\ForgeTournaments\Interfaces\IBasePool;

class GenericPool extends BasePool {

    protected $ns_class = null;

    public function __construct($ns_class, $max_pool_size=64) {
        $this->ns_class = $ns_class;
        parent::__construct($max_pool_size);
    }

    public function buildInstance($id, $args=[]) {
        $args = !is_array($args) ? [$args] : $args;
        $r = new \ReflectionClass($this->ns_class);
        return $r->newInstanceArgs($args);
    }
}