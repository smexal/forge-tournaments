<?php

namespace Forge\Modules\ForgeTournaments\Fields;

use Forge\Core\App\App;
use Forge\Core\App\Auth;
use Forge\Core\Classes\Utils as CoreUtils;

use Forge\Modules\ForgeTournaments\Facade\Tournament as TournamentFacade;
use Forge\Modules\ForgeTournaments\Output as Output;

class PhaseList {

    public static function load($item, $field, $lang) {
        $value = [
            'phases' => [],
            'item_id' => $item->getID()
        ]
        ;
        $tournament = TournamentFacade::getTournament($item->getID());
        $phases = $tournament->getPhases();
        foreach($phases as $phase) {
            $phase_data = [
                'title' => $phase->getName(),
                'content' => Output::renderTournamentEntity(Output::RENDER_ADMIN_SMALL, $phase),
                'open' => true
            ];

            if(Auth::allowed("manage.collection.sites")) {
                $phase_data['edit_text'] = \i('Edit', 'forge-tournaments');
                $phase_data['edit_url'] = CoreUtils::getUrl(array('manage', 'collections', 'forge-tournaments-phase', 'edit', $phase->getID()));
            }
            $value['phases'][] = $phase_data;
        }
        return $value;
    }

    public static function save($item, $field, $value, $lang) {
        return;
    }

    public static function render($args, $value) {
        return App::instance()->render(MOD_ROOT.'forge-tournaments/templates/views/',
            'tournament_phases',
            [
                'title' => \i('Phases', 'forge-tournaments'),
                'phases' => $value['phases'],
                'add_phase' => App::instance()->render(CORE_TEMPLATE_DIR . "assets/", "overlay-button", array(
                    'url' => CoreUtils::getUrl(array('manage', 'collections', 'forge-tournaments', 'edit', $value['item_id'], 'addPhase')),
                    'label' => \i('Add Phase', 'forge-tournaments')
                ))
            ]
        );
    }

}