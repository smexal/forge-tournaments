<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface ICalcNode {

    public function addInputs(IInput $input);

    public function addCalculation(ICalculations $calculation);

    public function inputsReady() : bool;

    public function gatherInputData();

    public function recalculate();
}

