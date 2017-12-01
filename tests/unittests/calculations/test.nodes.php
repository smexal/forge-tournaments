<?php

use PHPUnit\Framework\TestCase;

use \Forge\Core\App\App;
use \Forge\SuperLoader as SuperLoader;

use \TestUtilsForgeTournaments as TestUtilsForgeTournaments;


use Forge\Modules\ForgeTournaments\Calculations\Formula;
use Forge\Modules\ForgeTournaments\Calculations\CalculationInput;
use Forge\Modules\ForgeTournaments\Calculations\CollectionInput;
use Forge\Modules\ForgeTournaments\Calculations\DataSet;
use Forge\Modules\ForgeTournaments\Calculations\ Input;
use Forge\Modules\ForgeTournaments\Calculations\StaticInput;
use Forge\Modules\ForgeTournaments\Calculations\TeamData;
use Forge\Modules\ForgeTournaments\Calculations\CalcNode;
use Forge\Modules\ForgeTournaments\Calculations\Node;
use Forge\Modules\ForgeTournaments\Calculations\Sorting;
use Forge\Modules\ForgeTournaments\Calculations\SortNode;
use Forge\Modules\ForgeTournaments\Calculations\CalcUtils;
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

    public static function setUpBeforeClass() {
        // TEST CONFIG
        require_once("utils.php");
        require_once('mocks/class.app.php');
        require_once('mocks/class.auth.php');
        require_once('mocks/class.collection.php');
        require_once('mocks/class.cmsinterface.php');
        require_once('mocks/class.collection.php');

        TestUtilsForgeTournaments::setup();
        \Forge\SuperLoader::instance()->addIgnore('Spyc');
    }

    public static function tearDownAfterClass() {
    }

}