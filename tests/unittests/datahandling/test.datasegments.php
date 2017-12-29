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

        $ds1 = new DataSegment('team_a');
        $ds1->addData([
          'throws' => 40,
          'runs'   => 20
        ]);

        $ds2 = new DataSegment('team_a');
        $ds2->addData([
          'fouls' => 2,
          'time'   => '33:30',
          'runs'   => 22
        ]);

        $ds3 = new DataSegment('team_b');
        $ds3->addData([
          'throws' => 30,
          'runs'   => 10
        ]);

        $ds4 = new DataSegment('team_b');
        $ds4->addData([
          'fouls' => 3,
          'time'   => '30:40'
        ]);

        $alpha->addDataSegment($ds1);
        $alpha->addDataSegment($ds2);
        $beta->addDataSegment($ds3);
        $beta->addDataSegment($ds4);


        $alpha->merge($beta);

        $this->assertEquals(array_keys($alpha->getAllDataSegments()), ['team_a', 'team_b']);

        foreach(['team_a', 'team_b'] as $segment_id) {
            $ds = $alpha->getDataSegment($segment_id);
            
            $ds_data = $ds->getAllData();
            $expected_s = $expected[$segment_id];
            $expected_s = ksort($expected_s);
            $ds_data  = ksort($ds_data);
            
            $this->assertEquals($ds_data, $expected_s);
        }

        $this->assertNull($alpha->getDataSegment('Inexistent Identifier'));

    }

    public static function tearDownAfterClass() {
        UtilsTests::teardown();
    }


}