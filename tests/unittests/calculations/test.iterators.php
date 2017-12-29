<?php

use PHPUnit\Framework\TestCase;

use \Forge\Core\App\App;
use \Forge\SuperLoader as SuperLoader;

use \TestUtilsForgeTournaments as TestUtilsForgeTournaments;

use Forge\Modules\ForgeTournaments\Calculations\Nodes\Node;
use Forge\Modules\ForgeTournaments\Calculations\Nodes\Iterators;
use Forge\Modules\ForgeTournaments\Calculations\Nodes\Iterators\BreadthFirstIterator;
use Forge\Modules\ForgeTournaments\Calculations\Nodes\Iterators\ReverseBreadthFirstIterator;
use Forge\Modules\ForgeTournaments\Calculations\Nodes\Iterators\DepthFirstIterator;


class TestIterators extends TestCase {


    public function testBFIterator() {
        $node = new Node();
        $name_stack = TestUtilsForgeTournaments::nameStack();
        $node = TestUtilsForgeTournaments::appendBinaryTree($node, 3, 0, $name_stack);
        $iterator = new BreadthFirstIterator($node);
        $list = [];
        while(!is_null($n = $iterator->nextNode())) {
            $list[] = $n->getIdentifier();
        }
        $this->assertEquals(['A','B','C','D','E','J','K','F','G','H','I','L','M','N', 'O'], $list);
    }

    public function testDFIterator() {
        $node = new Node();
        $name_stack = TestUtilsForgeTournaments::nameStack();
        $node = TestUtilsForgeTournaments::appendBinaryTree($node, 3, 0, $name_stack);
        $iterator = new DepthFirstIterator($node);
        $list = [];
        while(!is_null($n = $iterator->nextNode())) {
            $list[] = $n->getIdentifier();
        }
        $this->assertEquals(['A', 'B', 'D', 'F', 'G', 'E', 'H', 'I', 'C', 'J', 'L', 'M', 'K', 'N', 'O'], $list);
    }

    public function testRBFIterator() {
        $node = new Node();
        $name_stack = TestUtilsForgeTournaments::nameStack();
        $node = TestUtilsForgeTournaments::appendBinaryTree($node, 3, 0, $name_stack);
        $iterator = new ReverseBreadthFirstIterator($node);
        $list = [];
        while(!is_null($n = $iterator->nextNode())) {
            $list[] = $n->getIdentifier();
        }
        $this->assertEquals(['F', 'G', 'H', 'I', 'L', 'M', 'N', 'O', 'D', 'E', 'J', 'K', 'B', 'C', 'A'], $list);
    }

    public static function tearDownAfterClass() {
        UtilsTests::teardown();
    }


}