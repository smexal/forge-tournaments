<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\Media;

class MyorganisationsView extends View {
    public $name = 'my-organisations';
    public $allowNavigation = true;
    private $organisations = null;

    public function getOrganisationsForUser($collection) {
        $organisations = [];
        foreach($collection->items() as $item) {
            $admins = is_array($item->getMeta('admins')) ? $item->getMeta('admins') : [$item->getMeta('admins')];
            $members = is_array($item->getMeta('admins')) ? $item->getMeta('admins') : [$item->getMeta('admins')];
            if (in_array(App::instance()->user->get('id'), $admins) ||
                in_array(App::instance()->user->get('id'), $members)) {
                error_log(print_r($item,true));
                $org = ['name' => $item->getName(),
                        'url' => $item->url(),
                        'key' => $item->getMeta('key')];
                array_push($organisations, $org);
            }
        }
        return $organisations;
    }

    public function content($parts = []) {
        $collection = App::instance()->cm->getCollection('forge-tournaments-organisations');

        $organisations = $this->getOrganisationsForUser($collection);
        foreach ($organisations as $org) {
            // TODO: things...
        }

        return App::instance()->render(MOD_ROOT.'forge-tournaments/templates/',
            'my_organisations',
            [
                'title' => i('Your organisations', 'forge-tournaments'),
                'organisations' => $organisations
            ]
        );
    }
}
