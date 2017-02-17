<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Loader;
use \Forge\Core\Abstracts\Module;
use \Forge\Core\App\API;
use \Forge\Core\App\Auth;
use \Forge\Core\App\App;

class ForgeTournaments extends Module {
    private $permission = 'manage.forge-tournaments';

    public function setup() {
        $this->version = '1.0.0';
        $this->id = "forge-tournaments";
        $this->name = i('Forge Tournaments', 'forge-tournaments');
        $this->description = i('Tournament Management for Forge.', 'forge-tournaments');
        $this->image = $this->url().'assets/images/module-image.png';
    }

    public function start() {
        Auth::registerPermissions($this->permission);

        // backend
        Loader::instance()->addStyle("modules/forge-tournaments/assets/css/forge-tournaments.less");

        Loader::instance()->addScript("modules/forge-tournaments/assets/scripts/forge-tournaments.js");

        // frontend
        App::instance()->tm->theme->addScript($this->url()."assets/scripts/forge-tournaments.js", true);
        App::instance()->tm->theme->addScript(CORE_WWW_ROOT."ressources/scripts/externals/tooltipster.bundle.min.js", true);

        App::instance()->tm->theme->addStyle(MOD_ROOT."forge-tournaments/assets/css/forge-tournaments.less");
        App::instance()->tm->theme->addStyle(MOD_ROOT."forge-tournaments/assets/css/bracket.less");
        App::instance()->tm->theme->addStyle(CORE_WWW_ROOT."ressources/css/externals/tooltipster.bundle.min.css");

        API::instance()->register('forge-tournaments', array($this, 'apiAdapter'));
    }

    public function apiAdapter($data) {
        switch($data['query'][0]) {
            default:
                return false;
        }
    }

}

?>
