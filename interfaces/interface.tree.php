<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface ITree {

   public function getRoot() : INode;

   public function getDepth();

}

