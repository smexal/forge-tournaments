<?php

namespace Forge\Modules\ForgeTournaments\Calculations;

class TeamData {
    private $team_id = null;
    private $data = [];

    public function __construct($team_id, $data) {
        $this->team_id = $team_id;
        $this->data = $data;
    }

    public function getTeamID() {
        return $this->team_id;
    }

    public function setValue($key, $value) {
        $this->data[$key] = $value;
    }

    public function getValue($key, $value) {
        $this->data[$key] = $value;
    }

    public function getData() : array {
        return $this->data;
    }

    public function merge(array $data) {
        if(is_null($data)) {
            return;
        }
        $this->data = array_merge($this->data, $data->getData());
        return $this;
    }

}