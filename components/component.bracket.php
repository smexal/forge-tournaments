<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\Component;
use \Forge\Core\App\App;

class ForgeTournamentBracket extends Component {
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
        return $bracket->render();
    }
}


?>