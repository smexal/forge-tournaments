<?php

namespace Forge\Modules\ForgeTournaments;

abstract class Output {
    public static function participantList($participants) {
        $html = "<ul>";
        for($i = 0; $i < $participants->numSlots(); $i++) {
            $participant = $participants->getSlot($i);
            $slot_name = is_null($participant) ? 'EMPTY' : $participant->getName();

            $html .= '<li>';
            $html .= 'Slot ' . ($i + 1) .': ';
            $html .= '<span>' . $slot_name . '</span>';
            $html .= '</li>';
        }
        $html .= "</ul>";
        return $html;
    }
}
