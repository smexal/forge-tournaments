<?php

namespace Forge\Modules\ForgeTournaments\Calculations;

class StaticInput extends Input {
    public function __construct($key, IDataSet $data) {
        $this->data = $data;
        parent::__construct($key);
    }
}