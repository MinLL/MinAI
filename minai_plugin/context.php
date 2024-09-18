<?php
require_once("util.php");
require_once("deviousfollower.php");

Function BuildContext($name) {
  if ($name == "The Narrator") {
    return "";
  }
 return "\n" . GetPhysicalDescription($name) . "\n" . GetClothingContext($name) . "\n" . GetDDContext($name) . "\n" . GetArousalContext($name) . "\n" . GetDeviousFollowerContext($name) . "\n";
}

Function GetArousalContext($name) {
  $ret = "";
  $arousal = GetActorValue($name, "arousal");
  if ($arousal != "") {
    $ret .= "{$name}'s sexual arousal is {$arousal} percent.";
  }
  return $ret;
}

Function GetPhysicalDescription($name) {
  $gender = GetActorValue($name, "gender");
  $race = GetActorValue($name, "race");
  $beautyScore = GetActorValue($name, "beautyScore");
  $breastsScore = GetActorValue($name, "breastsScore");
  $buttScore = GetActorValue($name, "buttScore");
  $ret = "";
  if ($gender != "" && $race != "") {
    $ret .= "{$name} is a {$gender} {$race}. ";
  }
  if (!IsPlayer($name)) {
    return $ret;
  }
  if($beautyScore != "") {
    $beautyScore = ceil(intval($beautyScore)/10);
    $ret .= "Her overall beauty is a {$beautyScore} out of 10. ";
  }
  if($breastsScore != "") {
    $ret .= "The sexual attractiveness of her breasts is a {$breastsScore} percent. ";
  }
  if($buttScore != "") {
    $ret .= "The sexual attractiveness of her ass is a {$buttScore} percent. ";
  }
  $ret  .= GetPenisSize($name);
  return $ret;
}

Function GetPenisSize($name) {
    $sizeDescription = "";
    if (HasKeyword($name, "TNG_ActorAddnAuto:05")) {
        $sizeDescription = "one of the biggest cocks you've ever seen";
    }
    elseif(HasKeyword($name, "TNG_ActorAddnAuto:04")) {
        $sizeDescription = "a large cock";
    }
    elseif (HasKeyword($name, "TNG_ActorAddnAuto:03")) {
        $sizeDescription = "an average sized cock";
    }
    elseif (HasKeyword($name, "TNG_ActorAddnAuto:02")) {
        $sizeDescription = "a very small cock";
    }        
    elseif (HasKeyword($name, "TNG_ActorAddnAuto:01")) {
        $sizeDescription = "an embarrassingly tiny prick";
    }
	elseif (HasKeyword($name, "TNG_XL")) {
        $sizeDescription = "one of the biggest cocks you've ever seen";
    }
    elseif(HasKeyword($name, "TNG_L")) {
        $sizeDescription = "a large cock";
    }
    elseif (HasKeyword($name, "TNG_M") || HasKeyword($name, "TNG_DefaultSize")) {
        $sizeDescription = "an average sized cock";
    }
    elseif (HasKeyword($name, "TNG_S")) {
        $sizeDescription = "a very small cock";
    }        
    elseif (HasKeyword($name, "TNG_XS")) {
        $sizeDescription = "an embarrassingly tiny prick";
    }
    if ($sizeDescription != "") {
        return "{$GLOBALS["HERIKA_NAME"]} has {$sizeDescription}. ";
    }
    return "";
}

                            
Function GetClothingContext($name) {
  $ret = "";
  if (HasKeyword($name, "SLA_HalfNakedBikini")) {
    $ret .= "{$name} is wearing a set of revealing bikini armor.\n";
  }
  if (HasKeyword($name, "SLA_ArmorHalfNaked")) {
    $ret .= "{$name} is wearing very revealing attire, leaving them half naked.\n";
  }
  if (HasKeyword($name, "SLA_Brabikini")) {
    $ret .= "{$name} is wearing a bra underneath her other equipment.\n";
  }
  if (HasKeyword($name, "SLA_Thong")) {
    $ret .= "{$name} is wearing a thong underneath her other equipment.\n";
  }
  if (HasKeyword($name, "SLA_PantiesNormal")) {
    $ret .= "{$name} is wearing plain panties underneath her other equipment.\n";
  }
  if (HasKeyword($name, "SLA_Heels")) {
    $ret .= "{$name} is wearing a set of high-heels.\n";
  }
  if (HasKeyword($name, "SLA_PantsNormal")) {
    $ret .= "{$name} is wearing a set of ordinary pants.\n";
  }
  if (HasKeyword($name, "SLA_MicroHotPants")) {
    $ret .= "{$name} is wearing a set of short hot-pants that accentuate her ass.\n";
  }
  if (HasKeyword($name, "SLA_ArmorHarness")) {
    $ret .= "{$name} is wearing a form-fitting body harness.\n";
  }
  if (HasKeyword($name, "SLA_ArmorSpendex")) {
    $ret .= "{$name}'s outfit is made out of latex (Referred to as Ebonite).\n";
  }
  if (HasKeyword($name, "SLA_ArmorTransparent")) {
    $ret .= "{$name}'s outfit is transparent, leaving nothing to the imagination.\n";
  }
  if (HasKeyword($name, "SLA_ArmorLewdLeotard")) {
    $ret .= "{$name} is wearing a sheer, revealing leotard leaving very little to the imagination.\n";
  }
  if (HasKeyword($name, "SLA_PelvicCurtain")) {
    $ret .= "{$name}'s pussy is covered only by a sheer curtain of fabric.\n";
  }
  if (HasKeyword($name, "SLA_FullSkirt")) {
    $ret .= "{$name} is wearing a full length skirt that goes down to her knees.\n";
  }
  if (HasKeyword($name, "SLA_MiniSkirt")) {
    $ret .= "{$name} is wearing a short mini-skirt that barely covers her ass. Her underwear or panties are sometimes visible underneath when she moves.\n";
  }
  if (HasKeyword($name, "SLA_ArmorRubber")) {
    $ret .= "{$name}'s outfit is made out of tight form-fitting rubber (Referred to as Ebonite).\n";
  }
  if (IsEnabled($name, "isNaked")) {
    $ret .= "{$name} is naked and exposed.\n";
  }
  if (HasKeyword($name, "EroticArmor")) {
      $ret .= "{$name} is wearing a sexy revealing outfit.\n";
  }
  if (HasKeyword($name, "SLA_PiercingVulva")) {
      $ret .= "{$name} has labia piercings.\n";
  }
  if (HasKeyword($name, "SLA_PiercingBelly")) {
      $ret .= "{$name} has a navel piercing.\n";
  }
  if (HasKeyword($name, "SLA_PiercingNipple")) {
      $ret .= "{$name} has nipple piercings.\n";
  }
  if (HasKeyword($name, "SLA_PiercingClit")) {
      $ret .= "{$name} has a clit piercing.\n";
  }
  return $ret;
}


Function GetDDContext($name) {
  $ret = "";
  if (HasKeyword($name, "zad_DeviousPlugVaginal")) {
    $ret .= "{$name} has a remotely controlled plug in her pussy capable of powerful vibrations.\n";
  }
  if (HasKeyword($name, "zad_DeviousPlugAnal")) {
    $ret .= "{$name} has a remotely controlled plug in her ass capable of powerful vibrations.\n";
  }
  if (HasKeyword($name, "zad_DeviousBelt")) {
    $ret .= "{$name}'s pussy is locked away by a chastity belt, preventing her from touching it or having sex.\n";
  }
  if (HasKeyword($name, "zad_DeviousCollar")) {
    $ret .= "{$name} is wearing a collar marking her as someone's property.\n";
  }
  if (HasKeyword($name, "zad_DeviousPiercingsNipple")) {
    $ret .= "{$name} is wearing remotely controlled nipple piercings capable of powerful vibration.\n";
  }
  if (HasKeyword($name, "zad_DeviousPiercingsVaginal")) {
    $ret .= "{$name} is wearing a remotely controlled clitoral ring capable of powerful vibration.\n";
  }
  if (HasKeyword($name, "zad_DeviousArmCuffs")) {
    $ret .= "{$name} is wearing an arm cuff on each arm.\n";
  }
  if (HasKeyword($name, "zad_DeviousLegCuffs")) {
    $ret .= "{$name} is wearing a leg cuff on each leg.\n";
  }
  if (HasKeyword($name, "zad_DeviousBra")) {
    $ret .= "{$name}'s breasts are locked away in a chastity bra.\n";
  }
  if (HasKeyword($name, "zad_DeviousArmbinder")) {
    $ret .= "{$name}'s hands are secured behind her back by an armbinder, leaving her helpless.\n";
  }
  if (HasKeyword($name, "zad_DeviousYoke")) {
    $ret .= "{$name}'s hands and neck are locked in an uncomfortable yoke, leaving her helpless.\n";
  }
  if (HasKeyword($name, "zad_DeviousElbowTie")) {
    $ret .= "{$name}'s arms are tied behind her back in-a strict elbow tie, leaving her helpless.\n";
  }
  if (HasKeyword($name, "zad_DeviousPetSuit")) {
    $ret .= "{$name} is wearing a full-body suit made out of shiny latex (Referred to as Ebonite) leaving nothing to the imagination.\n";
  }
  if (HasKeyword($name, "zad_DeviousStraitJacket")) {
    $ret .= "{$name}'s arms are secured by a strait jacket, leaving her helpless.\n";
  }
  if (HasKeyword($name, "zad_DeviousCorset")) {
    $ret .= "{$name} is wearing a corset around her waist.\n";
  }
  if (HasKeyword($name, "zad_DeviousHood")) {
    $ret .= "{$name} is wearing a hood over her head.\n";
  }
  if (HasKeyword($name, "zad_DeviousHobbleSkirt")) {
    $ret .= "{$name} is wearing a confining hobble-skirt that is restricting her movements.\n";
  }
  if (HasKeyword($name, "zad_DeviousGloves")) {
    $ret .= "{$name} is wearing a a pair of locking gloves.\n";
  }
  if (HasKeyword($name, "zad_DeviousSuit")) {
    $ret .= "{$name} is wearing skin tight body-suit.\n";
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
  if (HasKeyword($name, "zad_DeviousHarness")) {
    $ret .= "{$name} is wearing a form-fitting leather harness.\n";
  }
  if (HasKeyword($name, "zad_DeviousBlindfold")) {
    $ret .= "{$name} is blindfolded and cannot see where she is going.\n";
  }
  if (HasKeyword($name, "zad_DeviousAnkleShackles")) {
    $ret .= "{$name} is wearing a set of ankle shackles, restricting her ability to move quickly.\n";
  }
  if (HasKeyword($name, "zad_DeviousClamps")) {
    $ret .= "{$name} is wearing a set of painful nipple clamps.\n";
  }
  return $ret;
}


$GLOBALS["COMMAND_PROMPT"].= BuildContext($GLOBALS["PLAYER_NAME"]);
$GLOBALS["COMMAND_PROMPT"].= BuildContext($GLOBALS["HERIKA_NAME"]);
$GLOBALS["COMMAND_PROMPT"].="

";

// Remove all references to sex scene, and only keep the last one.
$locaLastElement=[];
foreach ($GLOBALS["contextDataFull"] as $n=>$ctxLine) {
    if (strpos($ctxLine["content"],"#SEX_SCENARIO")!==false) {
        $locaLastElement[]=$n;
    }
}
array_pop($locaLastElement);

foreach ($locaLastElement as $n) {
  unset($GLOBALS["contextDataFull"][$n]); 
}

?>
