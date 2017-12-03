<?php

namespace Forge\Modules\ForgeTournaments\Calculations;

use Forge\Modules\ForgeTournaments\Calculations\Nodes\CollectionNode;

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
        $children = $this->buildChildren($node);
        $node->setChildren($children);
        foreach($children as $child) {
            $this->build($child);
        }
        return $node;
    }

    public function buildChildren($node) {
        $children = $node->getItem()->getChildren();
        foreach($children as $key => $item) {
            $children[$key] = new CollectionNode($item);
        }
        return $children;
    }
}