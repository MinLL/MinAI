<?php
// Get player's bounty information from the Crime module
Function GetBountyContext($targetActor) {
    $ret = "";
    $bountyInfo = GetActorValue($targetActor, "bountyContext");
    
    if (empty($bountyInfo)) {
        return $ret; // No bounty information available
    }
    
    // Parse the bounty information
    $bounties = explode(";", $bountyInfo);
    $activeBounties = [];
    
    foreach ($bounties as $bounty) {
        if (empty(trim($bounty))) continue;
        
        $parts = explode(":", $bounty);
        if (count($parts) == 2) {
            $hold = trim($parts[0]);
            $amount = intval(trim($parts[1]));
            
            if ($amount > 0) {
                $activeBounties[$hold] = $amount;
            }
        }
    }
    
    // If no active bounties, return empty string
    if (empty($activeBounties)) {
        return $ret;
    }

    // Special case for the narrator - show all bounties with more descriptive text
    if ($GLOBALS["HERIKA_NAME"] == "The Narrator") {
        $totalBounty = array_sum($activeBounties);
        $numHolds = count($activeBounties);
        
        if ($numHolds == 1) {
            $holdName = array_keys($activeBounties)[0];
            $bountyAmount = reset($activeBounties);
            $ret .= $GLOBALS["PLAYER_NAME"] . " is currently wanted by the law in " . $holdName . " with a bounty of " . $bountyAmount . " gold. Guards from this hold would recognize and attempt to arrest " . $GLOBALS["PLAYER_NAME"] . " on sight.\n";
        } else {
            $ret .= $GLOBALS["PLAYER_NAME"] . " is currently a wanted criminal with bounties in " . $numHolds . " different holds of Skyrim, totaling " . $totalBounty . " gold.\n";
            
            // List each bounty from highest to lowest
            arsort($activeBounties);
            foreach ($activeBounties as $hold => $amount) {
                $ret .= "- " . $hold . ": " . $amount . " gold\n";
            }
            
            $ret .= "Guards from these holds would recognize and attempt to arrest " . $GLOBALS["PLAYER_NAME"] . " on sight.\n";
        }
        
        return $ret;
    }
    
    // For guard NPCs, determine their hold directly from allFactions
    $allFactions = GetActorValue($targetActor, "allFactions");
    $lowerAllFactions = strtolower($allFactions);
    $matchedHold = null;
    
    // Direct mapping of faction keywords to hold names
    $holdMatches = [
        "whiterun" => "Whiterun",
        "riften" => "Rift",
        "eastmarch" => "Eastmarch",
        "windhelm" => "Eastmarch",
        "haafingar" => "Haafingar", 
        "solitude" => "Haafingar",
        "reach" => "Reach",
        "markarth" => "Reach",
        "falkreath" => "Falkreath",
        "hjaalmarch" => "Hjaalmarch", 
        "morthal" => "Hjaalmarch",
        "pale" => "Pale",
        "dawnstar" => "Pale",
        "winterhold" => "Winterhold"
    ];
    
    // Look for specific hold keywords in allFactions
    foreach ($holdMatches as $keyword => $holdName) {
        if (strpos($lowerAllFactions, $keyword) !== false) {
            // Find matching hold in active bounties
            foreach ($activeBounties as $hold => $amount) {
                if (strpos(strtolower($hold), strtolower($holdName)) !== false) {
                    $matchedHold = $hold;
                    break 2; // Break both loops once found
                }
            }
        }
    }
    
    // If we found a matching hold, prioritize it
    if ($matchedHold && isset($activeBounties[$matchedHold])) {
        $ret .= "The guard recognizes " . $GLOBALS["PLAYER_NAME"] . " as a wanted criminal with a bounty of " . 
               $activeBounties[$matchedHold] . " gold in " . $matchedHold . ".\n";
        
        // Mention other bounties briefly if in higher ranks of the guard
        $isHighRanking = strpos($lowerAllFactions, "captain") !== false || 
                        strpos($lowerAllFactions, "commander") !== false || 
                        strpos($lowerAllFactions, "officer") !== false ||
                        strpos(strtolower($targetActor), "captain") !== false ||
                        strpos(strtolower($targetActor), "commander") !== false ||
                        strpos(strtolower($targetActor), "officer") !== false;
        
        if ($isHighRanking) {
            $otherBounties = array_diff_key($activeBounties, [$matchedHold => 0]);
            if (!empty($otherBounties)) {
                $ret .= "Being well-connected within the guard network, the officer is also aware that " . $GLOBALS["PLAYER_NAME"] . " has bounties in other holds: ";
                $bountyList = [];
                foreach ($otherBounties as $hold => $amount) {
                    $bountyList[] = $hold . " (" . $amount . " gold)";
                }
                $ret .= implode(", ", $bountyList) . ".\n";
            }
        }
        
        return $ret;
    }
    
    // If no specific faction matched, show all bounties in a generic way
    $ret .= $GLOBALS["PLAYER_NAME"] . " has active bounties in the following holds: ";
    $bountyList = [];
    foreach ($activeBounties as $hold => $amount) {
        $bountyList[] = $hold . " (" . $amount . " gold)";
    }
    $ret .= implode(", ", $bountyList) . ".\n";
    
    return $ret;
}