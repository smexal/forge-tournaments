<?php

namespace Forge\Modules\ForgeTournaments\Calculations\Nodes;

use Forge\Modules\ForgeTournaments\Interfaces\ICalcNode;
use Forge\Modules\ForgeTournaments\Interfaces\IInput;
use Forge\Modules\ForgeTournaments\Interfaces\ICalculation;


class CalcNode extends Node implements ICalcNode {
    // Eg.:TournamentXY.PhaseA.GroupZ.EncounterB.MatchT
    private $identifier = '';
    private $inputs = [];
    private $calculations = [];
    protected $data = [];

    public function __construct($identifier) {
        $this->identifier = $identifier;
    }

    public function addInputs(IInput $input) {
        $this->inputs[] = $input;
    }

    public function addCalculation(ICalculation $calculation) {
        $this->calculations[] = $calculation;
    }

    public function inputsReady() : bool {
        foreach($this->inputs as $input) {
            if($input->getState() != Input::STATE_OK) {
                return false;
            }
        }
        return true;
    }

    public function gatherInputData() {
        $data = [];
        foreach($this->inputs as $input) {
            $data[$input->getKey()] = $input->getData($this);
        }

        $data['_children'] = [];
        foreach($this->getChildren() as $child) {
            $data['_children'][] = $chid->gatherInputData();
        }
        return $data;
    }

    public function recalculate() {
        $data = $this->gatherInputData();
        foreach($this->getChildren() as $child) {
            $child->recalculate();
        }
        foreach($this->calculations as $calculation) {
            $data[$calculation->getKey()] = $calculation->getData($this, $data);
        }
        $this->data = $data;
    }

}