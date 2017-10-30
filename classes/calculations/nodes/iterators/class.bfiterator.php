<?php

namespace Forge\Modules\ForgeTournaments\Calculations\Nodes\Iterators;

use Forge\Modules\ForgeTournaments\Interfaces\INode;
use Forge\Modules\ForgeTournaments\Interfaces\INodeIterator;

/**
 *                          A
 *                B                   C
 *           D         E         F         G
 *         H   I     J   K     L   M     N   O
 * 
 *  Results in: A, B, C, D, E, F, G, H, I, J, K, L, M, N, O
 */
class BreadthFirstIterator extends NodeIterator {

    private $queue = [];

    protected function _nextNode() {
        if (!$this->hasStarted()) {
            $this->queue[] = $this->getBaseNode();
            return reset($this->queue);
        }
        $node = $this->getBFNode();
        return $node;
    }

    private function getBFNode() {
        if(!($queue_node = reset($this->queue))) {
            return null;
        }
        foreach ($queue_node->getChildren() as $child) {
            if (!$this->isNodeVisited($child) && !$this->isNodeClosed($child)) {
                $this->queue[] = $child;
                return $child;
            }
        }
        
        // Remove the first element, as it is not used anymore
        $old_element = array_shift($this->queue);
        return $this->getBFNode();
    }


}