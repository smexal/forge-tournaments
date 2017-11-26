<?php

namespace Forge\Modules\ForgeTournaments\Calculations\Nodes;

class CollectionNode extends CalcNode {
    protected $item;

    public function __construct($item) {
        $this->item = $item;
        parent::__construct($this->item->getID());
    }

    public function getItem() {
        return $this->item;
    }

}