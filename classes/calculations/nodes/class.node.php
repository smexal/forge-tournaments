<?php

namespace Forge\Modules\ForgeTournaments\Calculations;

use Forge\Modules\ForgeTournaments\Interfaces\INode;

class Node implements INode {
    private static $counter = 0;

    private $is_root = false;
    private $parent = null;
    private $children = [];

    private $identifier;

    public function __construct($identifier=null) {
        $this->identifier = !is_null($identifier) ? $identifier : static::$counter++;
    }

    public function getIdentifier() {
        return $this->identifier;
    }

    public function setIdentifier($identifier) {
        $this->identifier = $identifier;
    }

    public function setParent(INode $parent) {
        $this->parent = $parent;
    }

    public function getParent() {
        return $this->parent;
    }

    public function hasParent() : bool {
        return !is_null($this->parent);
    }

    public function addChild(INode $node) {
        $this->children[] = $node;
        $node->setParent($this);
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

    public function getChildren() : array {
        return $this->children;
    }


    public function getDepth() {
        return $this->getNodeDepth($this);
    }

    private function getNodeDepth(INode $node, $current_depth=0) {
        $depth = $current_depth;
        foreach($node->getChildren() as $child) {
            $child_depth = $this->getNodeDepth($child, $current_depth + 1);
            $depth = $depth < $child_depth ? $child_depth : $depth;
        }
        return $depth;
    }

    public function identifierArray() {
        $tree = ['id' => $this->getIdentifier(), 'children' => []];
        foreach($this->getChildren() as $child) {
            $tree['children'][] = $child->identifierArray();
        }
        return $tree;
    }

}