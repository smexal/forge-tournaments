<?php

namespace Forge\Modules\ForgeTournaments\Calculations;

use Forge\Modules\ForgeTournaments\Interfaces\ICalculation;

class CalculationInput extends Input implements ICalculation {
    private $formula = null;

    public function __construct($key, $formula='') {
        $this->formula = $formula;
        parent::__construct($key);
    }

    public function appendData(IDataSet $data, ICalcNode $node) {
        foreach($data->getAllTeamData() as $team_data) {
            $team_data->merge($this->calculate($team_data));
        }
    }

    public function calculate(ITeamData $team_data) : ITeamData {
        $formula = new Formula($this->formula);
        $base_data = $team_data->getData();
        
        $result = $formula->getResult($base_data);
        return new TeamData($team_data->getID(), [
            $this->getKey() => $result
        ]);
    }

}