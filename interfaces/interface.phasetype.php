<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

use \Forge\Core\Classes\CollectionItem;

interface IPhaseType extends ICollectionExtension {
    public static function identifier() : string;
    public static function name() : string;
    public function onStateChange($old, $new);
}

