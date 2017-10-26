<?php

use \Forge\SuperLoader as SuperLoader;

abstract class TestUtilsForgeTournaments {

    public static function setup() {
        require_once(dirname(__FILE__) . "/config.php");

        // APP CONFIG
        $prev = error_reporting(0);
        define('DOC_ROOT', TestUtilsForgeTournaments::getAppRoot());
        require_once(TestUtilsForgeTournaments::getAppRoot() . "/config.php");
        error_reporting($prev);
        
        // MODULE CONFIG
        require_once(TestUtilsForgeTournaments::getModuleRoot() . "/config.php");
        
        TestUtilsForgeTournaments::initSuperLoader();
        require_once(CORE_ROOT . "libs/helpers/additional_functions.php");
        require_once(CORE_ROOT . "libs/helpers/core_facade.php");
    }

    public static function getAppRoot() {
        $app_root = str_replace('\\', '/', getcwd());
        $app_root = preg_replace('/(.*\/)modules\/forge-tournaments\/.*/', '$1', $app_root);
        $app_root = str_replace('/', DIRECTORY_SEPARATOR, $app_root);
        return $app_root;
    }
    
    public static function getModuleRoot() {
        return dirname(dirname(dirname(__FILE__)));
    }

    public static function initSuperloader($flush=false) {
        $app_root = static::getAppRoot();

        require_once("${app_root}config.php");
        require_once("${app_root}core/superloader.php");
        require_once("${app_root}core/loader.php");

        SuperLoader::$BASE_DIR = $app_root;
        SuperLoader::$FLUSH = $flush;
        spl_autoload_register(array(SuperLoader::instance(), "autoloadClass"));
    }

}