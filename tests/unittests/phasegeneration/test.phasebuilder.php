<?php

use PHPUnit\Framework\TestCase;

use \Forge\Core\App\App as App;
use \Forge\SuperLoader as SuperLoader;

use \TestUtilsForgeTournaments as TestUtilsForgeTournaments;

class TestPhasebuilder extends TestCase {


    public function testBuildEncounters() {
        $phase_builder = PhaseBuilder::instance();
    }


    public static function setUpBeforeClass() {
        $cwd = getcwd();
        $root_dir = realpath($cwd . '/../../../../');
        $forge_tests_dir = realpath($root_dir .'/unittests');
        
        chdir($forge_tests_dir);
        require_once($forge_tests_dir . '/class.utils.php');
        
        UtilsTests::prepare();
        chdir($cwd);
    }

    public static function tearDownAfterClass() {
        UtilsTests::teardown();
    }


}