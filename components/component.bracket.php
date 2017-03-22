<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\Component;
use \Forge\Core\App\App;

class BracketComponent extends Component {
    public $settings = [];
    private $prefix = 'forge_tournament_bracket_';

    public function prefs() {
        $this->settings = [
            [
                'label' => i('Choose a tournament', 'forge-tournaments'),
                'hint' => i('All tournaments of this event will be displayed') ,
                'key' => $this->prefix.'tournament_list',
                'type' => 'select',
                'callable' => true,
                'values' => [$this, 'getTournamentListOptionValues']
            ]
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

    public function getTournamentListOptionValues() {
        $collection = App::instance()->cm->getCollection('forge-tournaments');
        $items = $collection->items([
            'order' => 'created',
            'order_direction' => 'desc',
            'status' => 'published'
        ]);
        $list = [];
        foreach ($items as $item) {
            $list[$item->id] = $item->getName();
        }

        return ['0' => i('Choose one', 'forge-tournaments')] + $list;
    }

    public function content() {
        $tournamentId = $this->getField($this->prefix.'tournament_list');
        $collection = App::instance()->cm->getCollection('forge-tournaments');
        $tournament = $collection->getItem($tournamentId);

        $bracket = new Bracket($tournament->getMeta('max_participants')/2);

        $db = App::instance()->db;
        // $db->where('tournament_id', $tournament->id);
        // $teams = $db->get('forge_tournaments_tournament_participant');

        $roundsMax = log($tournament->getMeta('max_participants'), 2);

        for ($round = 0; $round < $roundsMax; $round++) {
            $db->where('tournament_id', $tournament->id);
            $db->where('round', $round);
            $roundEncounters = $db->get('forge_tournaments_tournament_encounter');
            foreach ($roundEncounters as $roundEncounter) {
                $db->where('id', $roundEncounter['participant_id']);
                $participant = $db->getOne('forge_tournaments_tournament_participant');
                $name = '[' . $participant['key'] . '] ' . $participant['name'];

                $bracket->setTeam(
                    $round,
                    $roundEncounter['encounter'],
                    [
                        'name' => $name,
                        'score' => '',
                        'classes' => ''
                    ]
                );
            }
        }

        return App::instance()->render(
            DOC_ROOT.'modules/forge-tournaments/templates/components',
            'bracket',
            [
                'encounterRounds' => $bracket->getEncounters()
            ]
        );
    }
}


?>
