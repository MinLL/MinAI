# Frequently Asked Questions

## My model is dumb / keeps spamming actions inappropriately!
This is caused by the LLM you are using. If you are using Llama 70b, be aware that 90% of user complaints around LLM behavior are from this model. It tends to be rather shallow in it's RP, and very aggressively use actions at inappropriate times.


## What are your favorite models?
At this time, I would consider [Claude Sonnet 3.5](https://openrouter.ai/anthropic/claude-3.5-sonnet) to be the strongest model for both Sapience, and followers. It is both fast, and intelligent, though can suffer with censorship in some situations. [Claude Sonnet 3.0](https://openrouter.ai/anthropic/claude-3-sonnet) is a bit better about censorship, while being close in performance.

[Grok 2 Beta](https://openrouter.ai/x-ai/grok-beta) is also a strong general purpose model (Though, isn't quite as smart as Command R+ or Claude at action usage), and is completely uncensored.

My old setup was to use [Command R+](https://openrouter.ai/cohere/command-r-plus) for sapient NPC's, and [Hermes 405b](https://openrouter.ai/nousresearch/hermes-3-llama-3.1-405b) for followers.

Command R+ is a pretty solid general purpose LLM that is reasonably intelligent with action usage, while being fast and responsive and uncensored.

Hermes 405b is a strong, intelligent uncensored model, but is slower than Command R+.

Honorable mention to [Wizard 8x22b](https://openrouter.ai/microsoft/wizardlm-2-8x22b). This is a fast, cheap uncensored model that is strong at action usage, but is very chatty / verbose in its responses. May be a good choice if you like this sort of RP / dialogue.


## Things aren't working at all! I keep seeing a message about first time setup complete every time I load the game.
This is caused by you not having all of the hard requirements installed (Particularly Papyrus Tweaks). Validate that you have them installed, and that you have up to date versions of them. If you did not have them, install them and then revert to a save prior to installing MinAI.

## The Action Registry is empty!
This is caused by you not having all of the hard requirements installed (Particularly Papyrus Tweaks). Validate that you have them installed, and that you have up to date versions of them. If you did not have them, install them and then revert to a save prior to installing MinAI.

## Actions aren't working!
NSFW actions are only enabled if you have either Sexlab or ostim installed. If you don't have either, most of the actions and context will not be injected. In order to troubleshoot this you should:
1) Validate that you have all of the requirements installed.
2) Validate that your character name in-game exactly matches your character name in the AI-FF UI.
3) Validate that you have actions enabled in the AI-FF MCM.
4) Validate that the actions are showing up as enabled in the in-game MCM's Action Registry.
5) Look at your ai log, and look at the actions that are being sent to the LLM. In the list of actions, you should see actions from MinAI (Such as "AVAILABLE ACTION: Grope : Grope the target"). If you see actions here, it means that both the in-game plugin and server-side plugin are working. It's likely that the LLM is just deciding not to use the actions in this case. If you don't see the actions in the list, it means that they are not being exposed for the LLM. If they are not present, validate that you have the server plugin and the skyrim plugin both installed correctly, and look at your Papyrus log.
6) Look at your ai log, and look at the output from the LLM. There will be an "action" field in the response from the LLM. This is where the commands that the LLM executes will be specified. If the commands are not here, it means that the LLM is not sending them. If the commands are here and aren't working, you will need to examine your Papyrus log.
7) Enable Papyrus logging, and look in your log for errors related to MinAI. Anything coming from MinAI with the log level of "ERROR" or "FATAL" is probably why things are not working for you.

## Sex actions specifically aren't working!
In addition to the above sections, MinAI offers a feature to only expose sex / general perverted actions to the AI conditionally based on the NPC's arousal level (Disabled by default). Ensure that if you have set minimum arousal thresholds in the MCM, that the NPC is suitably aroused to use these actions.

## How do I set use Mantella's XTTS with CHIM?
CHIM is not compatible with the xtts from Mantella out of the box. The reason for this, is that CHIM requests the character name from xtts as the name of the voice type, where-as Mantella's XTTS expects the request to be for the base voice type. For example, for a guard in whiterun, CHIM would request a voice of type "whiterun_guard" from xtts, where-as mantella would expect "maleguard" instead.

MinAI offers a feature allowing you to use the latents from Mantella with CHIM seamlessly. It does this by reading the base voicetype of the NPC's you encounter in the game, and saving them. When CHIM requests a voice from xtts with this mode enabled, it instead sends the base voice type of the actor instead, allowing the Mantella XTTS to work with CHIM.
```
// Force the voice type to use the actor's base voice type (eg, "maleguard") instead of "whiterun_guard".
// Useful for compatibility with mantella's xtts configuration if you don't want to mess with setting up latents
$GLOBALS["force_voice_type"] = true;
```

You can enable this by editing the config.php that comes with this mod, and setting "force_voice_type" to true. This requires a functioning mantella xtts setup to utilize. Note, that this substitution only works in-game, and will not work in the "TTS Troubleshooting" page. If you want to validate that things are working via the troubleshooting tool, you should update the voiceid of the npc you are testing with to the base voice type appropriate ("maleguard", "malecommoner", etc).

MinAI also supports a feature to allow you to configure an automatic retry to the XTTS should a voice type be missing. This is intended to solve situations where mod added actors have base voice types that you do not have the latents for. In config.php, you can configure this like such:
```
// "genderRace" "voicetype"
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
```
For example, the above configuration would have all male bretons that are missing a voice instead default to the "malecommoner" voice, thereby ensuring that all mod added bretons (No matter their voice type) have at least a default voice to use. Ensure that you have latents for each of the above voice types, and configure them appropriately if you do not.

Here is an example of a correctly configured xtts in the UI:

![image](https://github.com/user-attachments/assets/db7a8a66-ddba-4a97-bdd0-6962a336d6db)

In this case, I am forcing this actor to use the "cruniqueodahviing" voice type. Note, that there is no trailing slash on the URL.
