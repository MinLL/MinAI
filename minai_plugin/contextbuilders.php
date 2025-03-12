<?php
require_once("config.php");
require_once("util.php");
require_once("deviousfollower.php");
require_once("wornequipment.php");
require_once("customintegrations.php");
require_once("weather.php");
require_once("reputation.php");
require_once("relationship.php");
require_once("submissivelola.php");
require_once("dirtandblood.php");
require_once("exposure.php");
require_once("fertilitymode.php");

Function BuildContext($name) {
  if ($name == "The Narrator") {
      // The narrator is always talking to the player, don't need to do this
      //return BuildContext($GLOBALS["PLAYER_NAME"]);
      return "";
  }
  $context = "";
  $context .= GetPhysicalDescription($name);
  $context .= GetClothingContext($name);
  $context .= GetDDContext($name);
  $context .= GetArousalContext($name);
  $context .= GetFollowingContext($name);
  $context .= GetFertilityContext($name);
  if (!isset($GLOBALS["HERIKA_TARGET"])) {
      $context .= GetDeviousFollowerContext($name);
      $context .= GetSubmissiveLolaContext($name);
  }
  $context .= GetSurvivalContext($name);

  // Add mind influence context for the narrator only
  if ($name == "The Narrator") {
      $mindState = GetMindInfluenceState($name);
      if ($mindState != "normal") {
          $context .= GetMindInfluenceContext($mindState) . "\n";
      }
  }

  return $context;
}

Function GetSurvivalContext($name) {
    $ret = "";
    // If it's a follower, use the player's needs instead
    // if/when we add support for ineed, we'll need to handle that differently
    if (IsFollower($name)) {
        $name = $GLOBALS["PLAYER_NAME"];
    }
    
    if ((!IsModEnabled("Sunhelm") && !IsModEnabled("SurvivalMode"))) {
        return $ret;
    }
    
    $hunger = floatval(GetActorValue($name, "hunger"));
    $thirst = floatval(GetActorValue($name, "thirst")); 
    $fatigue = floatval(GetActorValue($name, "fatigue"));
    $cold = floatval(GetActorValue($name, "cold"));

    $ret .= "{$name}'s hunger level is at {$hunger}%, where 0 is not hungry at all, and 100 is starving. ";

    if (IsModEnabled("Sunhelm")) {
        $ret .= "{$name}'s thirst level is at {$thirst}%, where 0 is not thirsty at all, and 100 is dying of thirst. ";
    }

    $ret .= "{$name}'s fatigue level is at {$fatigue}%, where 0 is not tired at all, and 100 is exhausted. ";

    if (IsModEnabled("SurvivalMode")) {
        $ret .= "{$name}'s cold level is at {$cold}%, where 0 is not cold at all, and 100 is freezing to death. ";
    }

    if ($ret != "")
        $ret .= "\n";
    return $ret;
}

Function GetArousalContext($name) {
  $ret = "";
  $arousal = GetActorValue($name, "arousal");
  if ($arousal != "" && (IsModEnabled("OSL") || IsModEnabled("Aroused"))) {
      $ret .= "{$name}'s sexual arousal level is {$arousal}/100, where 0 is not aroused at all, and 100 is desperate for sex.";
  }
  if ($ret != "")
        $ret .= "\n";
  return $ret;
}

Function GetPhysicalDescription($name) {
  $gender = GetActorValue($name, "gender");
  $race = GetActorValue($name, "race");
  $beautyScore = GetActorValue($name, "beautyScore");
  $breastsScore = GetActorValue($name, "breastsScore");
  $buttScore = GetActorValue($name, "buttScore");
  $isexposed = GetActorValue($name, "isexposed");
  $ret = "";
  $isWerewolf = false;
  if ($gender != "" && $race != "") {
    $ret .= "{$name} is a {$gender} {$race}. ";
    if ($race == "werewolf") {
        $isWerewolf = true;
        $ret .= "{$name} is currently transformed into a terrifying werewolf! ";
    }
  }
  if (!IsPlayer($name)) {
    return $ret;
  }
  if (!empty($beautyScore) && $beautyScore != "0" && !$isWerewolf) {
    $beautyScore = ceil(intval($beautyScore)/10);
    $ret .= "She is a {$beautyScore}/10 in terms of beauty ";
  }
  if((!empty($breastsScore) && $breastsScore != "0") && (!empty($buttScore) && $buttScore != "0") && !$isWerewolf) {
      $breastsScore = ceil(intval($breastsScore)/10);
      $buttScore = ceil(intval($buttScore)/10);
      $ret .= "with {$breastsScore}/10 tits and a {$buttScore}/10 ass. ";
  }
  if (IsEnabled($name, "isexposed")) {
    $ret  .= GetPenisSize($name);
  }
  if ($ret != "")
      $ret .= "\n";
  return $ret;
}

Function GetPenisSize($name) {
    $tngsize = GetActorValue($name, "tngsize");
    $sizeDescription = "";
    if (HasKeyword($name, "TNG_XL") || ($tngsize == 4)) {
        $sizeDescription = "one of the biggest cocks you've ever seen";
    }
    elseif(HasKeyword($name, "TNG_L") || ($tngsize == 3)) {
        $sizeDescription = "a large cock";
    }
    elseif (HasKeyword($name, "TNG_M") || HasKeyword($name, "TNG_DefaultSize") || ($tngsize == 2)) {
        $sizeDescription = "an average sized cock";
    }
    elseif (HasKeyword($name, "TNG_S") || ($tngsize == 1)) {
        $sizeDescription = "a very small cock";
    }        
    elseif (HasKeyword($name, "TNG_XS") || ($tngsize == 0)) {
        $sizeDescription = "an embarrassingly tiny prick";
    }
    if ($sizeDescription != "") {
        return "{$name} has {$sizeDescription}. ";
    }
    return "";
}

Function GetFollowingContext($name) {
  if (IsFollowing($name)) {
    return "{$name} is following, walking, or escorting ".$GLOBALS["PLAYER_NAME"];
  } else {
    return "";
  }
}

Function HasKeywordAndNotSkip($name, $eqContext, $keyword) {
  return HasKeyword($name, $keyword) && !IsSkipKeyword($keyword, $eqContext["skipKeywords"]);
}

Function GetClothingContext($name, $forceNarrator = false) {
  $cuirass = GetActorValue($name, "cuirass", false, true);
  $ret = "";
  
  $eqContext = GetAllEquipmentContext($name);

  $tmp = GetRevealedStatus($name);
  $wearingBottom = $tmp["wearingBottom"];
  $wearingTop = $tmp["wearingTop"];
  $isNarrator = $forceNarrator || ($GLOBALS["HERIKA_NAME"] == "The Narrator");
  
  // if $eqContext["context"] not empty, then will set ret
  if (!empty($eqContext["context"])) {
    $ret .= "{$name} is wearing {$eqContext["context"]}\n";
  } elseif (IsEnabled($name, "isNaked") && !$wearingTop && !$wearingBottom) {
    $ret .= "{$name} is naked and exposed.\n";
  } elseif (!empty($cuirass)) {
    $ret .= "{$name} is wearing {$cuirass}.\n";
  }

  // Only show detailed clothing info if narrator or the area is revealed
  if ($isNarrator || !$wearingTop) {
    $concealedPrefix = !empty($cuirass) ? "Concealed by {$cuirass}, " : "";
    
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_Brabikini")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a bra underneath her other equipment.\n";
    }
  }

  if ($isNarrator || !$wearingBottom) {
    $concealedPrefix = !empty($cuirass) ? "Concealed by {$cuirass}, " : "";
    
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_Thong")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a thong underneath her other equipment.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PantiesNormal")) {
      $ret .= "{$concealedPrefix}{$name} is wearing plain panties underneath her other equipment.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PantsNormal")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a set of ordinary pants.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PelvicCurtain")) {
      $ret .= "{$concealedPrefix}{$name}'s pussy is covered only by a sheer curtain of fabric.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_FullSkirt")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a full length skirt that goes down to her knees.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_MiniSkirt")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a short mini-skirt that barely covers her ass. Her underwear or panties are sometimes visible underneath when she moves.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_MicroHotPants")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a set of short hot-pants that accentuate her ass.\n";
    }
  }

  // Full body outfits only show if narrator or both top and bottom are revealed
  if ($isNarrator || (!$wearingTop && !$wearingBottom)) {
    $concealedPrefix = !empty($cuirass) ? "Concealed by {$cuirass}, " : "";

    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorHarness")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a form-fitting body harness.\n";
    }
  }

  // Always visible clothing
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_HalfNakedBikini")) {
    $ret .= "{$concealedPrefix}{$name} is wearing a set of revealing bikini armor.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorHalfNaked")) {
    $ret .= "{$concealedPrefix}{$name} is wearing very revealing attire, leaving them half naked.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "EroticArmor")) {
    // $ret .= "{$concealedPrefix}{$name} is wearing a sexy revealing outfit.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorSpendex")) {
    $ret .= "{$concealedPrefix}{$name}'s outfit is made out of latex (Referred to as Ebonite).\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorTransparent")) {
    $ret .= "{$concealedPrefix}{$name}'s outfit is transparent, leaving nothing to the imagination.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorLewdLeotard")) {
    $ret .= "{$concealedPrefix}{$name} is wearing a sheer, revealing leotard leaving very little to the imagination.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorRubber")) {
    $ret .= "{$concealedPrefix}{$name}'s outfit is made out of tight form-fitting rubber (Referred to as Ebonite).\n";
  }

  // Always visible accessories
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_Heels")) {
    $ret .= "{$name} is wearing a set of high-heels.\n";
  }

  // Rest of the piercings code remains unchanged...
  if (!$wearingBottom && HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingVulva")) {
    $ret .= "{$name} has labia piercings.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingBelly")) {
      $ret .= "{$name} has a navel piercing.\n";
  }
  if (!$wearingTop) {
      if (HasKeyword($name, "zad_DeviousPiercingsNipple")) {
          $ret .= "{$name} is wearing remotely controlled nipple piercings capable of powerful vibration.\n";
      }
      elseif  (HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingNipple")) {
          $ret .= "{$name} has nipple piercings.\n";
      }
  }
  if (!$wearingBottom) {
      if (HasKeyword($name, "zad_DeviousPiercingsVaginal")) {
          $ret .= "{$name} is wearing a remotely controlled clitoral ring capable of powerful vibration.\n";
      }
      elseif (HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingClit")) {
          $ret .= "{$name} has a clit piercing.\n";
      }
  }
  if ($ret != "")
      $ret .= "\n";
  return $ret;
}


Function GetDDContext($name, $forceNarrator = false) {
  $ret = "";
  $tmp = GetRevealedStatus($name);
  $wearingBottom = $tmp["wearingBottom"];
  $wearingTop = $tmp["wearingTop"];
  $isNarrator = $forceNarrator || ($GLOBALS["HERIKA_NAME"] == "The Narrator");
  $cuirass = GetActorValue($name, "cuirass", false, true);
  $concealedPrefix = !empty($cuirass) ? "Concealed by {$cuirass}, " : "";

  // Items that require bottom area to be visible
  if ($isNarrator || !$wearingBottom) {
    if (HasKeyword($name, "zad_DeviousPlugVaginal")) {
      $ret .= "{$concealedPrefix}{$name} has a remotely controlled plug in her pussy capable of powerful vibrations.\n";
    }
    if (HasKeyword($name, "zad_DeviousPlugAnal")) {
      $ret .= "{$concealedPrefix}{$name} has a remotely controlled plug in her ass capable of powerful vibrations.\n";
    }
    if (HasKeyword($name, "zad_DeviousBelt")) {
      $ret .= "{$concealedPrefix}{$name}'s pussy is locked away by a chastity belt, preventing her from touching it or having sex.\n";
    }
  }

  // Items that require top area to be visible
  if ($isNarrator || !$wearingTop) {
    if (HasKeyword($name, "zad_DeviousBra")) {
      $ret .= "{$concealedPrefix}{$name}'s breasts are locked away in a chastity bra.\n";
    }
  }

  // Always visible items (or items that show even with clothing)
  if (HasKeyword($name, "zad_DeviousCollar")) {
    $ret .= "{$name} is wearing a collar marking her as someone's property.\n";
  }
  if (HasKeyword($name, "zad_DeviousArmCuffs")) {
    $ret .= "{$name} is wearing an arm cuff on each arm.\n";
  }
  if (HasKeyword($name, "zad_DeviousLegCuffs")) {
    $ret .= "{$name} is wearing a leg cuff on each leg.\n";
  }

  // Full body restraints - only show if narrator or fully revealed
  if ($isNarrator || (!$wearingTop && !$wearingBottom)) {
    if (HasKeyword($name, "zad_DeviousArmbinder")) {
      $ret .= "{$concealedPrefix}{$name}'s hands are secured behind her back by an armbinder, leaving her helpless.\n";
    }
    if (HasKeyword($name, "zad_DeviousYoke")) {
      $ret .= "{$concealedPrefix}{$name}'s hands and neck are locked in an uncomfortable yoke, leaving her helpless.\n";
    }
    if (HasKeyword($name, "zad_DeviousElbowTie")) {
      $ret .= "{$concealedPrefix}{$name}'s arms are tied behind her back in-a strict elbow tie, leaving her helpless.\n";
    }
    if (HasKeyword($name, "zad_DeviousPetSuit")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a full-body suit made out of shiny latex (Referred to as Ebonite) leaving nothing to the imagination.\n";
    }
    if (HasKeyword($name, "zad_DeviousStraitJacket")) {
      $ret .= "{$concealedPrefix}{$name}'s arms are secured by a strait jacket, leaving her helpless.\n";
    }
    if (HasKeyword($name, "zad_DeviousCorset")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a corset around her waist.\n";
    }
    if (HasKeyword($name, "zad_DeviousHobbleSkirt")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a confining hobble-skirt that is restricting her movements.\n";
    }
    if (HasKeyword($name, "zad_DeviousGloves")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a a pair of locking gloves.\n";
    }
    if (HasKeyword($name, "zad_DeviousSuit")) {
      $ret .= "{$concealedPrefix}{$name} is wearing skin tight body-suit.\n";
    }
    if (HasKeyword($name, "zad_DeviousHarness")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a form-fitting leather harness.\n";
    }
  }

  // Always visible head items
  if (HasKeyword($name, "zad_DeviousHood")) {
    $ret .= "{$name} is wearing a hood over her head.\n";
  }
  if (HasKeyword($name, "zad_DeviousGag")) {
    $ret .= "{$name} is gagged and is drooling.\n";
  }
  if (HasKeyword($name, "zad_DeviousGagPanel")) {
    $ret .= "{$name} is gagged with a panel-gag that leaves her tongue exposed and is unable to close their mouth.\n";
  }
  if (HasKeyword($name, "zad_DeviousGagLarge")) {
    $ret .= "{$name} is gagged with a large gag and cannot speak clearly.\n";
  }
  if (HasKeyword($name, "zad_DeviousBlindfold")) {
    $ret .= "{$name} is blindfolded and cannot see where she is going.\n";
  }
  if (HasKeyword($name, "zad_DeviousAnkleShackles")) {
    $ret .= "{$name} is wearing a set of ankle shackles, restricting her ability to move quickly.\n";
  }

  // Items requiring exposed chest
  if ($isNarrator || !$wearingTop) {
    if (HasKeyword($name, "zad_DeviousClamps")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a set of painful nipple clamps.\n";
    }
  }

  // Vibration status - only show if relevant areas are exposed
  if (CanVibrate($name) && ($isNarrator || (!$wearingTop || !$wearingBottom))) {
    if (IsInFaction($name, "Vibrator Effect Faction")) {
      $ret .= "{$concealedPrefix}{$name}'s vibrator is currently on, and is actively stimulating her.\n";
    } else {
      $ret .= "{$concealedPrefix}{$name}'s vibrator is currently off.\n";
    }
  }

  if ($ret != "")
    $ret .= "\n";
  return $ret;
}