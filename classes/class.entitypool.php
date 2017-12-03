<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Modules\ForgeTournaments\Interfaces\IBasePool;

class EntityPool extends GenericPool {
    public function __construct($ns_class, $max_pool_size=64) {
        parent::__construct($ns_class, $max_pool_size);
        \registerEvent('Forge/Core/CollectionItem/delete', [$this, 'cleanupOnDelete']);
    }

    public function cleanupOnDelete($item) {
        if(!$this->hasInstance($item->getID())) {
            return;
        }
        $this->removeInstance($item->getID());
    }
}