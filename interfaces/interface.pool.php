<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface IPool {

    public function getInstance($id, $args=[]);

    public function setInstance($id, $instance);
    
}
