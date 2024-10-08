<?php

require_once("util.php");

$canVibrate = CanVibrate($GLOBALS["PLAYER_NAME"]);

  
$GLOBALS["F_NAMES"]["ExtCmdShock"]="Shock";
$GLOBALS["F_NAMES"]["ExtCmdForceOrgasm"]="ForceOrgasm";
$GLOBALS["F_NAMES"]["ExtCmdTurnOffVibrator"]="TurnOffVibrator";

$GLOBALS["F_TRANSLATIONS"]["ExtCmdShock"]="Shock the target in order to punish them or reduce their arousal";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdForceOrgasm"]="Make the target immediately have an orgasm";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdTurnOffVibrator"]="Turn off the targets vibrator";



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
        "name" => $GLOBALS["F_NAMES"]["ExtCmdTurnOffVibrator"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdTurnOffVibrator"],
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
    RegisterAction("ExtCmdShock");
    RegisterAction("ExtCmdForceOrgasm");
    RegisterAction("ExtCmdTurnOffVibrator");
 }


$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdShock"]="{$GLOBALS["HERIKA_NAME"]} comments on remotely shocking {$GLOBALS["PLAYER_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdForceOrgasm"]="{$GLOBALS["HERIKA_NAME"]} comments on remotely forcing {$GLOBALS["PLAYER_NAME"]} to have an orgasm. {$GLOBALS["TEMPLATE_DIALOG"]}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdTurnOffVibrator"]="{$GLOBALS["HERIKA_NAME"]} comments on turning off {$GLOBALS["PLAYER_NAME"]}'s vibrator. {$GLOBALS["TEMPLATE_DIALOG"]}";



$GLOBALS["FUNCRET"]["ExtCmdShock"]=$GLOBALS["GenericFuncRet"];
$GLOBALS["FUNCRET"]["ExtCmdForceOrgasm"]=$GLOBALS["GenericFuncRet"];
$GLOBALS["FUNCRET"]["ExtCmdTurnOffVibrator"]=$GLOBALS["GenericFuncRet"];




$vibSettings = Array ("Very Weak", "Weak", "Medium", "Strong", "Very Strong");

// Temporary ugly hack until I can figure out why parameters aren't working. Mantella feature parity
foreach ($vibSettings as $strength) {
  $keyword = "ExtCmdTeaseWithVibrator" . str_replace(' ', '', $strength);
  $name = "TeaseWithVibrator" . str_replace(' ', '', $strength);
  
  $GLOBALS["F_NAMES"][$keyword]=$name;
  $GLOBALS["F_TRANSLATIONS"][$keyword]="Remotely stimulate the target with a vibrator ($strength intensity) without letting them climax";

  $GLOBALS["FUNCTIONS"][] = [
			     "name" => $GLOBALS["F_NAMES"]["$keyword"],
			     "description" => $GLOBALS["F_TRANSLATIONS"]["$keyword"],
			     "parameters" => [
					      "type" => "object",
					      "properties" => [
							       "intensity" => [
									       "type" => "string",
									       "description" => "Strength of the vibration",
									       "enum" => ["Very Weak", "Weak", "Medium", "Strong", "Very Strong"]

									       ]
		
							       ],
					      "required" => [],
					      ],
			     ];
  if ($canVibrate) {
      $GLOBALS["ENABLED_FUNCTIONS"][]=$keyword;
  }
  $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["$keyword"]="{$GLOBALS["HERIKA_NAME"]} comments on remotely teasing {$GLOBALS["PLAYER_NAME"]} with a $strength vibration. {$GLOBALS["TEMPLATE_DIALOG"]}";
}

foreach ($vibSettings as $strength) {
  $keyword = "ExtCmdStimulateWithVibrator" . str_replace(' ', '', $strength);
  $name = "StimulateWithVibrator" . str_replace(' ', '', $strength);
  
  $GLOBALS["F_NAMES"][$keyword]=$name;
  $GLOBALS["F_TRANSLATIONS"][$keyword]="Remotely stimulate the target with a vibrator ($strength intensity)";

  $GLOBALS["FUNCTIONS"][] = [
			     "name" => $GLOBALS["F_NAMES"]["$keyword"],
			     "description" => $GLOBALS["F_TRANSLATIONS"]["$keyword"],
			     "parameters" => [
					      "type" => "object",
					      "properties" => [
							       "intensity" => [
									       "type" => "string",
									       "description" => "Strength of the vibration",
									       "enum" => ["Very Weak", "Weak", "Medium", "Strong", "Very Strong"]
									       ]
		
							       ],
					      "required" => [],
					      ],
			     ];
  if ($canVibrate) {
      $GLOBALS["ENABLED_FUNCTIONS"][]=$keyword;
  }
  $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["$keyword"]="{$GLOBALS["HERIKA_NAME"]} comments on remotely stimulating {$GLOBALS["PLAYER_NAME"]} with a $strength vibration. {$GLOBALS["TEMPLATE_DIALOG"]}";
}





$GLOBALS["F_NAMES"]["ExtCmdEquipCollar"]="EquipCollar";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdEquipCollar"]="Lock a collar on the target";
$GLOBALS["FUNCTIONS"][] = [
    "name" => $GLOBALS["F_NAMES"]["ExtCmdEquipCollar"],
    "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdEquipCollar"],
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

$GLOBALS["F_NAMES"]["ExtCmdUnequipCollar"]="UnequipCollar";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdUnequipCollar"]="Remove a collar from the target";
$GLOBALS["FUNCTIONS"][] = [
    "name" => $GLOBALS["F_NAMES"]["ExtCmdUnequipCollar"],
    "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdUnequipCollar"],
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

$GLOBALS["F_NAMES"]["ExtCmdEquipBinder"]="EquipBinder";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdEquipBinder"]="Lock a Armbinder on the target";
$GLOBALS["FUNCTIONS"][] = [
    "name" => $GLOBALS["F_NAMES"]["ExtCmdEquipBinder"],
    "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdEquipBinder"],
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

$GLOBALS["F_NAMES"]["ExtCmdUnequipBinder"]="UnequipBinder";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdUnequipBinder"]="Remove a Armbinder from the target";
$GLOBALS["FUNCTIONS"][] = [
    "name" => $GLOBALS["F_NAMES"]["ExtCmdUnequipBinder"],
    "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdUnequipBinder"],
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

$GLOBALS["F_NAMES"]["ExtCmdEquipBelt"]="EquipBelt";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdEquipBelt"]="Lock a Chastity Belt on the target";
$GLOBALS["FUNCTIONS"][] = [
    "name" => $GLOBALS["F_NAMES"]["ExtCmdEquipBelt"],
    "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdEquipCollar"],
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

$GLOBALS["F_NAMES"]["ExtCmdUnequipBelt"]="UnequipBelt";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdUnequipBelt"]="Remove a Chastity Belt from the target";
$GLOBALS["FUNCTIONS"][] = [
    "name" => $GLOBALS["F_NAMES"]["ExtCmdUnequipBelt"],
    "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdUnequipCollar"],
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


$GLOBALS["F_NAMES"]["ExtCmdEquipGag"]="EquipGag";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdEquipGag"]="Lock a Gag on the target";
$GLOBALS["FUNCTIONS"][] = [
    "name" => $GLOBALS["F_NAMES"]["ExtCmdEquipGag"],
    "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdEquipGag"],
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
$GLOBALS["F_NAMES"]["ExtCmdUnequipGag"]="UnequipGag";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdUnequipGag"]="Remove a gag from the target";
$GLOBALS["FUNCTIONS"][] = [
    "name" => $GLOBALS["F_NAMES"]["ExtCmdUnequipGag"],
    "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdUnequipGag"],
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
if (IsConfigEnabled("allowDeviceLock")) {
    RegisterAction("ExtCmdEquipCollar");
	RegisterAction("ExtCmdEquipGag");
	RegisterAction("ExtCmdEquipBelt");
	RegisterAction("ExtCmdEquipBinder");
}

if (IsConfigEnabled("allowDeviceUnlock")) {
    RegisterAction("ExtCmdUnequipCollar");
	RegisterAction("ExtCmdUnequipGag");
	RegisterAction("ExtCmdUnequipBelt");
	RegisterAction("ExtCmdUnequipBinder");
}

?>
