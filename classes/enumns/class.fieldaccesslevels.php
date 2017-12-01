<?php
namespace Forge\Modules\ForgeTournaments;

abstract class FieldAccessLevel {
    const ACCESS_ADMIN  = 5;
    const ACCESS_SYSTEM = 10;
    const ACCESS_COACH  = 20;
    const ACCESS_PUBLIC = 30;


    public static function getAccessLevels() {
        return [
            FieldAccessLevel::ACCESS_ADMIN,
            FieldAccessLevel::ACCESS_SYSTEM,
            FieldAccessLevel::ACCESS_COACH,
            FieldAccessLevel::ACCESS_PUBLIC
        ];
    }
}
