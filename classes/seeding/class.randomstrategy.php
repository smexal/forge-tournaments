<?php

namespace Forge\Modules\ForgeTournaments\Seeding;

use Forge\Modules\ForgeTournaments\Interfaces\ISeedingStrategy;

class RandomStrategy implements ISeedingStrategy {
    public static function getName() {
        return 'random';
    }
   /*
    fn state();
    fn add($existing, $new);
   */

}
