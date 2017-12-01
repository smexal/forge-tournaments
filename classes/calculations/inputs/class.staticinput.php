<?php

namespace Forge\Modules\ForgeTournaments\Calculations\Inputs;

use Forge\Modules\ForgeTournaments\Calculations\Inputs\DataSet;
use Forge\Modules\ForgeTournaments\Interfaces\IDataSet;

class StaticInput extends Input {
    public function __construct($key, IDataSet $data) {
        $this->data = $data;
        parent::__construct($key);
    }
}