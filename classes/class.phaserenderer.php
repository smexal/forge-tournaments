<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Core\App\App;
use Forge\Core\App\Auth;
use Forge\Core\Classes\CollectionItem;
use Forge\Core\Classes\Fields;
use Forge\Core\Classes\Media;
use Forge\Core\Classes\User;
use Forge\Core\Classes\Utils as CoreUtils;
use Forge\Modules\ForgeTournaments\Data\DataSegment;
use Forge\Modules\ForgeTournaments\Data\DataSet;
use Forge\Modules\ForgeTournaments\Data\DatasetStorage;
use Forge\Modules\ForgeTournaments\Encounter;
use Forge\Modules\ForgeTournaments\ParticipantCollection;
use Forge\Modules\TournamentsTeams\TeamsCollection;

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
        $encounters = [];

        foreach($this->phase->getGroups() as $group) {
            $encounters = array_merge($encounters, $group->getEncounters());

            $standings[] = [
                'no' => $groupNo,
                'title' => i(sprintf('Group %1$s', $groupId), 'forge-tournaments'),
                'values' => $this->getStandingValues($group)
            ];
            $groupNo++;
            $groupId++;
        }

        $schedule_entries = [];
        $index = 1;
        foreach($encounters as $encounter) {
            $slots = $encounter->getSlotAssignment();
            $slots = $slots->getSlots();
            $isOwnMatch = $this->isOwnMatch($slots[0], $slots[1]);
            $a_or_b = $this->getAB($slots[0], $slots[1]);
            $setResultLink = false;
            $setResultHref = '';
            if($isOwnMatch && $this->phase->getState() == PhaseState::RUNNING
                && ! $encounter->getItem()->getMeta('result_set_'.$a_or_b) == 'yes') {
                $setResultLink = true;
                $setResultHref = CoreUtils::getUrl(
                    array_merge(CoreUtils::getUriComponents(), ['set-result', $encounter->getID()])
                );
            }
            $schedule_entries[] = [
                'index' => $index,
                'participant_left_title' => $slots[0]->getName(),
                'participant_left_image' => $this->getAvatarImage($slots[0]),
                'participant_right_title' => $slots[1]->getName(),
                'participant_right_image' => $this->getAvatarImage($slots[1]),
                'is_own' => $isOwnMatch,
                'set_result_link' => $setResultLink,
                'set_result_href' => $setResultHref,
                'set_result_refresh_href' => CoreUtils::getUrl(CoreUtils::getUriComponents()),
                'set_result_refresh_target' => '#tournament-detail'
            ];
            $index++;
        }

        return App::instance()->render(
            MOD_ROOT.'forge-tournaments/templates/parts', 'group-phase',
            [
                'title' => $this->phase->getMeta('title'),
                'header_image' => $headerImage->getSizedImage(2100, 600),
                'tournament_title' => $this->tournament->getMeta('title'),
                'standings_title' => i('Standings', 'forge-tournaments'),
                'games' => i('Games', 'forge-tournaments'),
                'wins' => i('W', 'forge-tournaments'),
                'losses' => i('L', 'forge-tournaments'),
                'points' => i('Points', 'forge-tournaments'),
                'vs' => i('vs', 'forge-tournaments'),
                'standings' => $standings,
                'schedule_title' => i('Schedule & Results', 'forge-tournaments'),
                'schedule_entries' => $schedule_entries,
                'set_result_label' => i('Set Result', 'forge-tournaments')
            ]
        );
    }

    public function setResultView($encounter) {
        $encounterId = $encounter;
        $encounter = PoolRegistry::instance()->getPool('encounter')->getInstance($encounter);
        $encounter_slots = $encounter->getSlotAssignment();
        $encounter_slots = $encounter_slots->getSlots();

        if(! $this->isOwnMatch($encounter_slots[0], $encounter_slots[1]) ) {
            return 'nope dope';
        }
        $a_or_b = $this->getAB($encounter_slots[0], $encounter_slots[1]);
        if($encounter->getItem()->getMeta('result_set_'.$a_or_b) == 'yes') {
            return 'already set';
        }

        $heading = '<h3>'.i('Set Result for this match.', 'forge-tournaments').'</h3>';
        $content = [];
        $content[] = Fields::text([
            'label' => sprintf(i('Points: %1$s', 'forge-tournaments'), $encounter_slots[0]->getName()),
            'key' => 'result_team_1',
        ]);
        $content[] = Fields::text([
            'label' => sprintf(i('Points: %1$s', 'forge-tournaments'), $encounter_slots[1]->getName()),
            'key' => 'result_team_2',
        ]);
        $content[] = Fields::hidden([
            'name' => 'encounter', 
            'value' => $encounterId
        ]);
        $content[] = Fields::button(i('Save result', 'forge-tournaments'));

        return '<div class="wrapper">'.$heading.App::instance()->render(CORE_TEMPLATE_DIR.'assets/', 'form', [
            'action' => CoreUtils::getCurrentUrl(),
            'method' => 'post',
            'ajax' => true,
            'ajax_target' => '#slidein-overlay .content',
            'horizontal' => false,
            'content' => $content
        ]).'</div>';
    }

    public function insertResult($data) {
        $encounterId = $data['encounter'];
        $encounter = PoolRegistry::instance()->getPool('encounter')->getInstance($encounterId);
        $encounter_slots = $encounter->getSlotAssignment();
        $encounter_slots = $encounter_slots->getSlots();

        if(! $this->isOwnMatch($encounter_slots[0], $encounter_slots[1]) ) {
            return 'nope dope';
        }
        $a_or_b = $this->getAB($encounter_slots[0], $encounter_slots[1]);
        if($encounter->getItem()->getMeta('result_set_'.$a_or_b) == 'yes') {
            return 'already set';
        }
        $encounter->getItem()->updateMeta('result_set_'.$a_or_b, 'yes', false);
        
        $storage = DatasetStorage::getInstance('encounter_result', $encounterId);

        $segment = new DataSegment('team_'.$a_or_b);
        // Data recorded by team A for team A
        $segment->addData([
          'points_a' => $data['result_team_1'],
          'points_b' => $data['result_team_2']
        ], 'team_'.$a_or_b);

        $set = new DataSet();
        $set->addDataSegment($segment);
        $storage->save($set);
    }

    private function getStandingValues($group) {
        $position = 1;
        $values = [];
        foreach($group->getStandings() as $standingEntry) {
            $image = $this->getAvatarImage($standingEntry);
            $values[] = [
                'position' => $position,
                'logo' => $image,
                'name' => $standingEntry->getName(),
                'games' => 0,
                'wins' => 0,
                'losses' => 0,
                'points' => 0
            ];
            $position++;
        }
        return $values;
    }

    private function getAvatarImage($participant) {
        if(is_numeric($participant->getMeta('user'))) {
            $user = new User($participant->getMeta('user'));
            $image = $user->getAvatar();
        } else {
            $team = ParticipantCollection::getTeam($participant->getID());
            $orga = TeamsCollection::getOrganization($team);
            $orga = new CollectionItem($orga);
            $image = new Media($orga->getMeta('logo'));
            $image = $image->getSizedImage(60, 60);
        }
        return $image;
    }

    private function getAB($participant_a, $participant_b) {
        if(! Auth::any() ) {
            return;
        }
        if($this->isAdmin()) {
            return 'admin';
        }
        $participants = [$participant_a, $participant_b];
        $team = 'a';
        foreach($participants as $part) {
            if(is_numeric($part->getMeta('user'))) {
                if( $part->getMeta('user') == App::instance()->user->get('id') ) {
                    return 'a';
                } else {
                    return 'b';
                }
            } else {
                var_dump('!!!Check if user is in Team!!!');
            }
        }
    }

    private function isOwnMatch($participant_1, $participant_2) {
        if(! Auth::any() ) {
            return;
        }
        if($this->isAdmin()) {
            return true;
        }
        $participants = [$participant_1, $participant_2];
        foreach($participants as $part) {
            if(is_numeric($part->getMeta('user'))) {
                if( $part->getMeta('user') == App::instance()->user->get('id') ) {
                    return true;
                }
            } else {
                var_dump('!!!Check if user is in Team!!!');
            }
        }
    }

    public function isAdmin() {
        if(in_array(App::instance()->user->get('id'), $this->tournament->getMeta('responsibles'))) {
            return true;
        }
    }

}

?>