<?php

namespace Forge\Modules\ForgeTournaments\Interfaces;

interface ISubtypeRegistry {
   static function instance();
   static function getName() : string;
}