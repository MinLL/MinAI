<?php
// Force the voice type to use the actor's base voice type (eg, "maleguard") instead of "whiterun_guard".
// Useful for compatibility with mantella's xtts configuration if you don't want to mess with setting up latents
$GLOBALS["force_voice_type"] = true;
// Globally disable all NSFW features
$GLOBALS["disable_nsfw"] = false;
// Set this to override the prompt head for all profiles
$GLOBALS["PROMPT_HEAD_OVERRIDE"] = "";
?>
