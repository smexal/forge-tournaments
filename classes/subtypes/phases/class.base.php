<?php

namespace Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases;

use Forge\Modules\ForgeTournaments\PhaseType;
use Forge\Core\Classes\CollectionItem;
use Forge\Core\Classes\Media;
use Forge\Core\Classes\User;
use Forge\Modules\ForgeTournaments\ParticipantCollection;
use Forge\Modules\TournamentsTeams\TeamsCollection;

abstract class BasePhase {
    protected $parentPhase = null;

    public function fields($item=null) : array {
        return [];
    }

    public function modifyFields(array $fields, $item=null) : array {
        return $fields;
    }

    public function onStateChange($old, $new) {
        return;
    }

    public function render(CollectionItem $item) : string {
        return '';
    }

    public function setPhase($phase) {
        $this->parentPhase = $phase;
    }

    protected function getAvatarImage($participant) {
        if(is_null($participant)) {
            return;
        }
        if(is_numeric($participant->getMeta('user'))) {
            $user = new User($participant->getMeta('user'));
            $image = $user->getAvatar();
        } else {
            $team = ParticipantCollection::getTeam($participant->getID());
            $orga = TeamsCollection::getOrganization($team);
            $orga = new CollectionItem($orga);
            $image = new Media($orga->getMeta('logo'));
            $image = $image->getSizedImage(60, 60);
        }
        return $image;
    }

}