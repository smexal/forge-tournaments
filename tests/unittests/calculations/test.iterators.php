<?php

use PHPUnit\Framework\TestCase;

use \Forge\Core\App\App;
use \Forge\SuperLoader as SuperLoader;

use \TestUtilsForgeTournaments as TestUtilsForgeTournaments;

use Forge\Modules\ForgeTournaments\Calculations\Node;
namespace Forge\Modules\ForgeTournaments\Calculations\Nodes\Iterators;


class TestIterators extends TestCase {


    public function testBFIterator() {
        $node = new Node();
        die(var_dump(TestUtilsForgeTournaments::nameStack()));
        $node = TestUtilsForgeTournaments::appendBinaryTree($node, 3, TestUtilsForgeTournaments::nameStack());


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