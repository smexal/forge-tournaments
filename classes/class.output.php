<?php

namespace Forge\Modules\ForgeTournaments;

abstract class Output {
    const RENDER_VIEW='view';
    const RENDER_ADMIN='view';
    const RENDER_ADMIN_SMALL='view';

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

    public static function renderTournamentEntity($type, $entity) {
        if(!in_array($type, [Output::RENDER_VIEW, Output::RENDER_ADMIN, Output::RENDER_ADMIN_SMALL])) {
            $entity_key = is_object($entity) ? $entity->getID() : $entity;
            throw new \Exception("Can not render the undefined output type `$type` for entity `$entity_key`");
        }
        switch ($entity->getType()) {
            case TournamentCollection::COLLECTION_NAME:
                return "RENDER RENDER TOURNAMENT COLLECTION SENDER";
            break;

            case PhaseCollection::COLLECTION_NAME:
                return static::renderPhase($type, $entity);
            break;

            case GroupCollection::COLLECTION_NAME:
                return "RENDER RENDER GROUP COLLECTION SENDER";
            break;

            case EncounterCollection::COLLECTION_NAME:
                return "RENDER RENDER ENCOUNTER COLLECTION SENDER";
            break;
            
            default:
                # code...
            break;
        }
    }

    public static function renderPhase($type, $phase) {
        switch ($phase->getPhaseType()) {
            case \Forge\Modules\ForgeTournaments\PhaseTypes::REGISTRATION:
                return "RENDER RENDER REGISTRATION PHASE COLLECTION SENDER>";
            break;

            case \Forge\Modules\ForgeTournaments\PhaseTypes::GROUP:
                return "<p>RENDER RENDER GROUP PHASE COLLECTION SENDER</p>";
            break;

            case \Forge\Modules\ForgeTournaments\PhaseTypes::KOSYSTEM:
                return "RENDER RENDER KOSYSTEM PHASE COLLECTION SENDER";
            break;

            case \Forge\Modules\ForgeTournaments\PhaseTypes::PERFORMANCE:
                return "RENDER RENDER PERFORMANCE PHASE COLLECTION SENDER";
            break;
            
            default:
                # code...
                break;
        }
    }
}
