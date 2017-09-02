<?php

namespace Forge\Modules\ForgeTournaments\CollectionSubtypes\Participants;

use \Forge\Modules\ForgeTournaments\ParticipantType;
use \Forge\Core\Classes\CollectionItem;

abstract class BaseParticipant {

    public function fields($item=null) : array {
        return [];
    }

    public function modifyFields(array $fields, $item=null) : array {
        return $fields;
    }

    public function render(CollectionItem $item) : string {
        return '';
    }

}