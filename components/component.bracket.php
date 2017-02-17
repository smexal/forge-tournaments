<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\Component;
use \Forge\Core\App\App;

class BracketComponent extends Component {
    public $settings = [];
    private $prefix = 'forge_tournament_bracket_';

    public function prefs() {
        $this->settings = [
        ];
        return [
            'name' => i('Tournament Bracket'),
            'description' => i('Add Bracket - Testing / Abstract', 'forge-tournaments'),
            'id' => 'forge_tournament_bracket',
            'image' => '',
            'level' => 'inner',
            'container' => false
        ];
    }

    public function content() {
        $bracket = new Bracket(16);

        $bracket->setTeam(0,0,1,[
            'name' => 'FNATIC',
            'score' => '2',
            'classes' => 'winner my-team'
        ]);

        $bracket->setTeam(0,0,2,[
            'name' => 'G2 Esports',
            'score' => '1',
            'classes' => 'loser'
        ]);

        $bracket->setTeam(0,1,1,[
            'name' => 'Fly Quest',
            'score' => '2',
            'classes' => 'winner'
        ]);

        $bracket->setTeam(0,1,2,[
            'name' => 'Origen',
            'score' => '0',
            'classes' => 'loser'
        ]);

        $bracket->setTeam(1,0,1,[
            'name' => 'FNATIC',
            'score' => '2',
            'classes' => 'winner my-team'
        ]);

        $bracket->setTeam(1,0,2,[
            'name' => 'Fly Quest',
            'score' => '0',
            'classes' => 'loser'
        ]);

        $bracket->setTeam(2,0,1,[
            'name' => 'FNACTIC',
            'score' => '0',
            'classes' => 'my-team'
        ]);

        return $bracket->render();
    }
}


?>