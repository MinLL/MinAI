<?php
// object oriented way to organize this
// use it like $utilities->GetRevealedStatus($actorName);
class Utilities {
    private $existingFunctionsNames = array(
        "GetRevealedStatus",
        "GetActorValueCache",
        "HasActorValueCache",
        "BuildActorValueCache",
        "CanVibrate",
        "GetActorValue",
        "IsEnabled",
        "IsSexActive",
        "IsSexActiveSpeaker",
        "IsPlayer",
        "IsModEnabled",
        "IsInFaction",
        "HasKeyword",
        "IsConfigEnabled",
        "IsFollower",
        "IsFollowing",
        "IsInScene",
        "IsFollower",
        "ShouldClearFollowerFunctions",
        "ShouldEnableSexFunctions",
        "IsChildActor",
        "IsMale",
        "IsFemale",
        "IsActionEnabled",
        "RegisterAction",
        "StoreRadiantActors",
        "ClearRadiantActors",
        "IsNewRadiantConversation",
        "GetLastInput",
        "IsRadiant",
        "getScene",
        "addXPersonality",
        "getSceneDesc",
        "replaceActorsNamesInSceneDesc",
        "getXPersonality",
        "overrideTargetToTalk",
        "getTargetDuringSex",
        "GetRevealedStatus",
    );

    public function hasMethod($methodName) {
        if(in_array($methodName, $this->existingFunctionsNames)) {
            return true;
        }
        return false;
    }

    public function __call($name, $params=array()) {
        if(method_exists($this, $name)) {
            // for methods attached to this class
            return call_user_func(array($this, $name), $params);
        } else if ($this->hasMethod($name)) {
        // function exists outside of class in this utlities file
        return $name(...$params); 
        }
        else {
            minai_log("info", "Error calling Utilities clas: ". $name . " is not defined as a method or function in util.php");
        }
    }

    public function beingsInCloseRange() {
        $beingsInCloseRange = DataBeingsInCloseRange();
        $realBeings = [];
        $beingsInCloseRange = str_replace("(", "", $beingsInCloseRange);
        $beingsList = explode("|",$beingsInCloseRange);
        if (empty($beingsList)) {
            $nearbyActors = GetActorValue($GLOBALS["PLAYER_NAME"], "nearbyActors");
            if (!empty($nearbyActors)) {
                $beingsList = explode("|", $nearbyActors);
            }
        }
        $count = 0;
        foreach($beingsList as $bListItem) {
            if(strpos($bListItem, " ")===0) {
                // account for Igor| bandit|
                if(count($realBeings)>0){
                    $realBeings[count($realBeings) - 1] .= ",".$bListItem;
                }    
            } else {
                $realBeings[] = $bListItem;
            }
            $count++;
        }
        $result = implode("|", $realBeings);
        return $result;
    }   

    public function beingsInRange() {
        $beingsInRange = DataBeingsInRange();
        $beingsInRange = str_replace("(", "", $beingsInRange);

        $realBeings = [];
        $beingsList = explode("|",$beingsInRange);
        $count = 0;
        foreach($beingsList as $bListItem) {
            if(strpos($bListItem, " ")===0) {
                // account for Igor| bandit|
                if(count($realBeings)>0){
                    $realBeings[count($realBeings) - 1] .= ",".$bListItem;
                }    
            } else {
                $realBeings[] = $bListItem;
            }
            $count++;
        }
        $result = implode("|", $realBeings);
        return $result;
    }
}