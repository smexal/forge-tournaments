<?php

namespace Forge\Modules\ForgeTournaments\Fields;

use Forge\Core\App\App;
use Forge\Modules\ForgeTournaments\Utils;


class PhaseSteps {
    public static function render($args, $value) {
        $values = $args['values'];
        
        $prev_state = Utils::getPrevPhaseState($value);
        $next_state = Utils::getNextPhaseState($value);
        
        if(!is_null($prev_state)) {
            $prev_state = [
                'value' => $prev_state,
                'label' => '&lsaquo;&nbsp;' . substr($values[$prev_state], 0, 11) . '.'
            ];
        }
        if(!is_null($next_state)) {
            $next_state = [
                'value' => $next_state,
                'label' => substr($values[$next_state], 0, 11) . '. ' . '&nbsp;&rsaquo;'
            ];
        }

        $args['name'] = $args['key'];
        $args['value'] = $value;
        $args['current'] = Utils::getPhaseStates()[$value];
        $args['prev'] = $prev_state;
        $args['next'] = $next_state;

        return App::instance()->render(
            MOD_ROOT.'forge-tournaments/templates/fields',
            'phasesteps',
            $args
        );
    }
}