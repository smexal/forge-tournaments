<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface INode {

    public function setIdentifier($identifier);

    public function getIdentifier();

    public function setParent(INode $parent);

    public function hasParent() : bool;

    public function addChild(INode $node);

    public function removeChild(INode $node);

    public function addChildren(array $children);

    public function hasChildren() : bool;

    public function getChildren() : array;

}

