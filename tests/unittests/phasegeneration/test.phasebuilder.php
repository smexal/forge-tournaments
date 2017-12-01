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
        $item = $this->makePhase();
        $phase = ForgeTournaments\PoolRegistry::instance()->getPool('phase')->getInstance($item->getID(), $item);
        
        for($i = 0; $i<30; $i++) {
            $phase->addParticipant($this->makeParticipant($i));
        }
        die(var_dump($phase->getParticipantList()));

        PhaseBuilder::instance()->build($phase);
    }

    public function makePhase($name='') {
        $set_metas = [
            'ft_data_schema' => 'phase_result_group',
            'ft_phase_type' => ForgeTournaments\PhaseTypes::GROUP,
            'ft_group_size' => 4,
            'ft_participant_list_size' => 32

        ];
        $_GLOBALS['makae-test'] = true;
        $item =  $this->makeCollectionItem(ForgeTournaments\PhaseCollection::COLLECTION_NAME, 'Test Phase' . $name, $set_metas);

        return $item;
    }

    public function makeParticipant($name='') {
        return $this->makeCollectionItem(ForgeTournaments\ParticipantCollection::COLLECTION_NAME, 'Test Participant' . $name);
    }

    public function makeCollectionItem($c_name, $name, $set_metas=[]) {
        $participant = App::instance()->cm->getCollection($c_name);
        $args = [
            'name' => $name,
            'type' => $c_name
        ];

        $metas = [];
        $fields = $participant->fields();
        foreach($fields as $field) {
            if(isset($field['data_source_save'])) {
                continue;
            }
            if(!isset($field['value'])) {
                continue;
            }
            $metas[$field['key']] = [
                'value' => $field['value']
            ];
        }

        foreach($set_metas as $key => $value) {
            $metas[$key] = [
                'value' => $value
            ];
        }
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