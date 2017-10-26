<?php
namespace Forge\Core\App;

if(class_exists('Auth'))
    return;

class Auth {
    public static function getSessionUserID() {
        return '1337';
    }
}