<?php

class dnb {
    // includes is a common name so put it in a class to avoid name collisions
    static function includes($str, $longerStr) {
        if(strpos($longerStr, strtolower($str)) !== false) return true;
    }
    static function rollUpAList($someArray, $hasHave = false) {
        $count = count($someArray);
        if($count>1) {
            // prepend last element with "and" for "and so-and-so" in list
            $someArray[count($someArray) - 1] = "and " .  $someArray[count($someArray) - 1];
        }
        $str = "";
        if(count($someArray)>1) {
            $str = implode(", ", $someArray); 
        } else $str = $someArray[0];
        if($hasHave) {
            if($count>1) {
                $str .= " have ";
            } else {
                $str .= " has ";
            }
        } else {
            if($count>1) {
                $str .= " are ";
            } else {
                $str .= " is ";
            }
        }
        return $str;
    }
    
    // Helper function to get descriptions for different dirt and blood states
    static function getStateDescription($state, $hasHave = false) {
        switch($state) {
            case "Clean":
                return "immaculately clean and well-groomed, with not a speck of dirt or grime to be seen. Their skin glows with health, and their attire has been meticulously maintained.";
            case "Dirt1":
                return "lightly dusted with dirt on their clothes and skin, the kind that comes from a normal day of travel or work in Skyrim. Nothing unusual or particularly noticeable by local standards.";
            case "Dirt2":
                return "visibly dirty with grime accumulated across their clothing and exposed skin. Their appearance suggests it's been several days since they've had a proper bath or cleaned their garments.";
            case "Dirt3":
                return "heavily soiled with thick layers of dirt and grime covering much of their body and clothing. A noticeable earthy smell emanates from them, and their fingernails are blackened with embedded dirt.";
            case "Dirt4":
                return "absolutely caked in layers of thick, encrusted filth from head to toe. Their features are barely distinguishable beneath the grime, and they emit a powerful stench of rot, sweat, and decay that causes people nearby to wrinkle their noses in disgust.";
            case "Blood1":
                return "marked with scattered bloodstains across their garments and skin - spatter marks and small patches that suggest recent violence or perhaps tending to wounds. Though noticeable, the blood is minimal and has begun to dry in places.";
            case "Blood2":
                return "showing prominent large bloodstains spread across their clothing and armor. Dark crimson patches and streaks suggest they've been in a serious confrontation recently. The metallic scent of blood lingers around them.";
            case "Blood3":
                return "quite bloody, with gore liberally splashed across their person. Their hands and forearms are particularly stained with half-dried blood, and numerous spatters mark their face and clothing. They look as though they've just walked away from intense combat.";
            case "Blood4":
                return "covered in blood to a horrifying degree. They are drenched in crimson from head to toe, with blood soaking their clothing and armor, pooling in crevices, and still glistening wetly in places. Bloody footprints mark their path, and the overwhelming metallic smell of fresh gore surrounds them like an aura.";
            case "Bathing":
                return "currently bathing, with water droplets cascading down their exposed skin as they cleanse themselves of accumulated grime and sweat.";
            case "Professional":
                return "carrying dirt that appears related to their profession. The specific pattern of staining and residue suggests the honorable grime of daily work rather than simple neglect.";
            case "Bandit":
                return "showing a distinctive pattern of grime that resembles that of wilderness travelers or those who've recently fought with bandits. There's a particular mixture of forest debris, campfire smoke residue, and weathered stains that could come from extended time in bandit territory or possibly rough living in the wilds.";
            case "Lavender":
                if ($hasHave) return "the soothing, delicate aroma of lavender wafting pleasantly through the air around them, suggesting a recent bath with fine scented soap.";
                return "freshly bathed and impeccably clean. Their skin and clothing emit a soothing, delicate aroma of lavender that wafts pleasantly through the air around them.";
            case "Blue":
                if ($hasHave) return "a refreshing fragrance of blue mountain flowers emanating from their person. The sweet, slightly crisp scent suggests a recent thorough bathing with fine-quality soap.";
                return "exceptionally clean, with a refreshing fragrance of blue mountain flowers emanating from their person. The sweet, slightly crisp scent suggests a recent thorough bathing with fine-quality soap.";
            case "Red":
                if ($hasHave) return "the warm, comforting scent of red mountain flowers clinging to their skin and clothes. The pleasantly floral aroma is unmistakable to those familiar with fine soaps.";
                return "evidence of recent and thorough bathing, with the warm, comforting scent of red mountain flowers clinging to their skin and clothes. The pleasantly floral aroma is unmistakable to those familiar with fine soaps.";
            case "DragonsTongue":
                if ($hasHave) return "the exotic and slightly spicy aroma of dragon's tongue flowers surrounding them. The distinctive scent speaks of luxury and attention to personal hygiene.";
                return "immaculately clean, surrounded by the exotic and slightly spicy aroma of dragon's tongue flowers. The distinctive scent speaks of luxury and attention to personal hygiene.";
            case "Purple":
                if ($hasHave) return "the rich, enchanting fragrance of purple mountain flowers. The rare and sought-after scent indicates both wealth and fastidious grooming habits.";
                return "pristine cleanliness, carrying with them the rich, enchanting fragrance of purple mountain flowers. The rare and sought-after scent indicates both wealth and fastidious grooming habits.";
            case "Superior":
                if ($hasHave) return "an intoxicating, complex bouquet of various mountain flowers surrounding them. The layered, sophisticated fragrance suggests use of the finest and most expensive bathing products available in Skyrim.";
                return "perfect cleanliness, surrounded by an intoxicating, complex bouquet of various mountain flowers. The layered, sophisticated fragrance suggests use of the finest and most expensive bathing products available in Skyrim.";
            default:
                return "";
        }
    }
}

// Get dirt and blood information for a single actor by name
function GetSingleActorDirtAndBloodContext($actorName) {
    $utilities = new Utilities();
    if (!$utilities->IsModEnabled("DirtAndBlood")) {
        return "";
    }
    
    if($actorName == "") return "";
    
    $listOfTags = $utilities->GetActorValue($actorName, "dirtAndBlood");
    if(empty($listOfTags)) return "";
    
    $verbiage = "";
    $primaryStates = ["Clean", "Dirt1", "Dirt2", "Dirt3", "Dirt4", "Blood1", "Blood2", "Blood3", "Blood4", "Bathing"];
    $fallbackStates = ["Professional", "Bandit"];
    $scents = ["Lavender", "Blue", "DragonsTongue", "Red", "Purple", "Superior"];
    
    // Track if a primary state was found
    $primaryStateFound = false;
    
    // Check for primary state first
    foreach($primaryStates as $state) {
        if(dnb::includes($state, $listOfTags)) {
            $verbiage .= $actorName . " is " . dnb::getStateDescription($state) . "\n";
            $primaryStateFound = true;
            break; // Only one primary state applies
        }
    }
    
    // Only check fallback states if no primary state was found
    if(!$primaryStateFound) {
        // Check professional first
        if(dnb::includes("Professional", $listOfTags)) {
            $verbiage .= $actorName . " shows signs of " . dnb::getStateDescription("Professional") . "\n";
        }
        // Then check bandit if professional not found
        else if(dnb::includes("Bandit", $listOfTags)) {
            $verbiage .= $actorName . " " . dnb::getStateDescription("Bandit") . "\n";
        }
    }
    
    // Check for scents (these can apply regardless of dirt/blood status)
    foreach($scents as $scent) {
        if(dnb::includes($scent, $listOfTags)) {
            $verbiage .= $actorName . " has " . dnb::getStateDescription($scent, true) . "\n";
            break; // Usually only one scent applies
        }
    }
    
    if(empty($verbiage)) {
        return "";
    }
    return $verbiage;
}

function GetDirtAndBloodContext($localActors) {
    $utilities = new Utilities();
    if (!$utilities->IsModEnabled("DirtAndBlood")) {
        return "";
    }

    // lists of people
    $clean = [];
    $dirt1 = [];
    $dirt2 = [];
    $dirt3 = [];
    $dirt4 = [];
    $blood1 = [];
    $blood2 = [];
    $blood3 = [];
    $blood4 = [];
    $bathing = [];
    $professional = [];
    $bandit = [];

    // more soaps add on
    $lavender = [];
    $blue = [];
    $dragonsTongue = [];
    $purple = [];
    $red = [];
    $superior = [];
    
    $actorList = is_array($localActors) ? $localActors : explode("|", $localActors);
    $actorList[] = $GLOBALS["PLAYER_NAME"]; 
    foreach($actorList as $name) {
        $name = str_replace("(", "", $name);
        if($name == "") continue;
        $listOfTags = $utilities->GetActorValue($name, "dirtAndBlood");
        
        // Check for primary states first
        $hasPrimaryState = dnb::includes("Clean", $listOfTags) || 
                          dnb::includes("Dirt1", $listOfTags) || 
                          dnb::includes("Dirt2", $listOfTags) || 
                          dnb::includes("Dirt3", $listOfTags) || 
                          dnb::includes("Dirt4", $listOfTags) || 
                          dnb::includes("Blood1", $listOfTags) || 
                          dnb::includes("Blood2", $listOfTags) || 
                          dnb::includes("Blood3", $listOfTags) || 
                          dnb::includes("Blood4", $listOfTags) || 
                          dnb::includes("Bathing", $listOfTags);
        
        // Add to primary state arrays if applicable
        if(dnb::includes("Clean", $listOfTags)) $clean[] = $name;
        if(dnb::includes("Dirt1", $listOfTags)) $dirt1[] = $name;
        if(dnb::includes("Dirt2", $listOfTags)) $dirt2[] = $name;
        if(dnb::includes("Dirt3", $listOfTags)) $dirt3[] = $name;
        if(dnb::includes("Dirt4", $listOfTags)) $dirt4[] = $name;
        if(dnb::includes("Blood1", $listOfTags)) $blood1[] = $name;
        if(dnb::includes("Blood2", $listOfTags)) $blood2[] = $name;
        if(dnb::includes("Blood3", $listOfTags)) $blood3[] = $name;
        if(dnb::includes("Blood4", $listOfTags)) $blood4[] = $name;
        if(dnb::includes("Bathing", $listOfTags)) $bathing[] = $name;
        
        // Only add to fallback arrays if no primary state is present
        if(!$hasPrimaryState) {
            // if(dnb::includes("Professional", $listOfTags)) $professional[] = $name;
            // Only add to bandit if not already in professional
            // else if(dnb::includes("Bandit", $listOfTags)) $bandit[] = $name;
        }
        
        // Scent tags can always apply
        if(dnb::includes("Lavender", $listOfTags)) $lavender[] = $name;
        if(dnb::includes("Blue", $listOfTags)) $blue[] = $name;
        if(dnb::includes("DragonsTongue", $listOfTags)) $dragonsTongue[] = $name;
        if(dnb::includes("Red", $listOfTags)) $red[] = $name;
        if(dnb::includes("Purple", $listOfTags)) $purple[] = $name;
        if(dnb::includes("Superior", $listOfTags)) $superior[] = $name;
    }

    // build lists
    $verbiage = "";
    if(!empty($clean)) {
        $verbiage .= dnb::rollUpAList($clean) . dnb::getStateDescription("Clean") . "\n";
    }
    if(!empty($dirt1)) {
        $verbiage .= dnb::rollUpAList($dirt1) . dnb::getStateDescription("Dirt1") . "\n";
    }
    if(!empty($dirt2)) {
        $verbiage .= dnb::rollUpAList($dirt2) . dnb::getStateDescription("Dirt2") . "\n";
    }
    if(!empty($dirt3)) {
        $verbiage .= dnb::rollUpAList($dirt3) . dnb::getStateDescription("Dirt3") . "\n";
    }
    if(!empty($dirt4)) {
        $verbiage .= dnb::rollUpAList($dirt4) . dnb::getStateDescription("Dirt4") . "\n";
    }
    if(!empty($blood1)) {
        $verbiage .= dnb::rollUpAList($blood1) . dnb::getStateDescription("Blood1") . "\n";
    }
    if(!empty($blood2)) {
        $verbiage .= dnb::rollUpAList($blood2) . dnb::getStateDescription("Blood2") . "\n";
    }
    if(!empty($blood3)) {
        $verbiage .= dnb::rollUpAList($blood3) . dnb::getStateDescription("Blood3") . "\n";
    }
    if(!empty($blood4)) {
        $verbiage .= dnb::rollUpAList($blood4) . dnb::getStateDescription("Blood4") . "\n";
    }
    if(!empty($bathing)) {
        $verbiage .= dnb::rollUpAList($bathing) . dnb::getStateDescription("Bathing") . "\n";
    }
    if(!empty($professional)) {
        $verbiage .= dnb::rollUpAList($professional) . "shows signs of " . dnb::getStateDescription("Professional") . "\n";
    }
    if(!empty($bandit)) {
        $verbiage .= dnb::rollUpAList($bandit) . dnb::getStateDescription("Bandit") . "\n";
    }
    if(!empty($lavender)) {
        $verbiage .= dnb::rollUpAList($lavender, true) . dnb::getStateDescription("Lavender", true) . "\n";
    }
    if(!empty($blue)) {
        $verbiage .= dnb::rollUpAList($blue, true) . dnb::getStateDescription("Blue", true) . "\n";
    }
    if(!empty($red)) {
        $verbiage .= dnb::rollUpAList($red, true) . dnb::getStateDescription("Red", true) . "\n";
    }
    if(!empty($dragonsTongue)) {
        $verbiage .= dnb::rollUpAList($dragonsTongue, true) . dnb::getStateDescription("DragonsTongue", true) . "\n";
    }
    if(!empty($purple)) {
        $verbiage .= dnb::rollUpAList($purple, true) . dnb::getStateDescription("Purple", true) . "\n";
    }
    if(!empty($superior)) {
        $verbiage .= dnb::rollUpAList($superior, true) . dnb::getStateDescription("Superior", true) . "\n";
    }

    if(!$verbiage) {
        return "";
    }
    return "\n". $verbiage;
}


