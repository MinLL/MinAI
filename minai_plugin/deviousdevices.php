<?php

require_once("util.php");

$canVibrate = CanVibrate($GLOBALS["PLAYER_NAME"]);

  
$GLOBALS["F_NAMES"]["ExtCmdShock"]="Shock";
$GLOBALS["F_NAMES"]["ExtCmdForceOrgasm"]="ForceOrgasm";
$GLOBALS["F_NAMES"]["ExtCmdStopStimulation"]="StopStimulation";

$GLOBALS["F_TRANSLATIONS"]["ExtCmdShock"]="Shock the target in order to punish them or reduce their arousal";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdForceOrgasm"]="Make the target immediately have an orgasm";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdStopStimulation"]="Turn off the targets vibrator";



$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdShock"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdShock"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target NPC, Actor, or being",
                    "enum" => $GLOBALS["FUNCTION_PARM_INSPECT"]
                ]
            ],
            "required" => [],
        ],
    ];



$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdForceOrgasm"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdForceOrgasm"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target NPC, Actor, or being",
                    "enum" => $GLOBALS["FUNCTION_PARM_INSPECT"]
                ]
            ],
            "required" => [],
        ],
    ];

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdStopStimulation"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdStopStimulation"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target NPC, Actor, or being",
                    "enum" => $GLOBALS["FUNCTION_PARM_INSPECT"]
                ]
            ],
            "required" => [],
        ],
    ];






if ($canVibrate) {
  $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdShock";
  $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdForceOrgasm";
  $GLOBALS["ENABLED_FUNCTIONS"][]="ExtCmdStopStimulation";
 }


$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdShock"]="{$GLOBALS["HERIKA_NAME"]} comments on remotely shocking {$GLOBALS["PLAYER_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdForceOrgasm"]="{$GLOBALS["HERIKA_NAME"]} comments on remotely forcing {$GLOBALS["PLAYER_NAME"]} to have an orgasm. {$GLOBALS["TEMPLATE_DIALOG"]}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdStopStimulation"]="{$GLOBALS["HERIKA_NAME"]} comments on turning off {$GLOBALS["PLAYER_NAME"]}'s vibrator. {$GLOBALS["TEMPLATE_DIALOG"]}";



$GLOBALS["FUNCRET"]["ExtCmdShock"]=$GLOBALS["FUNCRET"]["ExtCmdSpankAss"];
$GLOBALS["FUNCRET"]["ExtCmdForceOrgasm"]=$GLOBALS["FUNCRET"]["ExtCmdSpankAss"];
$GLOBALS["FUNCRET"]["ExtCmdStopStimulation"]=$GLOBALS["FUNCRET"]["ExtCmdSpankAss"];




$vibSettings = array ("Very Weak", "Weak", "Medium", "Strong", "Very Strong");

// Temporary ugly hack until I can figure out why parameters aren't working. Mantella feature parity
foreach ($vibSettings as $strength) {
  $keyword = "ExtCmdTeaseWithVibrator" . str_replace(' ', '', $strength);
  $name = "TeaseWithVibrator" . str_replace(' ', '', $strength);
  
  $GLOBALS["F_NAMES"][$keyword]=$name;
  $GLOBALS["F_TRANSLATIONS"][$keyword]="Remotely tease the target with a vibrator ($strength intensity) without letting them orgasm";

  $GLOBALS["FUNCTIONS"][] = [
			     "name" => $GLOBALS["F_NAMES"]["$keyword"],
			     "description" => $GLOBALS["F_TRANSLATIONS"]["$keyword"],
			     "parameters" => [
					      "type" => "object",
					      "properties" => [
							       "intensity" => [
									       "type" => "integer",
									       "description" => "Strength of the vibration",
									       "enum" => ["weak", "moderate", "strong"]

									       ]
		
							       ],
					      "required" => [],
					      ],
			     ];
  if ($canVibrate) {
    $GLOBALS["ENABLED_FUNCTIONS"][]="$keyword";
  }
  $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["$keyword"]="{$GLOBALS["HERIKA_NAME"]} comments on remotely teasing {$GLOBALS["PLAYER_NAME"]} with a $strength vibration. {$GLOBALS["TEMPLATE_DIALOG"]}";
}

foreach ($vibSettings as $strength) {
  $keyword = "ExtCmdStimulateWithVibrator" . str_replace(' ', '', $strength);
  $name = "StimulateWithVibrator" . str_replace(' ', '', $strength);
  
  $GLOBALS["F_NAMES"][$keyword]=$name;
  $GLOBALS["F_TRANSLATIONS"][$keyword]="Remotely stimulate the target with a vibrator ($strength intensity) while potentially letting them orgasm";

  $GLOBALS["FUNCTIONS"][] = [
			     "name" => $GLOBALS["F_NAMES"]["$keyword"],
			     "description" => $GLOBALS["F_TRANSLATIONS"]["$keyword"],
			     "parameters" => [
					      "type" => "object",
					      "properties" => [
							       "intensity" => [
									       "type" => "integer",
									       "description" => "Strength of the vibration",
									       "enum" => ["weak", "moderate", "strong"]

									       ]
		
							       ],
					      "required" => [],
					      ],
			     ];
  if ($canVibrate) {
    $GLOBALS["ENABLED_FUNCTIONS"][]="$keyword";
  }
  $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["$keyword"]="{$GLOBALS["HERIKA_NAME"]} comments on remotely stimulating {$GLOBALS["PLAYER_NAME"]} with a $strength vibration. {$GLOBALS["TEMPLATE_DIALOG"]}";
}


?>
