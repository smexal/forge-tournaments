<?php
namespace Forge\Modules\ForgeTournaments;

use Forge\Core\Traits\Singleton;
use Forge\Core\Interfaces\IMigration;

use Forge\Core\App\App;

class Forgetournaments_0_0_1Migration implements IMigration {
    use Singleton;
    
    public static function identifier() {
        return 'forge-torunaments';
    }

    public static function targetversion() {
        return '0.0.1';
    }

    public static function oninstall() {
        return true;
    }

    public static function prepare() {

    }

    public static function execute() {
        try {
            App::instance()->db->startTransaction();
            App::instance()->db->query(
                'CREATE TABLE `ft_datastorage` (
                    `ref_type` VARCHAR(32) NOT NULL,
                    `ref_id` INT(11) NOT NULL,
                    `source` VARCHAR(16) NOT NULL,
                    `group` VARCHAR(16) NOT NULL,
                    `key` VARCHAR(16) NOT NULL,
                    `value` VARCHAR(64),
                    `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`ref_type`, `ref_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
            );
            App::instance()->db->commit();
        } catch (Exception $e) {
            App::instance()->db->rollback();
        }

    }
}