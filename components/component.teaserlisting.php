<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\Classes\Media;
use \Forge\Core\Abstracts\Components;
use \Forge\Core\App\App;
use \Forge\Core\Components\ListingComponent;
use \Forge\Modules\ForgeTournaments\Facade\Tournament as TournamentFacade;



class TeaserlistingComponent extends ListingComponent {
    protected $collection = 'forge-tournaments';
    protected $cssClasses = ['wrapper', 'teaser-listing'];

    public function prefs() {
        $this->settings = array_merge([
            [
                'label' => i('Display type', 'forge-tournaments'),
                'hint' => i('Small or big display type', 'forge-tournaments'),
                'key' => 'display_type',
                'type' => 'select',
                'values' => [
                    'big' => i('Big Teaser Elements', 'forge-tournaments'),
                    'small' => i('Small Teasr Elements', 'forge-tournaments'),
                    'compact' => i('Compact Listing with more information', 'forge-tournaments')
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
        if($this->getField('display_type') == 'compact') {
            $this->cssClasses[] = 'compact';
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
        $signupText = i('Signup now', 'forge-tournaments');
        $signupUrl = $item->url(false, ['signup']);
        $activePhase = $this->getActivePhase($item);

        if($this->getField('display_type') == 'compact') {
            $image = $item->getMeta('image_big');
            $image = new Media($image);
            $image = $image->getSizedImage(240, 140);

            return App::instance()->render(DOC_ROOT.'modules/forge-tournaments/templates/components/',
                'compact-listing',
                [
                    'image' => $image,
                    'title' => $item->getMeta('title'),
                    'url' => $item->url(),
                    'participants_title' => i('Participants', 'forge-tournaments'),
                    'participants' => count(TournamentCollection::getParticipants($item->getID())),
                    'participants_max' => $item->getMeta('max_participants'),
                    'phase_title' => i('Active Phase', 'forge-tournaments'),
                    'phase_name' => 'yo',
                    'signup' => $item->getMeta('allow_signup'),
                    'signup_text' => $signupText,
                    'signup_url' => $signupUrl,
                    'active_phase' => $activePhase
                ]
            );
        }

        return App::instance()->render(DOC_ROOT.'modules/forge-tournaments/templates/components/',
            'teaser-listing-item',
            [
                'url' => $item->url(),
                'title' => $item->getMeta('title'),
                'image' => $image,
                'subtitle' => $item->getMeta('description')
            ]
        );
    }

    private function getActivePhase($item) {
        $tournament = TournamentFacade::getTournament($item->getID());
        $phases = $tournament->getPhases();
        $activePhase = false;
        foreach($phases as $phase) {
            if(is_null($phase->getMeta('ft_phase_state'))) {
                continue;
            }
            if($phase->getMeta('ft_phase_state') == PhaseState::RUNNING) {
                $activePhase = [
                    'title' => $phase->getMeta('title'),
                    'url' => $item->url().'/phase/'.$phase->getID()
                ];
            }
        }
        return $activePhase;
    }
}
?>