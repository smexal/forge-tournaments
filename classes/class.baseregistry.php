<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Modules\ForgeTournaments\Interfaces\ISubtypeRegistry;

abstract class BaseRegistry {

    protected $cmods = [];

    protected static $registries = [];

    protected function __construct() {
        $cls = get_called_class();
        $r_name = call_user_func($cls . '::getName');
        static::$registries[$r_name] = $this;
    }

    public function prepare() {
        \registerEvent('onModulesLoaded', [$this, 'start']);
    }

    public function start() {
        \fireEvent(FORGE_TOURNAMENT_HOOK_NS . '/Register' . static::getName(), $this);
    }

    public function registerMultiple($cls_list) {
        foreach($cls_list as $cls) {
            $this->register($cls);
        }
    }
    public function register($cls) {
        $if_name = static::getName();
        if($cls instanceof $if_name) {
            throw new \Exception("$cls is not of type $if_name");
        }
        $this->cmods[$cls::identifier()] = new $cls;
    }

    public function getAll() {
        return $this->cmods;
    }

    public function get($m_name) {
        if(!isset($this->cmods[$m_name])) {
            return null;
        }
        return $this->cmods[$m_name];
    }

    public static function getRegistry($r_name) {
        if(isset(static::$registries[$r_name])) {
            return static::$registries[$r_name];
        }
        return null;
    }

    public static function registerTypes($r_name, $list) {
        $reg = static::getRegistry($r_name);
        if(!$reg) {
            throw new \Exception("Registry for $r_name not found");
        }

        $reg->registerMultiple($list);
    }
}