<?php

namespace Forge\Modules\ForgeTournaments\Scoring;

use Forge\Modules\ForgeTournaments\Interfaces\IScoringProvider;

use Forge\Core\Traits\Singleton;

class ScoringProvider implements IScoringProvider {
    use Singleton;

    private $scorings = [];

    protected function __construct() {}

    public function addScoring($scoring) {
        $this->scorings[$scoring['id']] = $scoring;
    }

    public function getAllScorings() {
        return $this->scorings;
    }

    public function getScoring($id) {
        if(isset($this->scorings[$id])) {
            return $this->scorings[$id];
        }
        return null;
    }

}