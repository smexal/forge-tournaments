<?php

namespace Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases;

use Forge\Core\Traits\Singleton;
use Forge\Modules\ForgeTournaments\BaseRegistry;
use Forge\Modules\ForgeTournaments\Interfaces\ISubtypeRegistry;

class PhaseRegistry extends BaseRegistry implements ISubtypeRegistry {
    use Singleton;

    protected function __construct() {
        parent::__construct();
    }

    public static function getName() : string {
        return 'IPhaseType';
    }
}