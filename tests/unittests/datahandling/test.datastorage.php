<?php

use PHPUnit\Framework\TestCase;

use \Forge\Core\App\App;
use \Forge\SuperLoader as SuperLoader;

use \TestUtilsForgeTournaments as TestUtilsForgeTournaments;

use Forge\Modules\ForgeTournaments\Data\DataSetStorage;
use Forge\Modules\ForgeTournaments\Data\DataSegment;
use Forge\Modules\ForgeTournaments\Data\DataSet;


class TestDataStorage extends TestCase {


    public function testDataSets() {
        return ;
        $storage = new DataSetStorage('test', 11, new MockStorageHandler());
        //$storage->setStorageHandler()
        $ds1 = new DataSegment('team_a');
        $ds2 = new DataSegment('team_a');
        $ds3 = new DataSegment('team_a');
        $ds4 = new DataSegment('team_b');
        $ds5 = new DataSegment('team_b');
        $ds6 = new DataSegment('team_b');

        // Data recorded of team A for team A
        $ds1->addData([
          'throws' => 40,
          'runs'   => 20
        ], 'team_a');
        // Data recorded of team A for team B
        $ds2->addData([
          'throws' => 30,
          'runs'   => 10
        ], 'team_b');
        // Data recorded of admin (manually corrected) for team B
        $ds3->addData([
          'throws' => 35,
          'runs'   => 11
        ], 'admin');
        // Data recorded of team B for team A
        $ds4->addData([
          'throws' => 40,
          'runs'   => 20
        ], 'team_b');
        // Data recorded of team B for team B
        $ds5->addData([
          'throws' => 40,
          'runs'   => 20
        ], 'team_a');

        $team_alpha = new DataSet();
        $team_beta = new DataSet();

        $team_alpha->addDataSegment($ds1);
        $team_alpha->addDataSegment($ds2);
        $team_beta->addDataSegment($ds3);
        $team_beta->addDataSegment($ds4);

        $storage->save($team_alpha);
        $storage->save($team_beta);

        $expectation = [
            'team_a' => [
                // The admin corrected values
                'throws' => 35,
                'runs' => 11
            ],
            'team_b' => [
                'throws' => 40,
                'runs' => 20
            ]
        ];

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