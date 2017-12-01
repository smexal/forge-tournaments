<?php

namespace Forge\Modules\ForgeTournaments\Calculations;

class CollectionTree {
    private $root = null;
    
    public function __construct($item) {
        $this->root = new CollectionNode($item);
    }

    public function getRoot() {
        return $this->root;
    }

    public function build($node=null) {
        if($node == null) {
            $node = $this->root;
        }
        $children = $node->buildChildren($node);
        $node->setChildren($children);
        foreach($children as $child) {
            $this->build($child);
        }
        return $node;
    }

    public function buildChildren($node) {
        $children_ids = $this->findChildren($node);
        foreach($children_ids as $item_id) {
            $node = new CollectionNode($item);
        }
    }

    private function findChildren($node) {
        $item = $node->getItem();
        $list = CollectionQuery::items([
            'parent' => $item->getID()
        ], CollectionQuery::AS_COLLECTIONS);
        return $list;
    }

}