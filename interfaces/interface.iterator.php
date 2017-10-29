<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface INodeIterator {

    public function nextNode() : INode;

    public function hasNextNode() : bool;

}
