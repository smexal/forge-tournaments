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
    private $doubleElimination = false;

    public function __construct($tournament, $phaseId) {
        $this->tournament = $tournament;

        $phase = new CollectionItem($phaseId);
        $this->phase = new Phase($phase);
        $this->phaseCollection = $phase;
        $this->phaseTypeItem = Utils::getSubtype('IPhaseType', $this->phase, 'ft_phase_type');
        $this->phaseTypeItem->setPhase($this->phase);
        $this->doubleElimination = $this->phase->getMeta('single_double') == 'double' ? true : false;
    }

    public function render() {
        if($this->phase->getState() < PhaseState::READY) {
            return 'not ready';
        }

        if($this->phase->getPhaseType() == 'group') {
            return $this->renderGroupPhase();
        }

        if($this->phase->getPhaseType() == 'ko') {
            return $this->renderKoPhase();
        }

        if($this->phase->getPhaseType() == 'performance') {
            return $this->renderPerformancePhase();
        }

    }

    private function renderPerformancePhase() {
        $headerImage = new Media($this->tournament->getMeta('image_background'));

        $standings = [];
        $encounters = [];

        foreach($this->phase->getGroups() as $group) {
            $encounters = $group->getEncounters();
            $standings[] = [
                'values' => $this->getPerformanceStandings($group)
            ];
        }

        return App::instance()->render(
            MOD_ROOT.'forge-tournaments/templates/parts', 'performance-phase',
            [
                'title' => $this->phase->getMeta('title'),
                'header_image' => $headerImage->getSizedImage(2100, 600),
                'tournament_title' => $this->tournament->getMeta('title'),
                'standings_title' => i('Standings', 'forge-tournaments'),
                'points' => i('Points', 'forge-tournaments'),
                'standings' => $standings,
                'set_result_label' => i('Set Result', 'forge-tournaments'),
                'round_title' => i('Round ', 'forge-tournaments'),
                'rounds' => range(1, count($group->getEncounters())),
                'is_admin' => $this->isAdmin(),
                'is_running' => $this->phase->getState() == PhaseState::RUNNING ? true : false,
                'set_result_links' => $this->getPerformanceSetResultLinks($group->getEncounters())
            ]
        );
    }

    private function getPerformanceSetResultLinks($encounters) {
        $resultLinks = [];

        if(! $this->isAdmin()) {
            return [];
        }

        foreach($encounters as $encounter) {
            $setResultHref = CoreUtils::getUrl(
                    array_merge(CoreUtils::getUriComponents(), ['set-result', $encounter->getID()])
            );
            $resultLinks[] = [
                'label' => i('Set results', 'forge-tournaments'),
                'href' => $setResultHref
            ];
        }

        return $resultLinks;
    }

    private function getPerformanceStandings($group) {
        $position = 1;
        $values = [];
        $id = 0;
        foreach($group->getStandings() as $standingEntry) {
            if(is_null($standingEntry)) {
                continue;
            }
            $image = $this->getAvatarImage($standingEntry);
            $points = 0;
            $results = $this->getPerformanceEncounterScores($id, $group->getEncounters());
            foreach($results as $result) {
                $points+=$result;
            }
            $values[] = [
                'position' => $position,
                'logo' => $image,
                'name' => $standingEntry->getName(),
                'points' => $points,
                'participantID' => $standingEntry->getID(),
                'results' => $results,
            ];
            $position++;
            $id++;
        }

        usort($values, function ($item1, $item2) {
            return $item2['points'] <=> $item1['points'];
        });

        $rank = 1;
        foreach($values as $key => $standing_entry) {
            $values[$key]['position'] = $rank;
            $rank++;
        }

        return $values;
    }

    private function getPerformanceEncounterScores($participant, $encounters) {
        $results = [];
        foreach($encounters as $encounter) {
            $storage = DatasetStorage::getInstance('encounter_result', $encounter->getID());
            $dataset = $storage->loadAll();
            $adminSet = $dataset->getDataSegment('admin');
            if(! is_null($adminSet)) {
                $has_result = true;
                $results[] = $adminSet->getValue('points_'.$participant, 'admin');
            } else {
                $results[] = 0;
            }
        }
        return $results;
    }

    private function renderKoPhase() {
        $headerImage = new Media($this->tournament->getMeta('image_background'));

        $encounters = [];
        foreach($this->phase->getGroups() as $group) {
            $encounters = array_merge($encounters, $group->getEncounters());
        }

        $entries = $this->getScheduleEntries($encounters);
        $entries = $this->shardByRounds($entries);
        $entries = $this->defineEncounterNext($entries);
        $entries = $this->updateFreeSlots($entries);
        $entries = $this->updateBracket($entries);

        return App::instance()->render(
            MOD_ROOT.'forge-tournaments/templates/parts', 'ko-phase',
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
                'schedule_title' => i('Schedule & Results', 'forge-tournaments'),
                'schedule_entries' => $entries,
                'set_result_label' => i('Set Result', 'forge-tournaments'),
            ]
        );
    }

    private function updateFreeSlots($allEncounters) {

        if($this->phaseCollection->getMeta('freeSlotsUpdated') == '1') {
            return $allEncounters;
        }

        if($this->doubleElimination) {
            $round = 0;
        } else {
            $round = 1;
        }
        /**
         * WINNER BRACKET UPDATE (Round 0)
         */
        for($encounterIndex = 0; $encounterIndex < count($allEncounters[$round]['winner_bracket']); $encounterIndex++) {
            $encounter = $allEncounters[$round]['winner_bracket'][$encounterIndex];
            $hasOnlyOne = false;
            if($encounter['participant_left_title'] == 'tbd' || $encounter['participant_right_title'] == 'tbd') {
                $hasOnlyOne = true;
            }
            
            if($encounter['participant_left_title'] == 'tbd' && $encounter['participant_right_title'] == 'tbd') {
                $hasOnlyOne = false;
            }

            if($encounter['participant_left_title'] == 'tbd') {
                $pointsLeft = '0';
                $pointsRight = '1';
            }
            if($encounter['participant_right_title'] == 'tbd') {
                $pointsLeft = '1';
                $pointsRight = '0';
            }
            if($hasOnlyOne) {
                // set winner
                $storage = DatasetStorage::getInstance('encounter_result', $encounter['encounter_id']);
                $storage->deleteAll();

                $dataSource = 'system';
                $segment = new DataSegment($dataSource);
                // Data recorded by team A for team A
                $segment->addData([
                  'points_a' => $pointsLeft,
                  'points_b' => $pointsRight
                ], $dataSource);

                $set = new DataSet();
                $set->addDataSegment($segment);
                $storage->save($set);

                $this->moveWinnerAndLoser($encounter['encounter_id']);
            }
        }

        $this->phaseCollection->updateMeta('freeSlotsUpdated', '1', 0);

        /**
         * LOSER BRACKET UPDATE (Round 0)
         */

        return $allEncounters;
    }

    private function moveWinnerAndLoser($encounter) {
        if(is_numeric($encounter)) {
            $encounter = new CollectionItem($encounter);
        }
        $encounter = PoolRegistry::instance()->getPool('encounter')->getInstance($encounter->getID(), $encounter);
        $slots = $encounter->getSlotAssignment();
        $slots = $slots->getSlots();
        $results = $this->getEncounterResult($encounter);

        if($results) {
            $winnerEncounter = $encounter->getMeta('winnerGoesTo');
            $winnerEncounter = new CollectionItem($winnerEncounter);
            $winnerEncounter = PoolRegistry::instance()->getPool('encounter')->getInstance($winnerEncounter->getID(), $winnerEncounter);

            $loserEncounter = $encounter->getMeta('loserGoesTo');
            $loserEncounter = new CollectionItem($loserEncounter);
            $loserEncounter = PoolRegistry::instance()->getPool('encounter')->getInstance($loserEncounter->getID(), $loserEncounter);

            if($results[0] > $results[1]) {
                // A goes to Winner
                // B goes to Loser
                if(! $this->isOnEncounter($winnerEncounter, $slots[0])) {
                    $winnerEncounter->setNumSlots(2);
                    $winnerEncounter->addParticipant($slots[0]);
                }

                if($slots[1]) {
                    // check if already on encounter;
                    if(! $this->isOnEncounter($loserEncounter, $slots[1])) {
                        $loserEncounter->setNumSlots(2);
                        $loserEncounter->addParticipant($slots[1]);
                    }
                }
            } else if ($results[1] > $results[0]) {
                // B goes to Winner
                // A goes to Loser
                if(! $this->isOnEncounter($loserEncounter, $slots[0])) {
                    $loserEncounter->setNumSlots(2);
                    $loserEncounter->addParticipant($slots[0]);
                }

                if($slots[1]) {
                    if(! $this->isOnEncounter($winnerEncounter, $slots[1])) {
                        $winnerEncounter->setNumSlots(2);
                        $winnerEncounter->addParticipant($slots[1]);
                    }
                }
            }
        }

        //$encounter->setNumSlots(2);
        //$encounter->addParticipant($participants->getSlot($encounter_no));

    }

    private function isOnEncounter($encounter, $participant) {
        $slots = $encounter->getSlotAssignment();
        $slots = $slots->getSlots();
        foreach($slots as $slot) {
            if(is_object($slot)) {
                if($slot->getID() == $participant->getID()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * this is where the bracket magic happens... :o
     * @param  [type] $schedule_entries [description]
     * @return [type]                   [description]
     */
    private function shardByRounds($schedule_entries) {
        $round_encounters = [];
        $participants = $this->phase->getSlotAssignment();
        $num_participants = $participants->count();
        $amountOfEncounters = $this->getNextPowerOf2($num_participants) / 2;
        $rounds = ceil($amountOfEncounters / 2);

        if($this->doubleElimination) {
            $index_range = range(0, $rounds+1);
        } else {
            // Single Elimination has one round less, only one final game.
            $index_range = range(0, $rounds);
        }
        $encounter_index = 0;

        /**
         * Since the preround is now done, we can create the rest of the rounds
         */
        $first = true;
        $bracketAmount = $amountOfEncounters;

        /**
         * Winner Bracket
         */
        foreach($index_range as $index) {
            for($index_in_round = 0; $index_in_round < $bracketAmount; $index_in_round++) {
                $round_encounters[$index]['winner_bracket'][] = $schedule_entries[$encounter_index];
                $encounter_index++;
            }
            $bracketAmount = ceil($bracketAmount / 2);
        }

        /**
         * Loser Bracket
         */
        if($this->doubleElimination) {
            $bracketAmount = $amountOfEncounters / 2;
            $index_range = range(0, $rounds+1);
            foreach($index_range as $index) {
                for($index_in_round = 0; $index_in_round < $bracketAmount; $index_in_round++) {
                    $round_encounters[$index]['loser_bracket'][] = $schedule_entries[$encounter_index];
                    $encounter_index++;
                }
                // only every second time we divide by 2
                if($index % 2 != 0) {
                    $bracketAmount = floor($bracketAmount / 2);
                }
            }
        }

        return $round_encounters;
    }

    /**
     * this method defines for each encounter, where
     * the winner has to go and where the loser goes...
     * @param  [type] $encounters List of all encounters
     * @return [type]             [description]
     */
    public function defineEncounterNext($encounters) {

        $half = true;
        for($roundIndex = 0; $roundIndex < count($encounters); $roundIndex++) {
            $winnerBracket = false;
            if(array_key_exists($roundIndex, $encounters) && array_key_exists('winner_bracket', $encounters[$roundIndex])) {
                $winnerBracket = $encounters[$roundIndex]['winner_bracket'];
            }
            $loserBracket = false;
            if($this->doubleElimination) {
                $loserBracket = @$encounters[$roundIndex]['loser_bracket'];
            }

            if($winnerBracket) {
                for($winnerBracketIndex = 0; $winnerBracketIndex < count($winnerBracket); $winnerBracketIndex++) {
                    $encounterItem = new CollectionItem($winnerBracket[$winnerBracketIndex]['encounter_id']);
                    // winner goes to next round, if this exists (not final)
                    // 0 => 0
                    // 1 => 0
                    // 2 => 1 /2
                    // 3 => 1 -1 / 2
                    // 4 => 2 ( / 2 )
                    // 5 => 2 ( -1 / 2)
                    $newIndex = 0;
                    if($winnerBracketIndex >= 2) {
                        if($winnerBracketIndex % 2 != 0) {
                            $newIndex = ($winnerBracketIndex -1) / 2;
                        } else {
                            $newIndex = $winnerBracketIndex / 2;
                        }
                    }
                    $newWinnerEncounter = @$encounters[$roundIndex+1]['winner_bracket'][$newIndex]['encounter_id'];
                    if(! is_null($newWinnerEncounter)) {
                        $encounterItem->updateMeta('winnerGoesTo', $newWinnerEncounter, 0);
                    }
                    
                    // Loser Bracket Round Works like below...
                    // 0 => 0
                    // 1 => 1
                    // 2 => 3 => +1
                    // 3 => 5 => +2
                    // 4 => 7 => +3
                    // 5 => 9 => +4 (+(self-1))
                    if($roundIndex == 0) {
                        $newYIndex = $newIndex;
                    } else if($roundIndex == 1) {
                        $newYIndex = count($winnerBracket)-1-$winnerBracketIndex;
                    } else {
                        $newYIndex = $winnerBracketIndex;
                    }
                    $newRoundIndex = $roundIndex;
                    if($newRoundIndex > 1) {
                        $newRoundIndex = $newRoundIndex+($newRoundIndex-1);
                    }
                    $newLoserEncounter = @$encounters[$newRoundIndex]['loser_bracket'][$newYIndex]['encounter_id'];
                    if(! is_null($newLoserEncounter)) {
                        $encounterItem->updateMeta('loserGoesTo', $newLoserEncounter, 0);
                    } else {
                        $encounterItem->updateMeta('loserGoesTo', '0', 0);
                    }
                }
            }

            if($loserBracket) {
                $half = ! $half;
                for($loserBracketIndex = 0; $loserBracketIndex < count($loserBracket); $loserBracketIndex++) {
                    $encounterItem = new CollectionItem($loserBracket[$loserBracketIndex]['encounter_id']);
                    // ROUND => Always + 1
                    // INDEX => every second round Calculate (%2 != 0 + 1) / 2 same as in winner bracket
                    
                    // only do this calculation every second round...
                    $newIndex = 0;
                    if(! $half ) {
                        $newIndex = $loserBracketIndex;
                    } else {
                        if($loserBracketIndex >= 2) {
                            if($loserBracketIndex % 2 != 0) {
                                $newIndex = ($loserBracketIndex -1) / 2;
                            } else {
                                $newIndex = $loserBracketIndex / 2;
                            }
                        }
                    }
                    $newLoserEncounter = @$encounters[$roundIndex+1]['loser_bracket'][$newIndex]['encounter_id'];
                    if(! is_null($newLoserEncounter)) {
                        $encounterItem->updateMeta('winnerGoesTo', $newLoserEncounter, 0);

                        // loser goes out of tournament...
                        $encounterItem->updateMeta('loserGoesTo', 0, 0);
                    } else {
                        // last round goes to winner bracket last encounter... FINALLZ!1
                        // there has always to be an encounter for a loser bracket winner.... :o
                        $newLoserEncounter = @$encounters[count($encounters)-1]['winner_bracket'][0]['encounter_id'];
                        $encounterItem->updateMeta('winnerGoesTo', $newLoserEncounter, 0);
                    }
                }
            }

        }

        return $encounters;
    }

    public function getNextPowerOf2($number) {
        $exp = log($number, 2);
        $exp = ceil($exp);
        return pow(2, $exp);
    }

    private function renderGroupPhase() {
        $headerImage = new Media($this->tournament->getMeta('image_background'));

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

        $schedule_entries = $this->getScheduleEntries($encounters);

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

    private function getScheduleEntries($encounters) {
        $index = 1;
        $schedule_entries = [];
        foreach($encounters as $encounter) {
            $slots = $encounter->getSlotAssignment();
            $slots = $slots->getSlots();
            if(count($slots) == 0) {
                $isOwnMatch = false;
                $a_or_b = false;
            } else {
                $isOwnMatch = $this->isOwnMatch($slots[0], $slots[1]);
                $a_or_b = $this->getAB($slots[0], $slots[1]);
            }
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
            $winner = 'none';
            if($has_result) {
                if($result_a > $result_b) {
                $winner = 'a';
                } elseif($result_b > $result_a) {
                    $winner = 'b';
                }
            }

            $schedule_entries[] = [
                'index' => $index,
                'encounter_id' => $encounter->getID(),
                'participant_left_title' => count($slots) > 0 && ! is_null($slots[0]) ? $slots[0]->getName() : 'tbd',
                'participant_left_image' => count($slots) > 0 && ! is_null($slots[0]) ? $this->getAvatarImage($slots[0]) : '',
                'participant_right_title' => count($slots) > 0 && ! is_null($slots[1]) ? $slots[1]->getName() : 'tbd',
                'participant_right_image' => count($slots) > 0 && ! is_null($slots[1]) ? $this->getAvatarImage($slots[1]) : '',
                'winner' => $winner,
                'is_own' => $isOwnMatch,
                'is_admin' => $this->isAdmin(),
                'set_result_link' => $setResultLink,
                'set_result_href' => $setResultHref,
                'set_result_refresh_href' => CoreUtils::getUrl(CoreUtils::getUriComponents()),
                'set_result_refresh_target' => '#tournament-detail',
                'has_result' => $has_result,
                'result_a' => $result_a,
                'result_b' => $result_b,
                'winner_to' => $encounter->getMeta('winnerGoesTo'),
                'loser_to' => $encounter->getMeta('loserGoesTo')
            ];
            $index++;
        }
        return $schedule_entries;
    }

    public function getEncounterResult($encounter) {
        $storage = DatasetStorage::getInstance('encounter_result', $encounter->getID());
        $dataset = $storage->loadAll();
        $systemSet = $dataset->getDataSegment('system');
        $has_result = false;
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
        if(! $has_result)
            return;
        return [$result_a, $result_b];
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

        if($this->phase->getPhaseType() == 'performance') {
            if(! $this->isAdmin()) {
                return 'Go away, script kiddy.';
            }
        }

        $heading = '<h3>'.i('Set Result for this match.', 'forge-tournaments').'</h3>';

        $content = [];

        // add results set by team for the admin.
        if($this->isAdmin() && $this->phase->getPhaseType() != 'performance') {
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

            // admin gets a link to manage encounter if is has permission
            /*
                not yet implemented since the encounter participants cant be changed for now...
                if(Auth::allowed("manage.collection.forge-tournaments-encounter")) {
                    $content[] = '<a href="'.CoreUtils::url(['manage']).'" target="_blank">'.i('Configure this encounter manually.', 'forge-tournaments').'</a>';
                }
             */
            
        }
        if($this->phase->getPhaseType() != 'performance') {
            $content[] = Fields::text([
            'label' => sprintf(i('Points: %1$s', 'forge-tournaments'), $encounter_slots[0]->getName()),
            'key' => 'result_team_0',
            ]);

            $content[] = Fields::text([
                'label' => sprintf(i('Points: %1$s', 'forge-tournaments'), is_null($encounter_slots[1]) ? 'tbd' : $encounter_slots[1]->getName()),
                'key' => 'result_team_1',
            ]);
        } else {
            $storage = DatasetStorage::getInstance('encounter_result', $encounterId);
            $dataset = $storage->loadAll();
            $content = array_merge($content, $this->getPerformanceEncounterFields($encounter_slots, $dataset));
        }
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

    private function getPerformanceEncounterFields($encounter_slots, $dataset) {
        $content = [];
        $id = 0;
        foreach($encounter_slots as $encounter_slot) {
            $result = '0';
            if(! is_null($dataset->getDataSegment('admin'))) {
                $result = $dataset->getDataSegment('admin')->getValue('points_'.$id, 'admin');
            }
            if($encounter_slot) {
                $name = $encounter_slot->getName();
            } else {
                $name = '';
            }
            $content[] = Fields::text([
                'label' => sprintf(i('Points: %1$s', 'forge-tournaments'), $name),
                'key' => 'result_team_'.$id,
                'value' => $result
            ]);
            $id++;
        }
        return $content;
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

        /**
         * only admins can enter results on performance phase.. at least for now :o
         */
        if($this->phase->getPhaseType() == 'performance' && ! $this->isAdmin()) {
            return 'away you go...';
        }
        if($this->phase->getPhaseType() == 'performance') {
            // set performance results....
            $dataSource = 'admin';
            $storage = DatasetStorage::getInstance('encounter_result', $encounterId);
            $segment = new DataSegment($dataSource);
            $segmentData = [];
            $id = 0;
            foreach($encounter_slots as $encounter_slot) {
                $segmentData['points_'.$id] = $data['result_team_'.$id];
                $id++;
            }
            $segment->addData($segmentData, $dataSource);
            $set = new DataSet();
            $set->addDataSegment($segment);
            $storage->save($set);

            return '<h2>'.i('Gratz, admin.', 'forge-tournaments').'</h2><p>'.i('You\'ve set the results (points) for this round. When you close this input, the page will be refreshed.', 'forge-tournaments').'</p>';
        }
        
        $storage = DatasetStorage::getInstance('encounter_result', $encounterId);

        $dataSource = $a_or_b == 'admin' ? 'admin' : 'team_'.$a_or_b;
        $segment = new DataSegment($dataSource);
        // Data recorded by team A for team A
        $segment->addData([
          'points_a' => $data['result_team_0'],
          'points_b' => $data['result_team_1']
        ], $dataSource);

        $set = new DataSet();
        $set->addDataSegment($segment);
        $storage->save($set);

        // check if other team has set result and is the same
        // then set the system result automatically
        if($this->isAdmin()) {
            if($this->phase->getPhaseType() == 'ko') {
                $this->moveWinnerAndLoser($encounter);
            }
            return '<h2>'.i('Gratz, admin.', 'forge-tournaments').'</h2><p>'.i('You\'ve set a result. Other user and system inputs will be ignored. Yours counts. Feel mighty. When you close this input, the page will be refreshed.', 'forge-tournaments').'</p>';
        }

        $dataset = $storage->loadAll();
        $otherTeam = $a_or_b == 'a' ? 'b' : 'a';
        if(! is_null($dataset->getDataSegment('team_'.$otherTeam))) {
            $valueForA = $dataset->getDataSegment('team_'.$otherTeam)->getValue('points_a', 'team_'.$otherTeam);
            $valueForB = $dataset->getDataSegment('team_'.$otherTeam)->getValue('points_b', 'team_'.$otherTeam);

            if($valueForA == $data['result_team_0'] && $valueForB == $data['result_team_1']) {
                // matches / save system result
                $segment = new DataSegment('system');
                $segment->addData([
                  'points_a' => $data['result_team_0'],
                  'points_b' => $data['result_team_1']
                ], 'system');

                $set = new DataSet();
                $set->addDataSegment($segment);
                $storage->save($set);
            }
        }

        if($this->phase->getPhaseType() == 'ko') {
            $this->moveWinnerAndLoser($encounter);
        }

        return '<h2>'.i('Thank you.', 'forge-tournaments').'</h2><p>'.i('Your result has been inserted. If it matches with the other teams input, it will be set automatically. If not, feel free to contact the tournament administrator.', 'forge-tournaments').'</p>';
    }

    private function updateBracket($encounters) {
        for($roundIndex = 0; $roundIndex < count($encounters); $roundIndex++) {
            $winnerBracket = false;
            if(array_key_exists($roundIndex, $encounters) && array_key_exists('winner_bracket', $encounters[$roundIndex])) {
                $winnerBracket = $encounters[$roundIndex]['winner_bracket'];
            }
            $loserBracket = false;
            if($this->doubleElimination) {
                $loserBracket = @$encounters[$roundIndex]['loser_bracket'];
            }

            if($winnerBracket) {
                for($winnerBracketIndex = 0; $winnerBracketIndex < count($winnerBracket); $winnerBracketIndex++) {
                    $this->moveWinnerAndLoser($encounters[$roundIndex]['winner_bracket'][$winnerBracketIndex]['encounter_id']);
                }
            }
        }
        return $encounters;
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
            if(is_null($part)) {
                continue;
            }
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