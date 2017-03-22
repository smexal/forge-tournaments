<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Abstracts\View;
use \Forge\Core\App\App;
use \Forge\Core\Classes\Fields;
use \Forge\Core\Classes\Media;
use \Forge\Core\Classes\User;

class OrganisationView extends View {
    public $name = 'organisation';
    public $allowNavigation = true;

    public function additionalNavigationForm() {
        $organisations = App::instance()->cm->getCollection('forge-tournaments-organisations')->items();
        $values = [];
        foreach($organisations as $organisation) {
            $values[$organisation->slug()] = $organisation->getMeta('title');
        }
        $formfields = Fields::select([
            'key' => 'add-to-url',
            'label' => i('Select the organisation, that you want to display.'),
            'values' => $values
        ]);
        return ['form' => $formfields];
    }

    public function content($parts = []) {
        $collection = App::instance()->cm->getCollection('forge-tournaments-organisations');
        $item = $collection->getBySlug($parts[0]);
        if (is_null($itme)) {
            App::instance()->redirect('404');
        }
        $members = [];
        $_members = [];
        $_members = array_merge($members, is_array($item->getMeta('admins')) ? $item->getMeta('admins') : [$item->getMeta('admins')]);
        $_members = array_merge($_members, is_array($item->getMeta('members')) ? $item->getMeta('members') : [$item->getMeta('members')]);
        foreach ($_members as $member) {
            $user = new User($member);
            array_push($members, [
                'name' => $user->get('username')
            ]);
        }

        return App::instance()->render(MOD_ROOT.'forge-tournaments/templates/views',
            'organisation',
            [
                'title' => $item->getName(),
                'description' => $item->getMeta('description'),
                'url' => $item->getMeta('url'),
                'members' => $members
            ]
        );
    }
}
