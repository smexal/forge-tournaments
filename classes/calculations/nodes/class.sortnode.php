<?php



// CHECK IF THIS IS EVEN NECESSARY




namespace Forge\Modules\ForgeTournaments\Calculations\Nodes;

class SortNode extends CalcNode {
    private $sorting = null;

    public function __construct($identifier, ISorting $sorting) {
        $this->identifier = $identifier;
        $this->sorting = $sorting;
    }

    public function sortData() {
        $this->recalculate();
        $this->data = $this->sorting->sort($this->data);
    }

    public function getSortedIdentifiers() {
        $this->sortData();
        return array_keys($this->data);
    }

}