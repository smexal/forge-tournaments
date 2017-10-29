<?php

namespace Forge\Modules\ForgeTournaments\Calculations\Nodes\Iterators;

use Forge\Modules\ForgeTournaments\Interfaces\INodeIterator;

/**
 *                          A
 *                B                   C
 *           D         E         F         G
 *         H   I     J   K     L   M     N   O
 * 
 *  Results in: A, B, D, H, I, E, J, K, C, F, L, M, G, N, O
 */
class DepthFirstIterator extends NodeIterator {

    public function _nextNode() : INode {
        if (!$this->hasStarted()) {
            return $this->base_node;
        }
        return $this->getDFNode($this->getCurrentNode());
    }

    private function getDFNode(INode $node) {
        foreach ($this->getChildren() as $child) {
            if (!$this->isNodeVisited($child) && !$this->isNodeClosed($child)) {
                return $child;
            }
        }
        
        $this->setNodeClosed($node);
        if (!$node->hasParent()) {
            $this->finishIteration();
            return null;
        }
        return $this->getDFNode($node->getParent());
    }

}