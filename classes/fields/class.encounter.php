<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;

class EncounterField {
    public function field($args, $value) {
        return App::instance()->render(
            MOD_ROOT.'forge-tournaments/templates/fields',
            'encounter',
            [
                'id' => $args['key'],
                'name' => $args['key'],
                'label' => $args['label'],
                'value' => $value,
                'hint' => $args['hint'],
                'disabled' => false
            ]
        );
    }
}
