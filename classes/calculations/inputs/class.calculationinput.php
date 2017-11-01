<?php

namespace Forge\Modules\ForgeTournaments\Calculations\Inputs;

use Forge\Modules\ForgeTournaments\Interfaces\ICalcNode;
use Forge\Modules\ForgeTournaments\Interfaces\ICalculation;
use Forge\Modules\ForgeTournaments\Interfaces\IDataSegment;

class CalculationInput extends Input implements ICalculation {
    private $formula = null;

    public function __construct($key, $formula='') {
        $this->formula = $formula;
        parent::__construct($key);
    }

    public function appendData(IDataSet $data, ICalcNode $node) {
        foreach($data->getAllDataSegments() as $data_segment) {
            $data_segment->merge($this->calculate($data_segment));
        }
    }

    public function calculate(DataSegment $data_segment) : IDataSegment {
        $formula = new Formula($this->formula);
        $base_data = $data_segment->getData();
        
        $result = $formula->getResult($base_data);
        return new DataSegment($data_segment->getID(), [
            $this->getKey() => $result
        ]);
    }

}