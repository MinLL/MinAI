<?php

require_once("util.php");
// Name of the function. This is what will be offered to LLM. Can be overwrited by LANG. 

$target = $GLOBALS["target"];

$GLOBALS["F_NAMES"]["ExtCmdSpankAss"]="SpankAss";
$GLOBALS["F_NAMES"]["ExtCmdSpankTits"]="SpankTits";
$GLOBALS["F_NAMES"]["ExtCmdGrope"]="Grope";
$GLOBALS["F_NAMES"]["ExtCmdPinchNipples"]="PinchNipples";


// Description. This is what will be offered to LLM. Can be overwrited by LANG.

$GLOBALS["F_TRANSLATIONS"]["ExtCmdSpankAss"]="Spank the targets ass";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdSpankTits"]="Spank the targets tits";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdGrope"]="Grope the target";
$GLOBALS["F_TRANSLATIONS"]["ExtCmdPinchNipples"]="Pinch the targets nipples";

// $FUNCTION_PARM_INSPECT will contain an enum of visible NPC
// $FUNCTION_PARM_MOVETO will contain an enum of visible places to move 

// Function definition (OpenaAI style)

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdSpankAss"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdSpankAss"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target NPC, Actor, or being",
                    "enum" => [$target]

                ]
            ],
            "required" => ["target"],
        ],
    ];

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdSpankTits"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdSpankTits"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target NPC, Actor, or being",
                    "enum" => [$target]

                ]
            ],
            "required" => ["target"],
        ],
    ];

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdGrope"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdGrope"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target NPC, Actor, or being",
                    "enum" => [$target]
                ]
            ],
            "required" => ["target"],
        ],
    ];

$GLOBALS["FUNCTIONS"][] = [
        "name" => $GLOBALS["F_NAMES"]["ExtCmdPinchNipples"],
        "description" => $GLOBALS["F_TRANSLATIONS"]["ExtCmdPinchNipples"],
        "parameters" => [
            "type" => "object",
            "properties" => [
                "target" => [
                    "type" => "string",
                    "description" => "Target NPC, Actor, or being",
                    "enum" => [$target]
                ]
            ],
            "required" => ["target"],
        ],
    ];






// Add this function to enabled array
if (IsModEnabled("DeviousFollowers") && IsModEnabled("STA")) {
    RegisterAction("ExtCmdSpankAss");
    RegisterAction("ExtCmdSpankTits");
}

if (IsModEnabled("Sexlab") || IsModEnabled("Ostim")) {
    RegisterAction("ExtCmdGrope");
    RegisterAction("ExtCmdPinchNipples");
}

// From here, is stuff that will be needed once the papyrus plugin make a request of type funcret.

// Stuff to manage the return value of the call function.
// Custom prompt. This will overwrite default cue. This is what we are requesting the LLM to do.
// TEMPLATE_DIALOG is degined in global prompts.php.

$target = GetTargetActor();
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdSpankAss"]="{$GLOBALS["HERIKA_NAME"]} comments on spanking {$target}'s ass. {$GLOBALS["TEMPLATE_DIALOG"]}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdSpankTits"]="{$GLOBALS["HERIKA_NAME"]} comments on spanking {$target}'s tits. {$GLOBALS["TEMPLATE_DIALOG"]}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdGrope"]="{$GLOBALS["HERIKA_NAME"]} comments on groping {$target}. {$GLOBALS["TEMPLATE_DIALOG"]}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdPinchNipples"]="{$GLOBALS["HERIKA_NAME"]} comments on pinching {$target}'s nipples. {$GLOBALS["TEMPLATE_DIALOG"]}";


// If function is a server function (we need to calculate the result value in web server using php code)
// add a callable to FUNCSERV array
// We should execute our code here.
// This example does not require to do anything.

$GLOBALS["FUNCSERV"]["ExtCmdSpankAss"]=function() {
    global $gameRequest,$returnFunction,$db,$request;
    // Probably we want to execute something, and put return value in $returnFunction[3] and $gameRequest[3];
    // We could overwrite also $request. 
    
    
    
};

// When preparing function return data to LLM, maybe we will need to alter request. return array should only contain argName,request,useFunctionsAgain
// argName is mandatory, is the name of the parameter this function uses
// request is optional, if we need to rewrite request to LLM 
// useFunctionsAgain is optional, if we need to expose functions again to LLM

$GLOBALS["FUNCRET"]["ExtCmdSpankAss"]=function($gameRequest) {
    // Example, if papyrus execution gives some error, we will need to rewrite request her.
    // BY default, request will be $GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdSpankAss"]
    // $gameRequest = [type of message,localts,gamets,data]
    $GLOBALS["FORCE_MAX_TOKENS"]=48;    // We can overwrite anything here using $GLOBALS;
   
    if (stripos($gameRequest[3],"error")!==false) // Papyrus returned error
        return ["argName"=>"target","request"=>"{$GLOBALS["HERIKA_NAME"]} says sorry about unable to spank the player. {$GLOBALS["TEMPLATE_DIALOG"]}"];
    else
        return ["argName"=>"target"];
    
};

$GLOBALS["FUNCRET"]["ExtCmdSpankTits"]=$GLOBALS["GenericFuncRet"];
$GLOBALS["FUNCRET"]["ExtCmdGrope"]=$GLOBALS["GenericFuncRet"];
$GLOBALS["FUNCRET"]["ExtCmdPinchNipples"]=$GLOBALS["GenericFuncRet"];


?>
