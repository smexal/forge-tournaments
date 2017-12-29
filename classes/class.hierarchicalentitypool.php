<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Modules\ForgeTournaments\Interfaces\IBasePool;

class HierarchicalEntityPool extends EntityPool {
    public function __construct($ns_class, $max_pool_size=64) {
        parent::__construct($ns_class, $max_pool_size);
    }

    public function buildInstance($id, $args=[]) {
        $args = !is_array($args) ? [$args] : $args;
        if(count($args) == 0) {
            if(is_numeric($id)) {
                $args[0] = new \Forge\Core\Classes\CollectionItem($id);
            } else {
                $args[0] = $id;
                $id = $id->getID();
            }
        }
        return parent::buildInstance($id, $args);
    }

}