<?php

namespace Forge\Modules\ForgeTournaments\Calculations;

class DataSet {
    private $team_data = [];

    public function __construct() {}

    public function getTeamIDs() {
        return array_keys($this->team_data);
    }

    public function appendTeamData(TeamData $data) {
        if(!$this->hasTeamData($data->getTeamID())) {
            $team_data[$data->getTeamID()] = $data;
            return;
        }
        $team_data[$data->getTeamID()]->merge($data);
    }

    public function hasTeamData($team_id) : bool {
        return isset($this->team_data[$team_id]);
    }

    public function getTeamData($team_id) : array {
        return $this->hasTeamData() ? $this->team_data[$team_id] : null;
    }

    public function getAllTeamData() {
        return $this->team_data;
    }

    public function merge(DataSet $data) {
        foreach($data->getTeamData() as $new_team_data) {
            $this->appendTeamData($new_team_data);
        }
        return $this;
    }

}