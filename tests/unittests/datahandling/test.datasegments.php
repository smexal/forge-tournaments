<?php

use PHPUnit\Framework\TestCase;

use \Forge\Core\App\App;
use \Forge\SuperLoader as SuperLoader;

use \TestUtilsForgeTournaments as TestUtilsForgeTournaments;

use Forge\Modules\ForgeTournaments\Data\DataSegment;
use Forge\Modules\ForgeTournaments\Data\DataSet;


class TestDataSegments extends TestCase {


    public function testDataSets() {
        $alpha = new DataSet();
        $beta = new DataSet();

        $expected = [
            'team_a' => [
                'throws' => 40,
                'runs'   => 22,
                'fouls' => 2,
                'time'   => '33:30'
            ],
            'team_b' => [
                'throws' => 30,
                'runs'   => 10,
                'fouls' => 3,
                'time'   => '30:40'
            ]
        ];

        $alpha->addDataSegment(new DataSegment('team_a', [
          'throws' => 40,
          'runs'   => 20
        ]));
        $alpha->addDataSegment(new DataSegment('team_a', [
          'fouls' => 2,
          'time'   => '33:30',
          'runs'   => 22
        ]));


        $beta->addDataSegment(new DataSegment('team_b', [
          'throws' => 30,
          'runs'   => 10
        ]));


        $beta->addDataSegment(new DataSegment('team_b', [
          'fouls' => 3,
          'time'   => '30:40'
        ]));


        $alpha->merge($beta);

        $this->assertEquals(array_keys($alpha->getAllDataSegments()), ['team_a', 'team_b']);

        foreach(['team_a', 'team_b'] as $segment_id) {
            $ds = $alpha->getDataSegment($segment_id);
            
            $ds_data = $ds->getData();
            $expected_s = $expected[$segment_id];

            $expected_s = ksort($expected_s);
            $ds_data  = ksort($ds_data);
            
            $this->assertEquals($ds_data, $expected_s);
        }

        $this->assertNull($alpha->getDataSegment('Inexistent Identifier'));

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