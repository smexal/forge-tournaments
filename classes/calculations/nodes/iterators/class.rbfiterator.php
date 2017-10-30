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
 *  Results in: H, I, J, K, L, M, N, O, D, E, F, G, B, C, A
 */
class ReverseBreadthFirstIterator extends NodeIterator {

    private $levels = [];
    private $current_level = -1;

    protected function _nextNode() {
        if (!$this->hasStarted()) {
            $this->levels = $this->generateLevels();
            $this->current_level = count($this->levels) - 1;
        }
        $node = $this->getRBFNode();
        return $node;
    }

    /**
     * For big trees this initial step might be very expensive
     * But as we have at max. 6 Levels with an estimated total
     * of 1200 Nodes this should not be a problem...
     * at least not for the initial usage of this plugin
     **/
    private function generateLevels() {
        $levels = [];
        $root = $this->getBaseNode();
        $this->_generateLevels($root, $levels);
        return $levels;
    }

    private function _generateLevels($node, &$levels, $depth=0) {
        if(!isset($levels[$depth])) {
            $levels[$depth] = [];
        }
        $levels[$depth][] = $node;
        foreach($node->getChildren() as $child) {
            $this->_generateLevels($child, $levels,  $depth + 1);
        }
    }

    private function getRBFNode() {
        if($this->current_level == 0 && count($this->levels[$this->current_level]) == 0) {
            return null;
        }
        if(count($this->levels[$this->current_level]) == 0) {
            $this->current_level--;
        }
        $element = array_shift($this->levels[$this->current_level]);
        return $element;
    }


}