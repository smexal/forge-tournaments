<?php

namespace Forge\Modules\ForgeTournaments\Phases;

use \Forge\Modules\ForgeTournaments\PhaseType;

class BasePhase {

    public function fields($item=null) : array {
        return [];
    }

    public function modifyFields(array $fields, $item=null) : array {
        return $fields;
    }

    public function onStateChange($old, $new) {
        return;
    }

}