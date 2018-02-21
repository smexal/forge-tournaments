<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Core\App\App;
use Forge\Core\Classes\CollectionItem;
use Forge\Core\Classes\Media;

class PhaseRenderer {
    private $tournament;
    private $phase;

    public function __construct($tournament, $phaseId) {
        $this->tournament = $tournament;

        $phase = new CollectionItem($phaseId);
        $this->phase = new Phase($phase);
    }

    public function render() {

        if($this->phase->getState() < PhaseState::READY) {
            return 'not ready';
        }

        if($this->phase->getPhaseType() == 'group') {
            return $this->renderGroupPhase($this->phase);
        }

    }

    private function renderGroupPhase() {
        $headerImage = new Media($this->tournament->getMeta('image_background'));

        $groupCollection = App::instance()->cm->getCollection('forge-tournaments-group');

        $groupId = 'A';
        $groupNo = '1';
        $standings = [];
        foreach($this->phase->getGroups() as $group) {
            $standingData = $group->getStandings();
            $standings[] = [
                'no' => $groupNo,
                'title' => i(sprintf('Group %1$s', $groupId), 'forge-tournaments'),
            ];
            $groupNo++;
            $groupId++;
        }

        return App::instance()->render(
            MOD_ROOT.'forge-tournaments/templates/parts', 'group-phase',
            [
                'title' => $this->phase->getMeta('title'),
                'header_image' => $headerImage->getSizedImage(2100, 600),
                'tournament_title' => $this->tournament->getMeta('title'),
                'standings' => $standings
            ]
        );
    }

}

?>