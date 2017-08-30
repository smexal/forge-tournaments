<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface IPhaseType {
    public static function identifier() : string;
    public static function name() : string;
    public function fields($item=null) : array;
    public function modifyFields(array $fields, $item=null) : array;
    public function onStateChange($old, $new);
}

