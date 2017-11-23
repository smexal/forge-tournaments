<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Modules\ForgeTournaments\Interfaces\IPool;

abstract class BasePool implements IPool {

    protected $instances = [];
    protected $max_pool_size = 64; 

    public function __construct($max_pool_size=64) {
        $this->max_pool_size = $max_pool_size;
    }

    public function getInstance($id, $args=[]) {
        if(!isset($this->instances[$id])) {
            $instance = $this->buildInstance($id, $args);
            $this->registerInstance($id, $instance);
        }
        return $this->instances[$id];
    }

    abstract protected function buildInstance($id, $args=[]);

    protected function registerInstance($id, $instance) {
        $this->instances[$id] = $instance;
    }

    public function getMaxPoolSize($max_pool_size=64) {
        $this->max_pool_size = $max_pool_size;
    }

}