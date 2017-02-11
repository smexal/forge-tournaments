<?php

namespace Forge\Modules\ForgeTournaments;

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
                        'name' => 'FNATIC',
                        'id' => null,
                        'classes' => ''
                    ],
                    'team_2' => [
                        'name' => 'mYinsanitY',
                        'id' => null,
                        'classes' => ''
                    ],
                    'result' => ''
                ];
            }
        }
    }

    /**
     * Renders the bracket with all set settings.
     * @return html output
     */
    public function render() {
        var_dump($this->encounters);
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