<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface IScoringProvider {

    public function addScoring($scoring);

    public function getAllScorings();
    
    public function getScoring($id);

}
