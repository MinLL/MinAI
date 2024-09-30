<?php
// Force the voice type to use the actor's base voice type (eg, "maleguard") instead of "whiterun_guard".
// Useful for compatibility with mantella's xtts configuration if you don't want to mess with setting up latents
$GLOBALS["force_voice_type"] = false;
// Globally disable all NSFW features
$GLOBALS["disable_nsfw"] = false;
// Restrict actions that only make sense for followers to followers
$GLOBALS["restrict_nonfollower_functions"] = true;
// Purge narrator dialogue from context shared with party members to avoid them commenting on narrator comments
$GLOBALS["stop_narrator_context_leak"] = false;
// Enable functions during things like rechat.
// Allows for better party dynamics. Disabled for the narrator to avoid CTD.
$GLOBALS["always_enable_functions"] = true;
// Set this to override the prompt head for all profiles
$GLOBALS["PROMPT_HEAD_OVERRIDE"] = "";
// Force the configured name in AI-FF to match the in-game name
$GLOBALS["force_aiff_name_to_ingame_name"] = true;
// List of commands to disable.
$GLOBALS["commands_to_purge"] = array("TakeASeat");
?>
