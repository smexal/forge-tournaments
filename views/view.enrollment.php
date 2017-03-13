<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\Media;



class MyorganisationsView extends View {
    public $name = 'enrollment';

    public function content($parts = []) {
        $collection = App::instance()->cm->getCollection('forge-tournaments');

        $organisations = $this->getOrganisationsForUser($collection);
        foreach ($organisations as $org) {

        }

        return App::instance()->render(MOD_ROOT.'forge-tournaments/templates/views',
            'enrollment',
            [
                'title' => i('Your organisations', 'forge-tournaments'),
                'organisations' => $organisations
            ]
        );
    }
}
