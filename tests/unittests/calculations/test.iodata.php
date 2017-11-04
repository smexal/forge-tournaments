<?php

use PHPUnit\Framework\TestCase;

use \Forge\Core\App\App;
use \Forge\SuperLoader as SuperLoader;

use \TestUtilsForgeTournaments as TestUtilsForgeTournaments;

use Forge\Modules\ForgeTournaments\Calculations\Node;
use Forge\Modules\ForgeTournaments\Calculations\CalcUtils;
use Forge\Modules\ForgeTournaments\Calculations\Inputs\CollectionInput;
use Forge\Modules\ForgeTournaments\Calculations\Inputs\Input;
use Forge\Modules\ForgeTournaments\Calculations\Inputs\StaticInput;
use Forge\Modules\ForgeTournaments\Calculations\Inputs\DataSegment;
use Forge\Modules\ForgeTournaments\Calculations\Inputs\DataSet;


class TestIOData extends TestCase {


    public function testStaticInput() {
         $expected = [
            'team_a' => ['time' => '33:30', 'fouls' => 10, 'men' => 5, 'women' => 2],
            'team_b' => ['time' => '44:40', 'fouls' => 2, 'men' => 4, 'women' => 3]
        ];

        $alpha_data = new DataSet([
            new DataSegment('team_a', ['time' => '33:30', 'fouls' => 10]),
            new DataSegment('team_b', ['time' => '44:40', 'fouls' => 2])
        ]);
        $beta_data = new DataSet([
            new DataSegment('team_a', ['men' => 5, 'women' => 2]),
            new DataSegment('team_b', ['men' => 4, 'women' => 3])
        ]);

        $dummy_node = new Node();
        $alpha = new StaticInput('alpha', $alpha_data);
        $beta = new StaticInput('beta', $beta_data);
        $current = new DataSet();
        $current = $alpha->appendData($current, $dummy_node);
        $current = $beta->appendData($current, $dummy_node);


        foreach(['team_a', 'team_b'] as $segment_id) {
            $ds = $current->getDataSegment($segment_id);
            
            $ds_data = $ds->getData();
            $expected_s = $expected[$segment_id];

            $expected_s = ksort($expected_s);
            $ds_data  = ksort($ds_data);
            
            $this->assertEquals($ds_data, $expected_s);
        }

        $this->assertNull($current->getDataSegment('Inexistent Identifier'));
    }

    public function testFormula() {
        $formula = 'a*x^2 + b*x + c';
        $v = [
            'a' => 2,
            'b' => 3,
            'c' => 4,
            'x' => 2.5
        ];
        $expected = $v['a'] * pow($v['x'], 2) + $v['b'] * $v['x'] + $v['c'];
        $calculated = CalcUtils::applyFormula($formula, $v, 4);
        $this->assertEquals(round($expected, 4), $calculated);
    }

    public function testLongVarsFormula() {
        $formula = 'alpha*exxx^2 + beta*exxx + gamma * matches.total_points';
        $v = [
            'alpha' => -1,
            'beta' => 10,
            'gamma' => -4,
            'exxx' => 1.234,
            'matches.total_points' => 22
        ];
        $expected = $v['alpha'] * pow($v['exxx'], 2) + $v['beta'] * $v['exxx'] + $v['gamma'] * $v['matches.total_points'];
        $calculated = CalcUtils::applyFormula($formula, $v, 4);
        $this->assertEquals(round($expected, 4), $calculated);
    }

    public function testCalculationInput() {

        return;

        $alpha_data = new DataSet([
            new DataSegment('team_a', ['time' => '33:30', 'fouls' => 10]),
            new DataSegment('team_b', ['time' => '44:40', 'fouls' => 2])
        ]);
        $beta_data = new DataSet([
            new DataSegment('team_a', ['men' => 5, 'women' => 2]),
            new DataSegment('team_b', ['men' => 4, 'women' => 3])
        ]);

        $dummy_node = new Node();
        $alpha = new StaticInput('alpha', $alpha_data);
        $beta = new StaticInput('beta', $beta_data);
        $current = new DataSet();
        $current = $alpha->appendData($current, $dummy_node);
        $current = $beta->appendData($current, $dummy_node);




        // Formulas
        // Saving to DataSet
        // Segment wise calculations?
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