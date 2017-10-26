<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface IInput {
    function setKey($key);
    function getKey();
    function appendData(ICalcNode $node, array $existing_data) : array;
    function getStatus();
}

