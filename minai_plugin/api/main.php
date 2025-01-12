<?php


/* Definitions and main includes */
error_reporting(E_ALL);

define("STOPALL_MAGIC_WORD", "/halt/i");

define("MAXIMUM_SENTENCE_SIZE", 125);
define("MINIMUM_SENTENCE_SIZE", 50);

date_default_timezone_set('Europe/Madrid');

$GLOBALS["AVOID_TTS_CACHE"]=true;

$path = dirname((__FILE__)) . DIRECTORY_SEPARATOR;
require($path . "conf".DIRECTORY_SEPARATOR."conf.php");
require_once($path . "lib" .DIRECTORY_SEPARATOR."auditing.php");
require_once($path . "lib" .DIRECTORY_SEPARATOR."model_dynmodel.php");
require_once($path . "lib" .DIRECTORY_SEPARATOR."{$GLOBALS["DBDRIVER"]}.class.php");
require_once($path . "lib" .DIRECTORY_SEPARATOR."data_functions.php");
require_once($path . "lib" .DIRECTORY_SEPARATOR."chat_helper_functions.php");
require_once($path . "lib" .DIRECTORY_SEPARATOR."memory_helper_vectordb_txtai.php");
requireFilesRecursively($path . "ext".DIRECTORY_SEPARATOR,"globals.php");


// PARSE GET RESPONSE into $gameRequest

if (php_sapi_name()=="cli") {
    // You can run this script directly with php: main.php "Player text"
    $GLOBALS["db"] = new sql();

    $latsRid=$db->fetchAll("select *  from eventlog order by rowid desc LIMIT 1 OFFSET 0");
    $res=$db->fetchAll("select max(gamets)+1 as gamets,max(ts)+1 as ts  from eventlog where rowid={$latsRid[0]["rowid"]}");
    $res[0]["ts"]=$res[0]["ts"]+0;
    $res[0]["gamets"]=$res[0]["ts"]+0;
        
    
        
    $receivedData = "inputtext|{$res[0]["ts"]}|{$res[0]["gamets"]}|{$GLOBALS["PLAYER_NAME"]}: {$argv[1]}";
    //$receivedData = "funcret|{$res[0]["ts"]}|{$res[0]["gamets"]}|command@Inspect@Serana@Serana is wearing: Serana Hood,Elven Dagger,Elder Scroll,Vampire Boots,Vampire Royal Armor,";
    //$receivedData = "{$argv[1]}";
    $_GET["profile"]=$argv[2];
    //error_reporting(E_ERROR);
    $GLOBALS["FUNCTIONS_ARE_ENABLED"]=true;

    unset($GLOBALS["db"]);
} else {

    //$receivedData = base64_decode($_GET["DATA"]);
    //base64 string has '+' chars. THis conflicts with urldecode, so $_GET["DATA"] will get bullshit.
    if (strpos($_SERVER["QUERY_STRING"],"&")===false)
        $receivedData = mb_scrub(base64_decode(substr($_SERVER["QUERY_STRING"],5)));
    else
        $receivedData = mb_scrub(base64_decode(substr($_SERVER["QUERY_STRING"],5,strpos($_SERVER["QUERY_STRING"],"&")-4)));

    //error_log($receivedData." ".$_GET["profile"]);

}




if (!isset($FUNCTIONS_ARE_ENABLED)) {
    $FUNCTIONS_ARE_ENABLED=false;
}



while (@ob_end_clean())	;
ignore_user_abort(true);
set_time_limit(1200);

$momentum=time();

// Array with sentences talked so far
$talkedSoFar = array();

// Array with sentences sent so far
$alreadysent = array();

// Array with parameters to override
$overrideParameters=array();

$ERROR_TRIGGERED=false;


$LAST_ROLE="user";

// SCRIPT LINE QUEUE
$GLOBALS["SCRIPTLINE_EXPRESSION"]="";
$GLOBALS["SCRIPTLINE_LISTENER"]="";
$GLOBALS["SCRIPTLINE_ANIMATION"]="";

$GLOBALS["TTS_FFMPEG_FILTERS"]=[];

/**********************
MAIN FLOW
***********************/

$gameRequest = explode("|", $receivedData);


$startTime = microtime(true);
//error_log("Audit run ID: " . $GLOBALS["AUDIT_RUNID"]. " ({$gameRequest[0]}) started: ".$startTime);
$GLOBALS["AUDIT_RUNID_REQUEST"]=$gameRequest[0];

$gameRequest[0] = strtolower($gameRequest[0]); // Who put 'diary' uppercase?


// Lock to avoid TTS hangs
/*if (($gameRequest[0]!="updateprofile")&&($gameRequest[0]!="diary")&&($gameRequest[0]!="_quest")&&($gameRequest[0]!="setConf")&&($gameRequest[0]!="request")
/*    &&($gameRequest[0]!="addnpc")&&($gameRequest[0]!="_speech")) {
*/

if (in_array($gameRequest[0],["inputtext","inputtext_s","ginputtext","ginputtext_s","instruction","init"])) {
    $GLOBALS["ADD_PLAYER_BIOS"]=true;
    $db = new sql();
    $db->insert(
        'eventlog',
        array(
            'ts' => $gameRequest[1],
            'gamets' => $gameRequest[2],
            'type' => "user_input",
            'data' => $gameRequest[0],
            'sess' => 'pending',
            'localts' => time(),
            'people'=> '',
            'location'=>'',
            'party'=>''
        )
    );
    unset($db);
}

if (!in_array($gameRequest[0],["addnpc","updateprofile","diary","_quest","setconf","request","_speech","infoloc","infonpc","infonpc_close","infoaction",
        "status_msg","delete_event","itemfound","_questdata","_uquest","location","_questreset"])) {
    $semaphoreKey =abs(crc32(__FILE__));
    $semaphore = sem_get($semaphoreKey);
    
    while (sem_acquire($semaphore,true)!=true)  {
        //error_log("Audit: Waiting for lock: {$gameRequest[0]}");
        usleep(1000);
    }
    error_log("Audit:Lock adquired by {$gameRequest[0]}");
} 

// adnpc has its custom semaphore, as it write files
if (in_array($gameRequest[0],["addnpc"])) {
    $semaphoreKey2 =abs(crc32(__FILE__."_secondary"));
    $semaphore2 = sem_get($semaphoreKey2);
    while (sem_acquire($semaphore2,true)!=true)  {
        usleep(100);
    }
} 


if (($gameRequest[0]=="playerinfo")||(($gameRequest[0]=="newgame"))) {
    sleep(1);   // Give time to populate data
}

$db = new sql();

if (($gameRequest[0]=="delete_event")) {
    // Do this ASAP
    $datacn=$db->escape($gameRequest[3]);
    $db->delete("eventlog","type in ('chat','prechat') and data like '%$datacn%' and localts>".(time()- 120));
    audit_log(__FILE__);
    die();
}

// Profile selection
if (isset($_GET["profile"])) {
    
    $OVERRIDES["BOOK_EVENT_ALWAYS_NARRATOR"]=$GLOBALS["BOOK_EVENT_ALWAYS_NARRATOR"];
    $OVERRIDES["MINIME_T5"]=$GLOBALS["MINIME_T5"];
    $OVERRIDES["STTFUNCTION"]=$GLOBALS["STTFUNCTION"];
    $OVERRIDES["TTSFUNCTION_PLAYER"]=$GLOBALS["TTSFUNCTION_PLAYER"];
    $OVERRIDES["TTSFUNCTION_PLAYER_VOICE"]=$GLOBALS["TTSFUNCTION_PLAYER_VOICE"];

    //$OVERRIDES["PROMPT_HEAD"]=$GLOBALS["PROMPT_HEAD"];
    
    if (file_exists($path . "conf".DIRECTORY_SEPARATOR."conf_{$_GET["profile"]}.php")) {
       // error_log("PROFILE: {$_GET["profile"]}");
        require($path . "conf".DIRECTORY_SEPARATOR."conf_{$_GET["profile"]}.php");

    } else {
        // error_log(__FILE__.". Using default profile because GET PROFILE NOT EXISTS");
    }
    
    $GLOBALS["BOOK_EVENT_ALWAYS_NARRATOR"]=$OVERRIDES["BOOK_EVENT_ALWAYS_NARRATOR"];
    $GLOBALS["MINIME_T5"]=$OVERRIDES["MINIME_T5"];
    $GLOBALS["STTFUNCTION"]=$OVERRIDES["STTFUNCTION"];
    $GLOBALS["TTSFUNCTION_PLAYER"]=$OVERRIDES["TTSFUNCTION_PLAYER"];
    $GLOBALS["TTSFUNCTION_PLAYER_VOICE"]=$OVERRIDES["TTSFUNCTION_PLAYER_VOICE"];

    // $GLOBALS["PROMPT_HEAD"]=$OVERRIDES["PROMPT_HEAD"];
    // error_log("Using profile {$GLOBALS["TTSFUNCTION_PLAYER"]} {$_GET["profile"]} / ".$path . "conf".DIRECTORY_SEPARATOR."conf_{$_GET["profile"]}.php");
    
} else {
    //error_log(__FILE__.". Using default profile because NO GET PROFILE SPECIFIED");
    $GLOBALS["USING_DEFAULT_PROFILE"]=true;
}

// Player TTS. We overwrite some confs an then restore them.
if (in_array($gameRequest[0],["inputtext","inputtext_s","ginputtext","ginputtext_s"])) {
    // Use preg_replace to remove the name and colon before the dialogue
    $cleaned_dialogue = preg_replace('/^[^:]+:/', '', $gameRequest[3]);
    
    
    // audit_log(__FILE__." ".__LINE__);
    $GLOBALS["PATCH_OVERRIDE_VOICE"]=$TTSFUNCTION_PLAYER_VOICE;
    $GLOBALS["PATCH_DONT_STORE_SPEECH_ON_DB"]=true;
    $origTTS=$GLOBALS["TTSFUNCTION"];
    $origName=$GLOBALS["HERIKA_NAME"];

    $GLOBALS["TTSFUNCTION"]=$GLOBALS["TTSFUNCTION_PLAYER"];
    $GLOBALS["HERIKA_NAME"]="Player";

    // error_log("$cleaned_dialogue {$GLOBALS["TTSFUNCTION_PLAYER"]} {$GLOBALS["TTSFUNCTION"]} {$GLOBALS["PATCH_OVERRIDE_VOICE"]} override:{$OVERRIDES["TTSFUNCTION_PLAYER"]}");
    $ownspeech=returnlines([$cleaned_dialogue]);
    
    unset($GLOBALS["PATCH_OVERRIDE_VOICE"]);
    $GLOBALS["TTSFUNCTION"]=$origTTS;
    unset($GLOBALS["SCRIPTLINE_ANIMATION_SENT"]);
    $GLOBALS["HERIKA_NAME"]=$origName;
    unset($GLOBALS["PATCH_DONT_STORE_SPEECH_ON_DB"]);
    // audit_log(__FILE__." ".__LINE__);
        
    
}




$GLOBALS["active_profile"]=md5($GLOBALS["HERIKA_NAME"]);
$GLOBALS["CURRENT_CONNECTOR"]=DMgetCurrentModel();


// End of profile selection

// This is the correct place, after arse $gameRequest and before starting to do substituions

if (($gameRequest[0]=="chatnf_book")&&($GLOBALS["BOOK_EVENT_ALWAYS_NARRATOR"])) {
    // When chatnf_book (make the AI to read a book), will override profile and will select default one
    error_log("Override conf with default");
    require($path . "conf".DIRECTORY_SEPARATOR."conf.php");
    $GLOBALS["CURRENT_CONNECTOR"]=DMgetCurrentModel();
}

foreach ($gameRequest as $i => $ele) {
    $gameRequest[$i] = trim(preg_replace('/\s\s+/', ' ', preg_replace('/\'/m', "'", $ele)));
    //$gameRequest[$i] = trim(preg_replace('/\s\s+/', ' ', preg_replace('/\'/m', "''", $ele)));
    $gameRequest[$i]=strtr($gameRequest[$i],["#HERIKA_NPC1#"=>$GLOBALS["HERIKA_NAME"]]);
}



// $gameRequest = type of message|localts|gamets|data

if ($gameRequest[0]=="diary") {
    $GLOBALS["CURRENT_CONNECTOR"]=$GLOBALS["CONNECTORS_DIARY"];
}






// Exit if only a event info log.
if (in_array($gameRequest[0],["info","infonpc","infonpc_close","infoloc","chatme","chat","infoaction","death","goodnight","itemfound","travelcancel","infoplayer","infosave","status_msg"])) {
    $gameRequest[3]=isset($gameRequest[3])?$gameRequest[3]:"";
    $lastInfoNpcData=$db->escape($gameRequest[3]);
    $lastlogEqual=$db->fetchAll("select count(*) as n from eventlog where type in ('infonpc','infoloc','infonpc_close') and data='$lastInfoNpcData' and localts>".(time()-5));
    if (is_array($lastlogEqual) && isset($lastlogEqual[0]) && ($lastlogEqual[0]["n"]>0)) {
        // error_log("Skipping {$gameRequest[0]}");
        die();
    }
    logEvent($gameRequest);
    die();
}

if (in_array($gameRequest[0],["playerinfo","newgame"])) {
    if (!$GLOBALS["NARRATOR_WELCOME"]) {
        logEvent($gameRequest);
        die();
    } else {
        $FUNCTIONS_ARE_ENABLED=false;
    }
} 


// Fake entry to mark time passing when borded event
if (in_array($gameRequest[0],["bored"])) {
    $localGameRequest=$gameRequest;
    $localGameRequest[0]="infoaction";
    $localGameRequest[3].=". (Time passes without anyone in the group talking) ";
    logEvent($localGameRequest);
    
}


// Only allow functions when explicit request
if (!in_array($gameRequest[0],["inputtext","inputtext_s","ginputtext","ginputtext_s","instruction"])) {
    $FUNCTIONS_ARE_ENABLED=false;
}

// Force actions when instruction issued
if (in_array($gameRequest[0],["instruction"])) {
    $FUNCTIONS_ARE_ENABLED=true;
    $gameRequest[3]=strtr($gameRequest[3],[$GLOBALS["PLAYER_NAME"].":"=>""]);// Remove 'Player:'
}

if (in_array($gameRequest[0],["suggestion"])) {
    $FUNCTIONS_ARE_ENABLED=false;
    $gameRequest[3]=strtr($gameRequest[3],[$GLOBALS["PLAYER_NAME"].":"=>""]);// Remove 'Player:'
}


if ($GLOBALS["HERIKA_NAME"]=="The Narrator") {
    $FUNCTIONS_ARE_ENABLED=false;
}

$GLOBALS["CACHE_PARTY"]=DataGetCurrentPartyConf();
$currentParty=json_decode($GLOBALS["CACHE_PARTY"],true);
if (is_array($currentParty)) {
    if (in_array($GLOBALS["HERIKA_NAME"],array_keys($currentParty))) {
        $GLOBALS["IS_NPC"]=false;
    } else
        $GLOBALS["IS_NPC"]=true;
} else
    $GLOBALS["IS_NPC"]=false;

// RECHAT PRE MANAGMENT

requireFilesRecursively(__DIR__.DIRECTORY_SEPARATOR."ext".DIRECTORY_SEPARATOR,"prerequest.php");

if (in_array($gameRequest[0],["rechat"]) ) {
    //die();
    //RECHAT. Must choose if we continue conversation or no.
    $rechatHistory=DataRechatHistory();
    
    if (sizeof($rechatHistory)>($GLOBALS["RECHAT_H"]))    {   // TOO MUCH RECHAT
        error_log("Rechat discarded");
        // Lets try to summarize
        sem_release($semaphore);
        while(@ob_end_clean());
        require(__DIR__.DIRECTORY_SEPARATOR."processor".DIRECTORY_SEPARATOR."postrequest.php");
        die();
    }
    
    $rndNumber=rand(1,100);
    if ($rndNumber>($GLOBALS["RECHAT_P"]+0)) {              
        //die();
    } else
        die();
    
    
    if (sizeof($rechatHistory)>1) {
        // Lets make rechat wait a bit, so events while NPCs are speaking get into context
        sem_release($semaphore);
        error_log("HOLDING RECHAT EVENT ".sizeof($rechatHistory));
        sleep(1);
        while (sem_acquire($semaphore,true)!=true)  {
            $user_input_after=$db->fetchAll("select count(*) as N from eventlog where type='user_input' and ts>$gameRequest[1]");
            if (isset($user_input_after[0]))
                if (isset($user_input_after[0]["N"]))
                    if ($user_input_after[0]["N"]>0) {
                        error_log("Generation stopped because user_input. ".__LINE__);
                        die();// Abort rechat
                    }

            usleep(1000);
        }
    }

    $sqlfilter=" and type in ('prechat','inputtext','ginputtext','infonpc','infonpc_close','logaction') ";  // Use prechat
    $FUNCTIONS_ARE_ENABLED=false;       // Enabling this can be funny => CHAOS MODE

} else
    $sqlfilter=" and type<>'prechat' "; // Will dismiss prechat entries by default. prechat are LLM responses still not displayed in-game


// Non-LLM request handling.

require(__DIR__.DIRECTORY_SEPARATOR."processor".DIRECTORY_SEPARATOR."comm.php");

if ($MUST_END) {  // Shorthand for non LLM processing
    die('X-CUSTOM-CLOSE');
    
}


//error_log("TRACE:\t".__LINE__. "\t".__FILE__.":\t".(microtime(true) - $startTime));

/**********************
 CONTEXT DATA BUILDING
***********************/

// Include prompts, command prompts and functions.
require(__DIR__.DIRECTORY_SEPARATOR."prompt.includes.php");
$gameRequest[0] = strtolower($gameRequest[0]); // one more time in case it was changed by an extension

// Take care of override request if needed..
require(__DIR__.DIRECTORY_SEPARATOR."processor".DIRECTORY_SEPARATOR."request.php");


/*
 Safe stop
*/
if (preg_match(STOPALL_MAGIC_WORD, $gameRequest[3]) === 1) {  
    echo "{$GLOBALS["HERIKA_NAME"]}|command|Halt@\r\n";
    @ob_flush();
    $alreadysent[md5("{$GLOBALS["HERIKA_NAME"]}|command|Halt@\r\n")] = "{$GLOBALS["HERIKA_NAME"]}|command|Halt@\r\n";
}

if (!isset($GLOBALS["CACHE_PEOPLE"])) {
    $GLOBALS["CACHE_PEOPLE"]=DataBeingsInRange();
} 
if (!isset($GLOBALS["CACHE_LOCATION"])) {
    $GLOBALS["CACHE_LOCATION"]=DataLastKnownLocation();
}     

if (!isset($GLOBALS["CACHE_PARTY"])) {
        $GLOBALS["CACHE_PARTY"]=DataGetCurrentPartyConf();
} 

if (in_array($gameRequest[0],["inputtext_s"])) {    // I stealth and targetet follower, CACHE_PEOPLE will only contain target NPC
    $GLOBALS["CACHE_PEOPLE"]=$GLOBALS["HERIKA_NAME"];
}

/// LOG INTO DB. Will use this later.
if ($gameRequest[0] != "diary") {
    $db->insert(
        'eventlog',
        array(
            'ts' => $gameRequest[1],
            'gamets' => $gameRequest[2],
            'type' => $gameRequest[0],
            'data' => ($gameRequest[3]),
            'sess' => (php_sapi_name()=="cli")?'cli':'web',
            'localts' => time(),
            'people'=> $GLOBALS["CACHE_PEOPLE"],
            'location'=>$GLOBALS["CACHE_LOCATION"],
            'party'=>$GLOBALS["CACHE_PARTY"],
            
        )
    );

}

// Check if this event  has been disabled 
if (isset($GLOBALS["PROMPTS"][$gameRequest[0]]["extra"]["dontuse"])) {
    if ($GLOBALS["PROMPTS"][$gameRequest[0]]["extra"]["dontuse"])
        die("\r\n");
}


// Narrator stop (from config)

if (isset($GLOBALS["NARRATOR_TALKS"])&&($GLOBALS["NARRATOR_TALKS"]==false)) {
    if ($GLOBALS["HERIKA_NAME"]=="The Narrator")
        die("\r\n");
}

$lastNDataForContext = (isset($GLOBALS["CONTEXT_HISTORY"])) ? ($GLOBALS["CONTEXT_HISTORY"]) : "25";

// Historic context (last dialogues, events,...)
//if ((!$GLOBALS["IS_NPC"])||($GLOBALS["HERIKA_NAME"]=="The Narrator"))
if (($GLOBALS["HERIKA_NAME"]=="The Narrator"))
    $contextDataHistoric = DataLastDataExpandedFor("", $lastNDataForContext * -1,$sqlfilter);
else if (!$GLOBALS["IS_NPC"])
    $contextDataHistoric = DataLastDataExpandedFor("{$GLOBALS["HERIKA_NAME"]}", $lastNDataForContext * -1,$sqlfilter);
else if ($GLOBALS["IS_NPC"]) {
    $contextDataHistoric = DataLastDataExpandedFor("{$GLOBALS["HERIKA_NAME"]}", $lastNDataForContext * -1,$sqlfilter);
    
}


// Info about location and npcs in first position
$contextDataWorld = DataLastInfoFor("", -2);

// Add current motto to COMMAND_PROMPT
if ($gameRequest[0] != "diary")
    if ((!$GLOBALS["IS_NPC"])||($GLOBALS["HERIKA_NAME"]=="The Narrator")) {
        $task=DataGetCurrentTask();
        if (empty($task)) {
            $task="No active quests right now.";
        }
        $GLOBALS["COMMAND_PROMPT"].=$task;
    } else {
        error_log("Task avoided {$GLOBALS["IS_NPC"]} ");
    }

// Offer memory in CONTEXT 
/*
if (!(isset($GLOBALS["MEMORY_INJECTION_ON"]) || (!$GLOBALS["MEMORY_INJECTION_ON"]))) {
    $GLOBALS["FEATURES"]["MEMORY_EMBEDDING"]["ENABLED"]=false;
}

$memoryInjection=offerMemory($gameRequest, $DIALOGUE_TARGET);
if ($memoryInjection) {
    
    //$memoryInjectionCtx[]= array('role' => 'user', 'content' => $gameRequest[3]);
    $memoryInjectionCtx[]= array('role' => 'user', 'content' => "#MEMORY: [$memoryInjection]");
    $contextDataHistoric=array_merge($memoryInjectionCtx,$contextDataHistoric);

    if (isset($GLOBALS["USE_MEMORY_STATEMENT_DELETE"]) && $GLOBALS["USE_MEMORY_STATEMENT_DELETE"] ) {
        $request=str_replace($GLOBALS["MEMORY_STATEMENT"],"",$request);
    }
    //$GLOBALS["COMMAND_PROMPT"].="'{$gameRequest[3]}'\n{$GLOBALS["HERIKA_NAME"]}):$memoryInjection\n";
    
} else {
    
    $request=str_replace($GLOBALS["MEMORY_STATEMENT"],"",$request);
        
}
*/   

if (in_array($gameRequest[0],["inputtext","inputtext_s","ginputtext","ginputtext_s"]) ) {

    $memoryInjection=offerMemory($gameRequest, $DIALOGUE_TARGET);
    if (!empty($memoryInjection)) {
        
        //$memoryInjectionCtx[]= array('role' => 'user', 'content' => $gameRequest[3]);
        $memoryInjectionCtx= array('role' => 'user', 'content' => "#MEMORY: {$GLOBALS["HERIKA_NAME"]} remembers this: [$memoryInjection]");
        //$GLOBALS["COMMAND_PROMPT"].="'{$gameRequest[3]}'\n{$GLOBALS["HERIKA_NAME"]}):$memoryInjection\n";
        
    } else {
        $memoryInjectionCtx=[];
        $request=str_replace($GLOBALS["MEMORY_STATEMENT"],"",$request);
            
    }
} else
     $memoryInjectionCtx=[];


// array('role' => $currentSpeaker, 'content' => implode("\n", $buffer));

if ($GLOBALS["FUNCTIONS_ARE_ENABLED"]) {
    
    if ($GLOBALS["MINIME_T5"]) {
        $pattern = "/\([^)]*Context location[^)]*\)/"; // Remove (Context location..
        $replacement = "";
        $TEST_TEXT = preg_replace($pattern, $replacement, $gameRequest[3]); // // assistant vs user war
        
        $pattern = '/\(talking to [^()]+\)/i';
        $TEST_TEXT = preg_replace($pattern, '', $TEST_TEXT);
        

        $TEST_TEXT=strtr($TEST_TEXT,["."=>" ","{$GLOBALS["PLAYER_NAME"]}:"=>""]);
        $command=file_get_contents("http://127.0.0.1:8082/command?text=".urlencode($TEST_TEXT));
        if ($command && $command !== "null") {
            $preCommand=json_decode($command,true);
            if ($preCommand["is_command"]!="Talk") {
                $GLOBALS["db"]->insert(
                    'audit_memory',
                    array(
                        'input' => $TEST_TEXT,
                        'keywords' =>'command offered',
                        'rank_any'=> -1,
                        'rank_all'=>-1,
                        'memory'=>$preCommand["is_command"],
                        'time'=>$preCommand["elapsed_time"]
                    )
                );
                error_log("ENFORCING COMMAND: <{$preCommand["is_command"]}>");
                $memoryInjectionCtx=[]; // Disable memorie when command.
                $COMMAND_PROMPT_ENFORCE_ACTIONS.="(USER MAY WANTS YOU TO ISSUE ACTION {$preCommand["is_command"]}).";
                $GLOBALS["PATCH_PROMPT_ENFORCE_ACTIONS"]=true;
            } 
        }

       
    }

    $GLOBALS["COMMAND_PROMPT"].=$GLOBALS["COMMAND_PROMPT_FUNCTIONS"];
}


// OGHMA STUFF

require(__DIR__."/processor/oghma.php");

if (sizeof($memoryInjectionCtx)>0) {
    // Persist memory injetction
    $gameRequestCopy=$gameRequest;
    $gameRequestCopy[0]="infoaction";
    $gameRequestCopy[3]=$memoryInjectionCtx["content"];
    logEvent($gameRequestCopy);
}

$contextDataFull = array_merge($contextDataWorld, $contextDataHistoric);

if (($gameRequest[0]=="chatnf_book")&&($GLOBALS["BOOK_EVENT_FULL"])) {
    // When chatnf_book (make the AI to read a book), context will only be the book data.
    $contextDataFull = DataGetLastReadedBook();
}


// Check for context overrides on ext dir (plugins)
requireFilesRecursively(__DIR__.DIRECTORY_SEPARATOR."ext".DIRECTORY_SEPARATOR,"context.php");

if (isset($GLOBALS["ADD_PLAYER_BIOS"])&&($GLOBALS["ADD_PLAYER_BIOS"])) {
    $GLOBALS["PROMPT_HEAD"].=PHP_EOL.$GLOBALS["PLAYER_BIOS"];
}

if (isset($GLOBALS["OGHMA_HINT"]) && $GLOBALS["OGHMA_HINT"]) {
    $GLOBALS["PROMPT_HEAD"].=$GLOBALS["OGHMA_HINT"];

}

$head[] = array('role' => 'system', 'content' =>  
    strtr($GLOBALS["PROMPT_HEAD"] . "\n".$GLOBALS["HERIKA_PERS"] ."\n". $GLOBALS["COMMAND_PROMPT"],["#PLAYER_NAME#"=>$GLOBALS["PLAYER_NAME"]])
);


/**********************
CALL BUILDING
***********************/

if ($gameRequest[0] == "funcret") {

    $prompt[] = array('role' => 'assistant', 'content' => $request);

    // Manage function stuff
    // $contextData will be populated

    require(__DIR__.DIRECTORY_SEPARATOR."processor".DIRECTORY_SEPARATOR."funcret.php");


} elseif ((strpos($gameRequest[0], "chatnf")!==false)) {

    // Won't use  functions.
    // $prompt and $contextData will be created
    if (!empty($request) && $request != "") {
        $prompt[] = array('role' => $LAST_ROLE, 'content' => $request);
        $contextData = array_merge($head, ($contextDataFull), $prompt);
    }
    else {
        $contextData = array_merge($head, ($contextDataFull));
    }


}  else {
    if (in_array($GLOBALS["CURRENT_CONNECTOR"],["koboldcpp","openai","google_openai","openrouter"])) {  // OLD SCHEMA
        if (!empty($request)) {
            if (sizeof($memoryInjectionCtx)>0) {
                if (!isset($prompt)) {
                    $prompt=[];
                }
                array_splice($prompt, -1, 0, $memoryInjectionCtx); // add memory as second-to-last entry
                error_log("Injected memory");
            }
            $FUNCTIONS_ARE_ENABLED=false;
            $prompt[] = array('role' => $LAST_ROLE, 'content' => $request);

            
        } else
            $prompt=[];
     
        $GLOBALS["CONNECTOR"][$GLOBALS["CURRENT_CONNECTOR"]]["stop"]=["\n"];
        
        if ($gameRequest[0]=="diary") {
            unset($GLOBALS["CONNECTOR"][$GLOBALS["CURRENT_CONNECTOR"]]["stop"]);
        }
        
        
    } else {
        if (!empty($request)) {
            $prompt[] = array('role' => $LAST_ROLE, 'content' => $request);
            if (sizeof($memoryInjectionCtx)>0) {
                array_splice($prompt, -1, 0, $memoryInjectionCtx); // add memory as second-to-last entry
                error_log("Injected memory");
            }
            
        } else
            $prompt=[];
    }

    $contextData = array_merge($head, ($contextDataFull), $prompt);
    
}

//error_log("*TRACE:\t".__LINE__. "\t".__FILE__.":\t".(microtime(true) - $startTime));
//returnLines(["Mmm..let me think"]);




/**********************
CALL INITIALIZATION
***********************/

if (!isset($GLOBALS["CURRENT_CONNECTOR"]) || (!file_exists(__DIR__.DIRECTORY_SEPARATOR."connector".DIRECTORY_SEPARATOR."{$GLOBALS["CURRENT_CONNECTOR"]}.php"))) {
    die("{$GLOBALS["HERIKA_NAME"]}|AASPGQuestDialogue2Topic1B1Topic|I'm mindless. Choose a LLM model and connector.".PHP_EOL);

} else {

    require(__DIR__.DIRECTORY_SEPARATOR."connector".DIRECTORY_SEPARATOR."{$GLOBALS["CURRENT_CONNECTOR"]}.php");

    $connectionHandler=new connector();
    $connectionHandler->open($contextData,$overrideParameters);
    

}

///// PATCH. STORE FUNCTION RESULT ONCE RESULT PROMPT HAS BEEN BUILT.

if (isset($GLOBALS["PATCH_STORE_FUNC_RES"])) {
    $gameRequestCopy=$gameRequest;
    $gameRequestCopy[0]="infoaction";
    $gameRequestCopy[3]=$GLOBALS["PATCH_STORE_FUNC_RES"];
    logEvent($gameRequestCopy);
}

///// PATCH

if ($connectionHandler->primary_handler === false) {

    $db->insert(
        'log',
        array(
            'localts' => time(),
            'prompt' => nl2br((json_encode($GLOBALS["DEBUG_DATA"], JSON_PRETTY_PRINT))),
            'response' => ((print_r(error_get_last(), true))),
            'url' => nl2br(("$receivedData in " . (microtime(true) - $startTime) . " secs "))


        )
    );
    returnLines([$GLOBALS["ERROR_OPENAI"]]);
    
    $ERROR_TRIGGERED=true;
    @ob_end_flush();

    error_log(print_r(error_get_last(), true));

} else {

    // Read and process the response line by line
    $buffer="";
    $totalBuffer="";
    $breakFlag=false;
    $lineCounter=0;
    $fullContent="";
    $totalProcessedData="";
    $numOutputTokens = 0;

    while (true) {

        if ($breakFlag) {
            break;
        }


        $buffer.=$connectionHandler->process();
        $totalBuffer.=$buffer;


        if ($connectionHandler->isDone()) {
            $breakFlag=true;
        }

        $buffer=strtr($buffer, array("\""=>"",".)"=>")."));

        if (strlen($buffer)<MINIMUM_SENTENCE_SIZE) {	// Avoid too short buffers
            continue;
        }

        $position = findDotPosition($buffer);

        //echo "<$buffer>".PHP_EOL;
        if ($position !== false && $position>MINIMUM_SENTENCE_SIZE ) {
            $extractedData = substr($buffer, 0, $position + 1);
            $remainingData = substr($buffer, $position + 1);
            $sentences=split_sentences_stream(cleanResponse($extractedData));
            $GLOBALS["DEBUG_DATA"]["response"][]=["raw"=>$buffer,"processed"=>implode("|", $sentences)];
            $GLOBALS["DEBUG_DATA"]["perf"][]=(microtime(true) - $startTime)." secs in openai stream";

            if ($gameRequest[0] != "diary") {
                returnLines($sentences);
            } else {
                $talkedSoFar[md5(implode(" ", $sentences))]=implode(" ", $sentences);
            }

            //echo "$extractedData  # ".(microtime(true)-$startTime)."\t".strlen($finalData)."\t".PHP_EOL;  // Output
            $totalProcessedData.=$extractedData;
            $extractedData="";
            $buffer=$remainingData;

            $user_input_after=$db->fetchAll("select count(*) as N from eventlog where type='user_input' and ts>$gameRequest[1]");
            if (isset($user_input_after[0]))
                if (isset($user_input_after[0]["N"]))
                    if ($user_input_after[0]["N"]>0) {
                        die('X-CUSTOM-CLOSE');
                        error_log("Generation stopped because user_input. ".__LINE__);
                        // Abort , user input detected
                    }

        }

    }
    
    
    if (trim($buffer)) {
        // error_log("REMAINING DATA <$buffer>");
        $sentences=split_sentences_stream(cleanResponse(trim($buffer)));
        $GLOBALS["DEBUG_DATA"]["response"][]=["raw"=>$buffer,"processed"=>implode("|", $sentences)];
        $GLOBALS["DEBUG_DATA"]["perf"][]=(microtime(true) - $startTime)." secs in openai stream";
        if ($gameRequest[0] != "diary") {
            returnLines($sentences);
        } else {
            $talkedSoFar[md5(implode(" ", $sentences))]=implode(" ", $sentences);
        }
        $totalBuffer.=trim($buffer);
        $totalProcessedData.=trim($buffer);
    }

    if ($GLOBALS["FUNCTIONS_ARE_ENABLED"])  {
        $actions=$connectionHandler->processActions();

        if (is_array($actions) && (sizeof($actions)>0)) {
            
            // ACTION POST-FILTER
            
            if ($GLOBALS["FUNCTIONS_ARE_ENABLED"]) {
                
                foreach ($actions as $n=>$action) {
                    $actionParts=explode("|",$action);
                    $actionParts2=explode("@",$actionParts[2]);
                    
                    if (isset($actionParts2[1])) {
                        // Parameter part 
                        if ($actionParts2[0]=="Attack") {
                            // Lets polish the parammeters
                            $localtarget=$actionParts2[1];
                            $mang1=explode(",",$localtarget);
                            $mang2=explode(" and ",$mang1[0]);
                            $mang3=explode("(",$mang2[0]);
                            $actions[$n]="{$actionParts[0]}|{$actionParts[1]}|Attack@{$mang3[0]}";
                        }
                    }
                }
            }

            $GLOBALS["DEBUG_DATA"]["response"][]=$actions;
            echo implode("\r\n", $actions).PHP_EOL;
            file_put_contents(__DIR__."/log/ouput_to_plugin.log",implode("\r\n", $actions), FILE_APPEND | LOCK_EX);

        }
    }
    $connectionHandler->close();
    //fwrite($fileLog, $totalBuffer . PHP_EOL); // Write the line to the file with a line break // DEBUG CODE


}

if (sizeof($talkedSoFar) == 0) {
    if (sizeof($alreadysent) > 0) { // AI only issued commands

        $db->insert(
            'log',
            array(
                'localts' => time(),
                'prompt' => nl2br((json_encode($GLOBALS["DEBUG_DATA"], JSON_PRETTY_PRINT))),
                'response' => (print_r($alreadysent, true)),
                'url' => nl2br(("$receivedData in " . (microtime(true) - $startTime) . " secs "))


            )
        );
        // Should choose wich events she tends to call function without response.
        //returnLines(["Sure thing!"]);

    } else { // Fail request? or maybe an invalid command was issued

        //returnLines(array($randomSentence));
        $db->insert(
            'log',
            array(
                'localts' => time(),
                'prompt' => nl2br((json_encode($GLOBALS["DEBUG_DATA"], JSON_PRETTY_PRINT))),
                'response' => (print_r($alreadysent, true)),
                'url' => nl2br(("$receivedData in " . (microtime(true) - $startTime) . " secs "))


            )
        );

    }
} else {

    if (sizeof($alreadysent) > 0) { // AI only issued commands
        $db->insert(
            'log',
            array(
                'localts' => time(),
                'prompt' => nl2br((json_encode($GLOBALS["DEBUG_DATA"], JSON_PRETTY_PRINT))),
                'response' => (print_r($alreadysent, true)),
                'url' => nl2br(("$receivedData in " . (microtime(true) - $startTime) . " secs "))
            )
        );
    }

    if (!$ERROR_TRIGGERED) {
        if ($gameRequest[0] == "diary") {
            $topic=DataLastKnowDate();
            $location=DataLastKnownLocation();
            $db->insert(
                'diarylog',
                array(
                    'ts' => $gameRequest[1],
                    'gamets' => $gameRequest[2],
                    'topic' => "$topic",
                    'content' => (implode(" ", $talkedSoFar)),
                    'tags' => "Pending",
                    'people' => $GLOBALS["HERIKA_NAME"],
                    'location' => "$location",
                    'sess' => 'pending',
                    'localts' => time()
                )
            );
            /*
            $db->insert(
			'diarylogv2',
                array(
                    'topic' => ($topic),
                    'content' => (implode(" ", $talkedSoFar)),
                    'tags' => "Pending",
                    'people' => "Pending",
                    'location' => "$location"
                )
            );
            */
            // Log Memory also.
            if ((php_sapi_name()!="cli"))	
	            logMemory($GLOBALS["HERIKA_NAME"], $GLOBALS["HERIKA_NAME"],implode(" ", $talkedSoFar), $momentum, $gameRequest[2],$gameRequest[0],$gameRequest[1]);
            returnLines([$RESPONSE_OK_NOTED]);

        } else {
            
            $lastPlayerLine=$db->fetchAll("SELECT data from eventlog where type in ('inputtext','inputtext_s') order by gamets desc limit 1 offset 0");
            if (php_sapi_name()!="cli")	{
                if (in_array($gameRequest[0],["inputtext","inputtext_s"]))
                    // logMemory($GLOBALS["HERIKA_NAME"], $GLOBALS["PLAYER_NAME"], "{$lastPlayerLine[0]["data"]} \n\r {$GLOBALS["HERIKA_NAME"]}:".implode(" ", $talkedSoFar), $momentum, $gameRequest[2],$gameRequest[1]);
                    ;
                else {
                    // Speech table will take care
                    //logMemory($GLOBALS["HERIKA_NAME"], $GLOBALS["PLAYER_NAME"], "{$GLOBALS["HERIKA_NAME"]}:".implode(" ", $talkedSoFar), $momentum, $gameRequest[2]);
                    ;
                }
            }
        }
    }
}



echo 'X-CUSTOM-CLOSE'.PHP_EOL;

if (php_sapi_name()=="cli") {
    echo PHP_EOL;
    file_put_contents("log/debug_comm_".basename(__FILE__).".log", print_r($GLOBALS["DEBUG_DATA"], true));

    //$db->delete("eventlog", "sess='cli'");

}


// POST PROCESS TASKS
if ($semaphore) 
    sem_release($semaphore);

while(@ob_end_clean());
require(__DIR__.DIRECTORY_SEPARATOR."processor".DIRECTORY_SEPARATOR."postrequest.php");


?>
