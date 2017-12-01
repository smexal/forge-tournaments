<?php

namespace Forge\Modules\ForgeTournaments\Calculations\Nodes\Iterators;

use Forge\Modules\ForgeTournaments\Interfaces\INode;
use Forge\Modules\ForgeTournaments\Interfaces\INodeIterator;

abstract class NodeIterator implements INodeIterator {
    private $started = false;
    private $finished = false;
    private $base_node = null;
    private $current_node = null;
    private $nodes_closed = [];

    public function __construct($node=null) {
        $this->base_node = $node;
    }

    public function nextNode() {
        $node = $this->_nextNode();
        $this->visitNode($node);
        $this->started = true;
        return $this->getCurrentNode();
    }

    // Returning null will automatically stop the iteration
    abstract protected function _nextNode();

    public function hasStarted() : bool {
        return $this->started;
    }

    public function getCurrentNode() {
        return $this->current_node;
    }

    public function getBaseNode() {
        return $this->base_node;
    }

    public function hasFinished() : bool {
        return $this->finished;
    }

    protected function finishIteration() {
        $this->finished = true;
    }


    protected function setCurrentNode($node) {
        $this->current_node = $node;
    }

    protected function visitNode($node) {
        $this->setCurrentNode($node);
        $this->setNodeVisited($node);
        if(is_null($node)) {
            $this->finishIteration();
        }
    }

    protected function setNodeClosed($node) {
        $this->nodes_closed[$this->getNodeUID($node)] = $node;
    }

    protected function isNodeClosed($node) {
        if (is_null($node)) {
            return null;
        }
        return array_key_exists($this->getNodeUID($node), $this->nodes_closed);
    }

    protected function setNodeVisited($node) {
        if (is_null($node)) {
            return;
        }
        $this->nodes_visited[$this->getNodeUID($node)] = $node;
    }

    protected function isNodeVisited($node) {
        if (is_null($node)) {
            return;
        }
        return array_key_exists($this->getNodeUID($node), $this->nodes_visited);
    }

    private function getNodeUID($node) {
        return spl_object_hash($node);
    }

}