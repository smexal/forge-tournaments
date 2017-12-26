<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Classes\Media;
use \Forge\Core\Abstracts\Components;
use \Forge\Core\App\App;
use \Forge\Core\Components\ListingComponent;



class TeaserlistingComponent extends ListingComponent {
    protected $collection = 'forge-tournaments';
    protected $cssClasses = ['wrapper', 'reveal', 'teaser-listing'];

    public function prefs() {
        return [
            'name' => i('Tournament Teaser Listing', 'allocate'),
            'description' => i('Select and list tournaments.', 'allocate'),
            'id' => 'tournament-teaser-listing',
            'image' => '',
            'level' => 'inner',
            'container' => false
        ];
    }
    public function renderItem($item) {
        $image = $item->getMeta('image_big');
        $image = new Media($image);

        return App::instance()->render(DOC_ROOT.'modules/forge-tournaments/templates/components/',
            'teaser-listing-item',
            [
                'title' => $item->getMeta('title'),
                'image' => $image->getSizedImage(1120, 660),
                'subtitle' => $item->getMeta('description')
            ]
        );
    }
}
?>