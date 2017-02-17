<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Classes\Logger;
use \Forge\Core\App\App;

/**
 * This class is capable of rendering
 * a bracket for tournaments for the forge-tournaments plugin.
 */
class Bracket {
    private $size           = 0;
    private $rounds         = 1;
    private $encounters     = [];

    /**
     * Prepares the bracket for further configuration.
     * Requires the size of the of the bracket.
     * The size is measured in initial encounters and not participants.
     * @param int size
     */
    public function __construct($size) {
        if(!is_numeric($size)) {
            Logger::debug("Bracket Size '$size' is not numeric.");
            // assume a size, for further progress.
            $size = 4;
        }

        $this->size = $size;
        while($size > 0) {
            $size = intval($size/2);
            $this->rounds++;
        }

        $this->prepareEncounters();
    }

    /**
     * Prepares the encounters and sets all empty ready for filling.
     * @return null
     */
    private function prepareEncounters() {
        for ($round=1; $round < $this->rounds; $round++) { 
            $this->encounters[$round-1] = [
                'round' => $round,
                'encounters' => []
            ];
            $encountersForRound = $this->size;
            for ($rIndex=1; $rIndex < $round; $rIndex++) { 
                $encountersForRound = intval($encountersForRound / 2);
            }
            for ($encounterAmount=0; $encounterAmount < $encountersForRound; $encounterAmount++) { 
                $this->encounters[$round-1]['encounters'][] = [
                    'team_1' => [
                        'name' => '',
                        'id' => null,
                        'score' => '',
                        'classes' => ''
                    ],
                    'team_2' => [
                        'name' => '',
                        'id' => null,
                        'score' => '',
                        'classes' => ''
                    ],
                    'result' => ''
                ];
            }
        }
    }

    /**
     * Sets a team in a bracket for an encounter in a round.
     * @param int $round         Round in number (0 for the first round.).
     * @param int $encounter     Number of encounter in the round.
     * @param int $encounterTeam Position on the encounter (1 or 2)
     * @param array $team        Array with team information, name, id, the score, classes like "winner" or "my-team"
     */
    public function setTeam($round, $encounter, $encounterTeam, $team) {
        // check the delivered team if it has the required values.
        if(! array_key_exists('name', $team)) {
            Logger::debug('No Team "name" defined for bracket encounter.');
            return '';
        }
        if(! array_key_exists('score', $team)) {
            Logger::debug('No Team "score" defined for bracket encounter, default 0 assumed.');
            $team['score'] = 0;
        }
        if(! array_key_exists('classes', $team)) {
            $team['classes'] = '';
        }
        if(! array_key_exists('id', $team)) {
            Logger::debug('No Team "id" defined for bracket encounter, default null assumed.');
            $team['id'] = null;
        }

        $teamNo = 'team_2';
        if($encounterTeam == 1) {
            $teamNo = 'team_1';
        }
        $this->encounters[$round]['encounters'][$encounter][$teamNo] = $team;
    }

    /**
     * Renders the bracket with all set settings.
     * @return html output
     */
    public function render() {
        return App::instance()->render(
            DOC_ROOT."modules/forge-tournaments/templates/",
            "bracket",
            [
                'encounterRounds' => $this->encounters
            ]
        );
    }

}

?>