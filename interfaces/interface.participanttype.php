<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

use \Forge\Core\Classes\CollectionItem;

interface IParticipantType extends ICollectionExtension {
    public static function identifier() : string;
    public static function name() : string;
}

