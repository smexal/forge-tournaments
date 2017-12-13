<?php

namespace Forge\Modules\ForgeTournaments;

abstract class FieldRenderer {
    
    public static function slotAssignment($args, $value = ''){
        return "participant LIST";
    }
}