<?php

namespace Forge\Modules\ForgeTournaments\Calculations\Inputs;

use Forge\Modules\ForgeTournaments\Interfaces\INode;
use Forge\Modules\ForgeTournaments\Interfaces\ICalcNode;
use Forge\Modules\ForgeTournaments\Interfaces\ICalculation;
use Forge\Modules\ForgeTournaments\Interfaces\IDataSegment;
use Forge\Modules\ForgeTournaments\Interfaces\IDataSet;
use Forge\Modules\ForgeTournaments\Calculations\CalcUtils;

class CalculationInput extends Input implements ICalculation {
    private $formula = null;

    public function __construct($key, $formula='') {
        $this->formula = $formula;
        parent::__construct($key);
    }

    public function appendData(IDataSet $data, INode $node) : IDataSet {
        foreach($data->getAllDataSegments() as $data_segment) {
            $data_segment->merge($this->calculate($data_segment));
        }
        return $data;
    }

    public function calculate(IDataSegment $data_segment) : IDataSegment {
        $base_data = $data_segment->getData();
        $result = CalcUtils::applyFormula($this->formula, $base_data, 4);
        return new DataSegment($data_segment->getSegmentID(), [
            $this->getKey() => $result
        ]);
    }

}