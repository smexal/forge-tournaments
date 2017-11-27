<?php

use PHPUnit\Framework\TestCase;

use Forge\SuperLoader as SuperLoader;

use Forge\Core\App\App as App;
use Forge\Core\Classes\CollectionItem;

use Forge\Modules\ForgeTournaments;
use Forge\Modules\ForgeTournaments\PhaseBuilder as PhaseBuilder;
use TestUtilsForgeTournaments as TestUtilsForgeTournaments;

class TestPhasebuilder extends TestCase {


    public function testBuildEncounters() {
        $phase_builder = PhaseBuilder::instance();
        $item = $this->makePhase();
        $phase = ForgeTournaments\PoolRegistry::instance()->getPool('phase')->getInstance($item->getID(), $item);
        die(var_dump($phase));
    }


    public function makePhase() {
        $phase_collection = App::instance()->cm->getCollection(ForgeTournaments\PhaseCollection::COLLECTION_NAME);
        $args = [
            'name' => \i('Teset Phase'),
            'type' => ForgeTournaments\PhaseCollection::COLLECTION_NAME
        ];
        $metas = [];
        $fields = $phase_collection->fields();
        foreach($fields as $field) {
            if(isset($field['data_source_save'])) {
                continue;
            }
            if(!isset($field['value'])) {
                continue;
            }
            $metas[$field['key']] = [
                'value' => $field['value'],
                'lang' => 0
            ];
        }
        $metas['ft_data_schema']['value'] = 'phase_result_group';
        $metas['ft_phase_type']['value'] = ForgeTournaments\PhaseTypes::GROUP;

        $item = new CollectionItem(CollectionItem::create($args, $metas));
        return $item;

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