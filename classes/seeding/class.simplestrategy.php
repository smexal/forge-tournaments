<?php

namespace Forge\Modules\ForgeTournaments\Seeding;

use Forge\Modules\ForgeTournaments\Interfaces\ISeedingStrategy;

/**
 *  Just append at the end of the Array
 */
class SimpleStrategy implements ISeedingStrategy  {
    public static function getName() {
        return 'simple';
    }
   /*
    fn state();
    fn add($existing, $new);
   */

}
