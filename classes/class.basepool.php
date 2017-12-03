<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Modules\ForgeTournaments\Interfaces\IPool;

abstract class BasePool implements IPool {

    protected $instances = [];
    protected $max_pool_size = 64; 

    public function __construct($max_pool_size=64) {
        $this->max_pool_size = $max_pool_size;
    }

    public function hasInstance($id) {
        return isset($this->instances[$id]);
    }

    public function getInstance($id, $args=[]) {
        if(!$this->hasInstance($id)) {
            $instance = $this->buildInstance($id, $args);
            $this->registerInstance($id, $instance);
        }
        return $this->instances[$id];
    }

    public function setInstance($id, $instance) {
        $this->registerInstance($id, $instance);
    }

    public function removeInstance($id) {
        if($this->hasInstance($id)) {
            unset($this->instances[$id]);
        }
    }

    abstract protected function buildInstance($id, $args=[]);


    protected function registerInstance($id, $instance) {
        if(count($this->instances) >= $this->max_pool_size) {
            $first_id = current(array_keys($this->instances));
            $this->removeInstance($first_id);
        }
        $this->instances[$id] = $instance;
    }

    public function getMaxPoolSize($max_pool_size=64) {
        $this->max_pool_size = $max_pool_size;
    }

}