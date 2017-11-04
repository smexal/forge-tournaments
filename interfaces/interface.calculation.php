<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface ICalculation {
    public function calculate(IDataSegment $data_segment) : IDataSegment;
}