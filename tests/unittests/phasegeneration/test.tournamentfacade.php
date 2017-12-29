<?php

use PHPUnit\Framework\TestCase;

use Forge\SuperLoader as SuperLoader;

use Forge\Core\App\App as App;
use Forge\Core\Classes\CollectionItem;

use Forge\Modules\ForgeTournaments;
use Forge\Modules\ForgeTournaments\PhaseBuilder as PhaseBuilder;
use TestUtilsForgeTournaments as TestUtilsForgeTournaments;

class TestTournamentFacade extends TestCase {

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

    }

    public static function tearDownAfterClass() {
        UtilsTests::teardown();
    }


}