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

    public function nextNode() : INode {
        $node = $this->_nextNode();
        $this->visitNode($node);
        $this->started = true;
    }

    public function hasStarted() : bool {
        return $this->started;
    }

    public function finishIteration() {
        $this->finished = true;
    }

    public function hasFinished() : bool {
        return $this->finished;
    }

    abstract protected function _nextNode() : INode;

    public function getBaseNode() : INode {
        return $this->base_node;
    }

    protected function getCurrentNode() : INode {
        return $this->current_node;
    }

    protected function setCurrentNode(INode $node) : INode {
        $this->current_node = $node;
    }

    private function visitNode(INode $node) {
        $this->setCurrentNode($node);
        $this->setNodeVisited($node);
    }

    private function setNodeClosed(INode $node) {
        $this->nodes_closed[$this->getNodeUID($node)] = $node;
    }

    private function isNodeClosed(INode $node) {
        return array_key_exists($this->getNodeUID($node), $this->nodes_closed);
    }

    private function setNodeVisited(INode $node) {
        $this->nodes_visited[$this->getNodeUID($node)] = $node;
    }

    private function isNodeVisited(INode $node) {
        return array_key_exists($this->getNodeUID($node), $this->nodes_visited);
    }

    private function getNodeUID(INode $node) {
        return spl_object_hash($node);
    }

}