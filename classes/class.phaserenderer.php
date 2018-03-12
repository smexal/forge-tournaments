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
    private $phaseTypeItem;

    public function __construct($tournament, $phaseId) {
        $this->tournament = $tournament;

        $phase = new CollectionItem($phaseId);
        $this->phase = new Phase($phase);
        $this->phaseTypeItem = Utils::getSubtype('IPhaseType', $this->phase, 'ft_phase_type');
        $this->phaseTypeItem->setPhase($this->phase);
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
                'values' => $this->phaseTypeItem->getGroupStandingValues($group)
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
            $has_result = false;
            $result_a = 0;
            $result_b = 0;

            $storage = DatasetStorage::getInstance('encounter_result', $encounter->getID());
            $dataset = $storage->loadAll();
            $systemSet = $dataset->getDataSegment('system');
            if(! is_null($systemSet)) {
                $has_result = true;
                $result_a = $systemSet->getValue('points_a', 'system');
                $result_b = $systemSet->getValue('points_b', 'system');
            }
            $adminSet = $dataset->getDataSegment('admin');
            if(! is_null($adminSet)) {
                $has_result = true;
                $result_a = $adminSet->getValue('points_a', 'admin');
                $result_b = $adminSet->getValue('points_b', 'admin');
            }

            $schedule_entries[] = [
                'index' => $index,
                'participant_left_title' => $slots[0]->getName(),
                'participant_left_image' => $this->getAvatarImage($slots[0]),
                'participant_right_title' => $slots[1]->getName(),
                'participant_right_image' => $this->getAvatarImage($slots[1]),
                'is_own' => $isOwnMatch,
                'is_admin' => $this->isAdmin(),
                'set_result_link' => $setResultLink,
                'set_result_href' => $setResultHref,
                'set_result_refresh_href' => CoreUtils::getUrl(CoreUtils::getUriComponents()),
                'set_result_refresh_target' => '#tournament-detail',
                'has_result' => $has_result,
                'result_a' => $result_a,
                'result_b' => $result_b
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
                'wins_tooltip' => i('Wins', 'forge-tournaments'),
                'losses' => i('L', 'forge-tournaments'),
                'losses_tooltip' => i('Losses', 'forge-tournaments'),
                'draws' => i('D', 'forge-tournaments'),
                'draws_tooltip' => i('Draws', 'forge-tournaments'),
                'points' => i('Points', 'forge-tournaments'),
                'vs' => i('vs', 'forge-tournaments'),
                'standings' => $standings,
                'schedule_title' => i('Schedule & Results', 'forge-tournaments'),
                'schedule_entries' => $schedule_entries,
                'set_result_label' => i('Set Result', 'forge-tournaments'),
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

        // add results set by team for the admin.
        if($this->isAdmin()) {
            $storage = DatasetStorage::getInstance('encounter_result', $encounterId);
            $dataset = $storage->loadAll();
            if(! is_null($dataset->getDataSegment('team_a'))) {
                $content[] = '
                    <p>'.sprintf(i('Result by %1$s', 'forge-tournaments'), $encounter_slots[0]->getName()).' > '
                    .$dataset->getDataSegment('team_a')->getValue('points_a', 'team_a').' : '
                    .$dataset->getDataSegment('team_a')->getValue('points_b', 'team_a').'</p>
                ';
            }
            if(! is_null($dataset->getDataSegment('team_b'))) {
                $content[] = '
                    <p>'.sprintf(i('Result by %1$s', 'forge-tournaments'), $encounter_slots[1]->getName()).' > '
                    .$dataset->getDataSegment('team_b')->getValue('points_a', 'team_b').' : '
                    .$dataset->getDataSegment('team_b')->getValue('points_b', 'team_b').'</p>
                ';
            }
            if(! is_null($dataset->getDataSegment('system'))) {
                $content[] = '
                    <p>'.sprintf(i('Result by system', 'forge-tournaments'), $encounter_slots[1]->getName()).' > '
                    .$dataset->getDataSegment('system')->getValue('points_a', 'system').' : '
                    .$dataset->getDataSegment('system')->getValue('points_b', 'system').'</p>
                ';
            }
            if(! is_null($dataset->getDataSegment('admin'))) {
                $content[] = '
                    <p>'.sprintf(i('Result by Admin', 'forge-tournaments'), $encounter_slots[1]->getName()).' > '
                    .$dataset->getDataSegment('admin')->getValue('points_a', 'admin').' : '
                    .$dataset->getDataSegment('admin')->getValue('points_b', 'admin').'</p>
                ';
            }
        }

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

        if(! $this->isOwnMatch($encounter_slots[0], $encounter_slots[1]) && ! $this->isAdmin() ) {
            return 'nope dope';
        }
        $a_or_b = $this->getAB($encounter_slots[0], $encounter_slots[1]);
        if($encounter->getItem()->getMeta('result_set_'.$a_or_b) == 'yes' && ! $this->isAdmin() ) {
            return 'already set';
        }
        if(! $this->isAdmin() ) {
            $encounter->getItem()->updateMeta('result_set_'.$a_or_b, 'yes', false);
        }
        
        $storage = DatasetStorage::getInstance('encounter_result', $encounterId);

        $dataSource = $a_or_b == 'admin' ? 'admin' : 'team_'.$a_or_b;
        $segment = new DataSegment($dataSource);
        // Data recorded by team A for team A
        $segment->addData([
          'points_a' => $data['result_team_1'],
          'points_b' => $data['result_team_2']
        ], $dataSource);

        $set = new DataSet();
        $set->addDataSegment($segment);
        $storage->save($set);

        // check if other team has set result and is the same
        // then set the system result automatically
        if($this->isAdmin()) {
            return '<h2>'.i('Gratz, admin.', 'forge-tournaments').'</h2><p>'.i('You\'ve set a result. Other user and system inputs will be ignored. Yours counts. Feel mighty. When you close this input, the page will be refreshed.', 'forge-tournaments').'</p>';
        }

        $dataset = $storage->loadAll();
        $otherTeam = $a_or_b == 'a' ? 'b' : 'a';
        if(! is_null($dataset->getDataSegment('team_'.$otherTeam))) {
            $valueForA = $dataset->getDataSegment('team_'.$otherTeam)->getValue('points_a', 'team_'.$otherTeam);
            $valueForB = $dataset->getDataSegment('team_'.$otherTeam)->getValue('points_b', 'team_'.$otherTeam);

            if($valueForA == $data['result_team_1'] && $valueForB == $data['result_team_2']) {
                // matches / save system result
                $segment = new DataSegment('system');
                $segment->addData([
                  'points_a' => $data['result_team_1'],
                  'points_b' => $data['result_team_2']
                ], 'system');

                $set = new DataSet();
                $set->addDataSegment($segment);
                $storage->save($set);
            }
        }
        return '<h2>'.i('Thank you.', 'forge-tournaments').'</h2><p>'.i('Your result has been inserted. If it matches with the other teams input, it will be set automatically. If not, feel free to contact the tournament administrator.', 'forge-tournaments').'</p>';
    }

    private function getAvatarImage($participant) {
        if(is_null($participant)) {
            return;
        }
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
                $team = ParticipantCollection::getTeam($part);
                $members = TeamsCollection::getMembers($team);
                if(in_array(App::instance()->user->get('id'), $members)) {
                    return 'a';
                } else {
                    return 'b';
                }
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
                $team = ParticipantCollection::getTeam($part);
                $members = TeamsCollection::getMembersAsUsers($team);
                if(in_array(App::instance()->user->get('id'), $members)) {
                    return true;
                }
            }
        }
    }

    public function isAdmin() {
        if(! Auth::any()) {
            return false;
        }
        if(!is_array( $this->tournament->getMeta('responsibles') )) {
            return false;
        }
        if(in_array(App::instance()->user->get('id'), $this->tournament->getMeta('responsibles'))) {
            return true;
        }
    }

}

?>