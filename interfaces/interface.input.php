<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface IInput {

    function setKey($key);

    function getKey();

    function appendData(IDataSet $data, INode $node) : IDataSet;

    function getState();
}
