<?php

use PHPUnit\Framework\TestCase;

use \Forge\Core\App\App;
use \Forge\SuperLoader as SuperLoader;

use \TestUtilsForgeTournaments as TestUtilsForgeTournaments;

use Forge\Modules\ForgeTournaments\Data\DatasetStorage;
use Forge\Modules\ForgeTournaments\Data\DataSegment;
use Forge\Modules\ForgeTournaments\Data\DataSet;


class TestDataStorage extends TestCase {


    public function testDataSets() {
        $sh = new MockStorageHandler();
        $storage = DatasetStorage::getInstance('test', 11, $sh);
        //$storage->setStorageHandler()
        $ds1_a = new DataSegment('team_a');
        $ds2_a = new DataSegment('team_a');
        $ds3_a = new DataSegment('team_a');
        $ds4_b = new DataSegment('team_b');
        $ds5_b = new DataSegment('team_b');
        $ds6_b = new DataSegment('team_b');

        // Data recorded by team A for team A
        $ds1_a->addData([
          'throws' => 40,
          'runs'   => 20
        ], 'team_a');
        // Data recorded by team B for team A
        $ds2_a->addData([
          'throws' => 30,
          'runs'   => 10
        ], 'team_b');
        // Data recorded by admin (manually corrected) for team A
        $ds3_a->addData([
          'throws' => 35,
          'runs'   => 11
        ], 'admin');
        // Data recorded by team B for team B
        $ds4_b->addData([
          'throws' => 40,
          'runs'   => 20
        ], 'team_b');
        // Data recorded by team B for team A
        $ds5_b->addData([
          'throws' => 40,
          'runs'   => 20
        ], 'team_a');
        // Data recorded by team B for team B
        $ds6_b->addData([
          'throws' => 33,
          'runs'   => 18
        ], 'team_b');

        $team_alpha = new DataSet();
        $team_beta = new DataSet();

        $team_alpha->addDataSegment($ds1_a);
        $team_alpha->addDataSegment($ds2_a);
        $team_beta->addDataSegment($ds3_a);
        $team_beta->addDataSegment($ds4_b);
        $team_beta->addDataSegment($ds5_b);
        $team_beta->addDataSegment($ds6_b);

        $storage->save($team_alpha);
        $storage->save($team_beta);

        $sh_data = $sh->data['ft_datastorage'];
        $this->assertCount(10, $sh_data);

        $dataset = $storage->loadAll();
        
        // Data for team A
        $this->assertEquals(40, $dataset->getDataSegment('team_a')->getValue('throws', 'team_a'));
        $this->assertEquals(30, $dataset->getDataSegment('team_a')->getValue('throws', 'team_b'));
        $this->assertEquals(35, $dataset->getDataSegment('team_a')->getValue('throws', 'admin'));

        // Data for team B
        $this->assertEquals(33, $dataset->getDataSegment('team_b')->getValue('throws', 'team_b'));
        $this->assertEquals(20, $dataset->getDataSegment('team_b')->getValue('runs', 'team_a'));

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

    public static function tearDownAfterClass() {
        UtilsTests::teardown();
    }


}