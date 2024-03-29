<?php

use PHPUnit\Framework\TestCase;

use Forge\SuperLoader as SuperLoader;

use Forge\Core\App\App as App;
use Forge\Core\Classes\CollectionItem;

use Forge\Modules\ForgeTournaments;
use Forge\Modules\ForgeTournaments\PhaseBuilder as PhaseBuilder;
use TestUtilsForgeTournaments as TestUtilsForgeTournaments;

class TestPhasebuilder extends TestCase {

    public function testEntityPool() {
        UtilsTests::doPurgeDB();

        $entity_pool = new ForgeTournaments\EntityPool('\\Forge\\Core\\Classes\\CollectionItem', 4);

        $ids = [];
        for($i = 0; $i < 4; $i++) {
            $item = new CollectionItem(CollectionItem::create([
                'name' => 'Int: ' . $i,
                'type' => 'forge-tournaments-prize'
            ]));
            $ids[] = $item->getID();
            $entity_pool->setInstance($item->getID(), $item);
        }

        $this->assertTrue($entity_pool->hasInstance($ids[0]));
        
        foreach($ids as $id) {
            $this->assertTrue($entity_pool->hasInstance($id));
        }

        $item =  new CollectionItem(CollectionItem::create([
                'name' => 'Special',
                'type' => 'forge-tournaments-prize'
            ]));
        $entity_pool->setInstance($item->getID(), $item);

        $this->assertFalse($entity_pool->hasInstance($ids[0]));
        $this->assertTrue($entity_pool->hasInstance($item->getID()));

    }

    public function testBuildEncounters() {
        UtilsTests::doPurgeDB();
        $item = TestUtilsForgeTournaments::makePhase();
        $phase = ForgeTournaments\PoolRegistry::instance()->getPool('phase')->getInstance($item->getID(), $item);
        $this->assertEquals($phase,  ForgeTournaments\PoolRegistry::instance()->getPool('phase')->getInstance($item->getID()));
        $phase->setNumSlots(32);
        for($i = 0; $i < 30; $i++) {
            $participant = TestUtilsForgeTournaments::makeParticipant($i);
            $phase->addParticipant($participant);
        }

        PhaseBuilder::instance()->build($phase);

        $phase = ForgeTournaments\PoolRegistry::instance()->getPool('phase')->getInstance($item->getID());

        $groups = $phase->getGroups();
        $this->assertCount(8, $groups);

        foreach($groups as $idx => $group) {
            // var_dump("--- GROUP {$idx} START ---");
            $this->assertEquals(4, $group->getGroupSize());
            $encounters = $group->getEncounters();
            //error_log(print_r("enc_count: " . count($encounters), 1));
            if($idx < 6) {
                $this->assertEquals(6, count($encounters));
            } else {
                $this->assertEquals(3, count($encounters));
            }
            /*foreach($group->getEncounters() as $encounter) {
                var_dump(implode(',', $encounter->getSlotAssignment()->getSlotData()));
            }
            var_dump("--- GROUP {$idx} END ---");*/
        }

        $participants = $phase->getSlotAssignment()->getSlots();
        $g1_encounters = $groups[0]->getEncounters();
        $this->assertEquals([$participants[0], $participants[1]], $g1_encounters[0]->getSlotAssignment()->getSlots());
        $this->assertEquals([$participants[0], $participants[2]], $g1_encounters[1]->getSlotAssignment()->getSlots());
        $this->assertEquals([$participants[2], $participants[3]], end($g1_encounters)->getSlotAssignment()->getSlots());

        $g7_encounters = $groups[6]->getEncounters();
        $this->assertEquals([$participants[24], $participants[25]], $g7_encounters[0]->getSlotAssignment()->getSlots());
        $this->assertEquals([$participants[24], $participants[26]], $g7_encounters[1]->getSlotAssignment()->getSlots());
        $this->assertEquals([$participants[25], $participants[26]], end($g7_encounters)->getSlotAssignment()->getSlots());

        $g8_encounters = $groups[7]->getEncounters();
        $this->assertEquals([$participants[27], $participants[28]], $g8_encounters[0]->getSlotAssignment()->getSlots());
        $this->assertEquals([$participants[27], $participants[29]], $g8_encounters[1]->getSlotAssignment()->getSlots());
        $this->assertEquals([$participants[28], $participants[29]], end($g8_encounters)->getSlotAssignment()->getSlots());


        
        PhaseBuilder::instance()->clean($phase);
        $ctree = new \Forge\Modules\ForgeTournaments\Calculations\CollectionTree($phase->getItem());
        $root = $ctree->build();
      
        $this->assertCount(0, $root->getChildren());
    }

    public static function tearDownAfterClass() {
        UtilsTests::teardown();
    }


}