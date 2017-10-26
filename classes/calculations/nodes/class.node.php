<?php

namespace Forge\Modules\ForgeTournaments\Calculations;

use Forge\Modules\ForgeTournaments\Interfaces\INode;

class Node implements INode {
    private $is_root;
    private $parent;
    private $children;

    public function __construct() {}

    public function setParent(INode $parent) {
        $this->parent = $parent;
    }

    public function hasParent() : bool {
        return !is_null($this->parent);
    }

    public function addChild(INode $node) {
        $this->children[] = $child;
        $child->setParent($this);
    }

    public function removeChild(INode $node) {
        $idx = array_search($node, $this->children, true);
        if(!is_numeric($idx)) {
            throw new \Exception("Node not found in childrens");
        }
        $node = $this->children[$idx];
        unset($this->children[$idx]);
        $node->setParent(null);
    }

    public function addChildren(array $children) {
        foreach($children as $node) {
            $this->addChild($node);
        }
    }

    public function hasChildren() : bool {
        return count($this->children) == 0;
    }

    public function getChildren() : INode {
        return $this->children;
    }

}