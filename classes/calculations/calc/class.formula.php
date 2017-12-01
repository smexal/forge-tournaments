<?php

namespace Forge\Modules\ForgeTournaments\Calculations;

class Formula {
    private $formula = '';
    private $variable_values = [];
    private $result = null;

    public function __construct($formula, $variable_values=[]) {
        $this->formula = $formula;
        $this->variable_values = $variable_values;
    }

    public function validateFormula() {

    }

    public function getResult($variable_values=[]) {
        $this->mergeVariables($variable_values);
        return CalcUtils::applyFormula($this->formula, $this->variable_values);
    }

    public function mergeVariables($variable_values) {
        $this->variable_values = array_merge($this->variable_values, $variable_values);
    }

}