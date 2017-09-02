<?php

namespace Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases;

use \Forge\Modules\ForgeTournaments\PhaseType;
use \Forge\Core\Classes\CollectionItem;

abstract class BasePhase {

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

}