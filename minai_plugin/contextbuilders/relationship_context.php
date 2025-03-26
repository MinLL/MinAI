<?php

class relation {
    static function rollUpRelationshipStatus($relationshipValue) {
        $statuses = [];
        if ($relationshipValue >= 1) {
            if ($relationshipValue >= 1) {
                $statuses[] = "a friend";
            }
            if ($relationshipValue >= 2) {
                $statuses[] = "confidant";
            }
            if ($relationshipValue >= 3) {
                $statuses[] = "ally";
            }
            if ($relationshipValue == 4) {
                $statuses[] = "lover";
            }
        } elseif ($relationshipValue <= -1) {
            if ($relationshipValue <= -1) {
                $statuses[] = "a rival";
            }
            if ($relationshipValue <= -2) {
                $statuses[] = "foe";
            }
            if ($relationshipValue <= -3) {
                $statuses[] = "enemy";
            }
            if ($relationshipValue == -4) {
                $statuses[] = "archenemy";
            }
        } else {
            return "a stranger to";
        }

        if (count($statuses) > 1) {
            return relation::rollUpAList($statuses) . " of";
        } else {
            return $statuses[0] . " of";
        }
    }

    static function rollUpAList($someArray) {
        $count = count($someArray);
        if ($count > 1) {
            $someArray[$count - 1] = "and " . $someArray[$count - 1];
        }
        return implode(", ", $someArray);
    }
}

function GetRelationshipContext($targetActor) {
    if ($targetActor == $GLOBALS["PLAYER_NAME"] || $targetActor == "The Narrator") {
        return "";
    }
    $utilities = new Utilities();
    $playerName = $GLOBALS["PLAYER_NAME"];
    $targetName = $targetActor;
    $relationshipValue = $utilities->GetActorValue($targetActor, "relationshipRank");

    $relationshipStatus = relation::rollUpRelationshipStatus($relationshipValue);
    $contextString = "$targetName is $relationshipStatus $playerName.";

    return $contextString;
}