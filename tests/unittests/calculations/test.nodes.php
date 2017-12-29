<?php

use PHPUnit\Framework\TestCase;

use Forge\Core\App\App;
use Forge\SuperLoader as SuperLoader;

use Forge\Modules\ForgeTournaments\Calculations\Formula;
use Forge\Modules\ForgeTournaments\Calculations\CalculationInput;
use Forge\Modules\ForgeTournaments\Calculations\CollectionInput;
use Forge\Modules\ForgeTournaments\Calculations\DataSet;
use Forge\Modules\ForgeTournaments\Calculations\Input;
use Forge\Modules\ForgeTournaments\Calculations\StaticInput;
use Forge\Modules\ForgeTournaments\Calculations\TeamData;
use Forge\Modules\ForgeTournaments\Calculations\CalcNode;
use Forge\Modules\ForgeTournaments\Calculations\Nodes\Node;
use Forge\Modules\ForgeTournaments\Calculations\Nodes\Sorting;
use Forge\Modules\ForgeTournaments\Calculations\Nodes\SortNode;
use Forge\Modules\ForgeTournaments\Calculations\Nodes\CalcUtils;
use Forge\Modules\ForgeTournaments\Calculations;

class TestNodes extends TestCase {
  
    public function testChildNodeAssignement() {
        $node = new Node();
        $nodes = []; 
        $nodes[] = new Node();
        $nodes[] = new Node();
        $nodes[] = new Node();

        foreach($nodes as $child) {
            $node->addChild($child);
        }

        $fetched_children = $node->getChildren();
        $this->assertEquals(count($fetched_children), count($nodes));
        foreach($fetched_children as $idx => $node) {
            $this->assertEquals($node, $nodes[$idx]);
        }

    }

    public static function tearDownAfterClass() {
        UtilsTests::teardown();
    }

}