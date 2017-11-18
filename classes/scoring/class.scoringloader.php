<?php

namespace Forge\Modules\ForgeTournaments\Scoring;

use Forge\Core\Traits\Singleton;

use Forge\Modules\ForgeTournaments\Scoring\ScoringProvider;

class ScoringLoader {
    use Singleton;

    private $loaded = false;
    
    public function load() {
        if($this->loaded) {
            return;
        }
        $scoring_provider = ScoringProvider::instance();
        $base = FORGE_TOURNAMENTS_SCORINGS_DIR;
        $files = glob($base . '*.php');
        if (count($files) > 0) {
            foreach ($files as $file) {
                if (substr($file, 0, 1) == '.') {
                    continue;
                }

                list($id, $name, $config) = require_once $file;
                $scoring = [
                    'id' => $id,
                    'name' => $name,
                    'config' => $config
                ];
                $scoring_provider->addScoring($scoring);
            }
        }
        $this->loaded = true;
    }

}