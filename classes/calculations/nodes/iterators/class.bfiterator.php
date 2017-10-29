<?php

namespace Forge\Modules\ForgeTournaments\Calculations\Nodes\Iterators;

use Forge\Modules\ForgeTournaments\Interfaces\INodeIterator;

/**
 *                          A
 *                B                   C
 *           D         E         F         G
 *         H   I     J   K     L   M     N   O
 * 
 *  Results in: A, B, C, D, E, F, G, H, I, J, K, L, M, N, O
 */
class BreadthFirstIterator implements INodeIterator {

    private $queue = [];

    public function _nextNode() : INode {
        if (!$this->hasStarted()) {
            $this->queue[] = $this->base_node;
            return reset($this->queue);
        }
        return $this->getBFNode();
    }

    private function getBFNode() {
        $queue_node = reset($this->queue);
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