<?php

namespace Forge\Modules\ForgeTournaments\Phases;

use Forge\Core\Traits\Singleton;
use \Forge\Modules\ForgeTournaments\Interfaces\IPhaseType;
use \Forge\Modules\ForgeTournaments\PhaseType;

class PhaseRegistry {
    use Singleton;
    private $phases = [];

    public function prepare() {
        \registerEvent('onModulesLoaded', [$this, 'start']);
    }

    public function start() {
        \fireEvent(FORGE_TOURNAMENT_HOOK_NS . '/RegisterPhaseTypes', $this);
    }

    public function register($cls) {
        if($cls instanceof IPhaseType) {
            throw new \Exception("$cls is not of type IPhaseType");
        }
        $this->phases[$cls::identifier()] = new $cls;
    }

    public function getAll() {
        return $this->phases;
    }

    public function get($name) {
        if(!isset($this->phases[$name])) {
            return null;
        }
        return $this->phases[$name];
    }
}