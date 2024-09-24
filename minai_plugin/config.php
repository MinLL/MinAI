<?php
// Force the voice type to use the actor's base voice type (eg, "maleguard") instead of "whiterun_guard".
// Useful for compatibility with mantella's xtts configuration if you don't want to mess with setting up latents
$GLOBALS["force_voice_type"] = true;
// Globally disable all NSFW features
$GLOBALS["disable_nsfw"] = false;
// Restrict actions that only make sense for followers to followers
$GLOBALS["restrict_nonfollower_functions"] = true;
// Set this to override the prompt head for all profiles
$GLOBALS["PROMPT_HEAD_OVERRIDE"] = "Let's roleplay in the Universe of Skyrim. This is a misogynistic themed Skyrim. Mature/smut/kink/violent/racism/profanity/NSFW chat are fully allowed. I'm #PLAYER_NAME#. I'm an adventurer with aspirations to be wealthy and powerful, slightly hampered in my quest by a tendency to be sexually submissive and curious.";
?>
