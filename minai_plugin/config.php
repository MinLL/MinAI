<?php
// Player bio - Added to the prompt head for dialogue that is not npc -> npc
$GLOBALS["PLAYER_BIO"] = "I'm #PLAYER_NAME#. ";

// Set this to override the prompt head for all profiles
$GLOBALS["PROMPT_HEAD_OVERRIDE"] = "";

// Use a specific profile for the narrator instead of the default one.
$GLOBALS["use_narrator_profile"] = false;

// Purge narrator dialogue from context shared with party members to avoid them commenting on narrator comments
$GLOBALS["stop_narrator_context_leak"] = false; 

// Voice type overrides for devious narrator
$GLOBALS["devious_narrator_eldritch_voice"] = "dragon";
$GLOBALS["devious_narrator_telvanni_voice"] = "TelvanniNarrator";

// Force the voice type to use the actor's base voice type (eg, "maleguard") instead of "whiterun_guard".
// Useful for compatibility with mantella's xtts configuration if you don't want to mess with setting up latents
$GLOBALS["force_voice_type"] = false;

// Globally disable all NSFW features
$GLOBALS["disable_nsfw"] = false;

// Restrict actions that only make sense for followers to followers
$GLOBALS["restrict_nonfollower_functions"] = true;

// Enable functions during things like rechat.
// Allows for better party dynamics. Disabled for the narrator to avoid CTD.
$GLOBALS["always_enable_functions"] = true;

// Force the configured name in AI-FF to match the in-game name
$GLOBALS["force_aiff_name_to_ingame_name"] = true;

// List of commands to disable.
$GLOBALS["commands_to_purge"] = array("TakeASeat");

// List of events from the game to ignore.
// Example: $GLOBALS["events_to_ignore"] = array("rpg_lvlup");
// This would cause the LLM to not coment on the player leveling up.
$GLOBALS["events_to_ignore"] = array();

// disable worn equipment
$GLOBALS["disable_worn_equipment"] = false;


// Overrides for rechat settings during radiant dialogue.
// NOTE: At least one rechat is always guaranteed after a radiant dialogue is started.
$GLOBALS["radiance_rechat_h"] = 4;
$GLOBALS["radiance_rechat_p"] = 20;

// Set this to force the xtts server to be the same for all profiles
$GLOBALS["xtts_server_override"] = "";

// Cleanup emotes like *smirking* from the LLM's response. Disabled by default as some LLM's use *'s for quotes.
$GLOBALS["strip_emotes_from_output"] = false;

// How many seconds after player input should radiant dialogue be blocked?
// This is intended to prevent the situation where the player begins talking, and a radiant conversation is 
// triggered before the LLM and TTS have finished with rthe response. For radiant dialogue frequency configuration,
// use the in-game MCM.
$GLOBALS["input_delay_for_radiance"] = 15;

// Voice type fallbacks. These provide default voices based on the actor's gender and race if xtts did not have a valid voicetype for them.
// "genderRace" => "voicename"
$GLOBALS["voicetype_fallbacks"] = [
    "maleargonian" => "argonianmale",
    "femaleargonian" => "argonianfemale",
    "malekhajiit" => "khajiitmale",
    "femalekhajiit" => "khajiitfemale",
    "maleredguard" => "maleeventonedaccented",
    "femaleredguard" => "femaleeventonedaccented",
    "malenord" => "malecondescending",
    "femalenord" => "femalecondescending",
    "malebreton" => "malecommoner",
    "femalebreton" => "femalecommoner",
    "maleimperial" => "maleeventoned",
    "femaleimperial" => "femaleeventoned",
    "maleorc" => "maleorc",
    "femaleorc" => "femaleorc",
    "malealtmer" => "maleelfhaughty",
    "femalealtmer" => "femaleelfthaughty",
    "malehighelf" => "maleelfhaughty",
    "femalehighelf" => "femaleelfthaughty",
    "maledunmer" => "maledarkelf",
    "femaledunmer" => "femaledarkelf",
    "maledarkelf" => "maledarkelf",
    "femaledarkelf" => "femaledarkelf",
    "maleOld People Race" => "maleoldkindly",
    "femaleOld People Race" => "femaleoldkindly"
];
?>
