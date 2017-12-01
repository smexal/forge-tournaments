<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Core\Traits\Singleton;

class PoolRegistry {
    use Singleton;

    protected $pools = [];


    protected function __construct() {}
    
    public function add($id, $pool) {
        $this->pools[$id] = $pool;
    }

    public function getPool($id) {
        return $this->pools[$id];
    }
}