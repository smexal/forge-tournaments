<?php

use \Forge\SuperLoader as SuperLoader;

use Forge\Modules\ForgeTournaments\Calculations\Node;
use Forge\Modules\ForgeTournaments\Calculations\Tree;

abstract class TestUtilsForgeTournaments {

    public static function strBinaryTree($node) {
        $str = '';
        $depth = $node->getDepth();
        $current_node = $node;
        echo print_r($node->identifierArray(), 1);
        for($i = 1; $i < $depth; $i++) {
            $nodes = static::getNodesOfDepth($node, $i);
            $list = array_map(function($node) { return $node->getIdentifier();}, $nodes);
            $str .= implode(' ', $list) . "\n";
        }
        return $node->getIdentifier() . " \n" . $str;
    }

    public static function getNodesOfDepth($node, $depth, $current_depth=0) {
        $list = [];
        if($current_depth == 0 && $depth == $current_depth) {
            return [$node];
        }

        if($current_depth + 1 == $depth) {
            return $node->getChildren();
        }

        foreach($node->getChildren() as $child) {
            $sub_list = static::getNodesOfDepth($child, $depth, $current_depth + 1);
            $list = array_merge($list, $sub_list);
        }

        return $list;
    }

    public static function appendBinaryTree($node, $max_depth, $depth=0, &$id_stack=[]) {
        if($depth==0) {
            if(count($id_stack) > 0) {
                $node->setIdentifier(array_shift($id_stack));
            }
        }
        if($depth == $max_depth) {
            return $node;
        }
        $left = new Node();
        $right = new Node();

        if(count($id_stack) > 0) {
            $left->setIdentifier(array_shift($id_stack));
        }

        if(count($id_stack) > 0) {
            $right->setIdentifier(array_shift($id_stack));
        }

        $node->addChild($left);
        $node->addChild($right);

        foreach($node->getChildren() as &$child) {
            $child = static::appendBinaryTree($child, $max_depth, $depth + 1, $id_stack);
        }
        return $node;
    }

    public static function nameStack() {
        $stack = [];
        for($i = 0; $i < 26; $i++) {
            $stack[] = chr(65 + $i);
        }
        return $stack;
    }


    public static function setup() {
        require_once(dirname(__FILE__) . "/config.php");

        // APP CONFIG
        $prev = error_reporting(0);
        define('DOC_ROOT', TestUtilsForgeTournaments::getAppRoot());
        require_once(TestUtilsForgeTournaments::getAppRoot() . "/config-tests.php");
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