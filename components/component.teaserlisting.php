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
        $this->settings = array_merge([
            [
                'label' => i('Display type', 'forge-tournaments'),
                'hint' => i('Small or big display type', 'forge-tournaments'),
                'key' => 'display_type',
                'type' => 'select',
                'values' => [
                    'big' => i('Big Teaser Elements', 'forge-tournaments'),
                    'small' => i('Small Teasr Elements', 'forge-tournaments')
                ]
            ]
        ], $this->settings);
        return [
            'name' => i('Tournament Teaser Listing', 'allocate'),
            'description' => i('Select and list tournaments.', 'allocate'),
            'id' => 'tournament-teaser-listing',
            'image' => '',
            'level' => 'inner',
            'container' => false
        ];
    }

    public function beforeContent() {
        if($this->getField('display_type') == 'small') {
            $this->cssClasses[] = 'small';
        };
    }

    public function renderItem($item) {
        if($this->getField('display_type') == 'small') {
            $image = $item->getMeta('image_thumbnail');
            $image = new Media($image);
            $image = $image->getSizedImage(100, 80);
        } else {
            $image = $item->getMeta('image_big');
            $image = new Media($image);
            $image = $image->getSizedImage(1120, 660);
        }

        return App::instance()->render(DOC_ROOT.'modules/forge-tournaments/templates/components/',
            'teaser-listing-item',
            [
                'title' => $item->getMeta('title'),
                'image' => $image,
                'subtitle' => $item->getMeta('description')
            ]
        );
    }
}
?>