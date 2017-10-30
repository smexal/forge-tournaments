<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface INodeIterator {

    public function nextNode();

    public function hasStarted() : bool;
    
    public function hasFinished() : bool;

}
