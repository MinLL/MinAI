<?php

class relation {
    static function rollUpRelationshipStatus($relationshipValue, $b_met_before = false) {
        $relationshipValue = intval($relationshipValue ?? 0);
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
            if ($b_met_before) {
                return "an acquaintance of";
            } else 
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
    $relationshipValue = intval($utilities->GetActorValue($targetActor, "relationshipRank") ?? 0); 
    
    $s_first_met = DataRetrieveFirstTimeMet($playerName, $targetName);
    $b_met = ($s_first_met > "");
    $relationshipStatus = relation::rollUpRelationshipStatus($relationshipValue, $b_met);
    $contextString = "{$targetName} is {$relationshipStatus} {$playerName}.\n{$s_first_met}";

    return $contextString;
}
