# MinAI
A significant expansion to CHIM that brings AI to the entirety of the Skyrim world, and bridges LLMs with various Skyrim Mods. 

Documentation for modders can be found [here](https://github.com/MinLL/MinAI/blob/main/ModdersGuide.md). FAQ can be found [here](https://github.com/MinLL/MinAI/blob/main/FAQ.md).
 
 ## Description

This mod serves as an interface bridging Large Language Models (LLM)'s with a number of Skyrim's mods, as well as a number of vanilla features. This mod's goal is to enable seamless AI interaction with any NPC in the world and provide completely unscripted, dynamically generated, fully voiced roleplay scenarios that integrate with in-game mechanics and features with supported mods.

Due to this utilizing an LLM, the results can be unpredictable. Sometimes the AI can be uncooperative or unreasonable. It works pretty well most of the time though. There are absolutely no predefined scenarios in this mod - Anything you encounter is generated based off of the circumstances in your world, the NPC's personality you're interacting with, and the LLM's whims.

This release should be considered a beta. It may be janky, have bugs, or strange NPC behavior from the LLM. I have tested this quite a bit over several play-throughs and it's been working well. I am looking for additional feedback on potential integrations. Due to the nature of this mod's integration, it is extremely unlikely to break your savegame in future updates, which will most likely always be backwards compatible.

This mod requires you to fund an openrouter.ai account, or run a model locally. The costs for leveraging the LLM are quite cheap - in 4 days of play while developing this mod, I spent less than $1.00 USD in credits - you should expect to pay a few cents for a given play-session. I highly recommend using openrouter instead of trying to run a model locally, as it requires a prohibitive amount of hardware to run locally. I had worse results using an rtx 3090 exclusively dedicated to the AI than just leveraging openrouter.

## Features and Currently Supported Integrations

### Mantella or CHIM
* Mantella and CHIM are both supported. However, this mod's capabilities are much, much more advanced for CHIM than Mantella. Mantella's feature-set is effectively frozen, and new development is focused around CHIM.

## CHIM Extensions
* This mod contains a number of quality of life improvements for CHIM.
### Action Registry (CHIM)
* All commands can be enabled or disabled in the MCM.
* All commands can be configured with cooldowns, with exponential backoff parameters. Effectively, this means that you can set a cooldown on a command, and have the cooldown increase every time that command is used, until enough time passes for the cooldown to reset back to base values.
### Sapience
* This mod exposes an option allowing you to dynamically enable / disable AI for all actors near the player. This generally means that you can just walk up to any NPC in the world, and start interacting with them seamlessly. This also implements radiant dialogue (Similar to the mantella feature), except more powerful / less buggy.
### CHIM-Specific Configuration Options:
(found in File Explorer: \\wsl.localhost\DwemerAI4Skyrim3\var\www\html\HerikaServer\ext\minai_plugin\config.php)
(You can easily access this folder by running the tools/AI-FF Plugins Folder file in your DwemerAI4Skyrim3 directory)
* force_voice_type = Force the voice type sent to xtts to be the NPC's base voice type. This is useful for compatibility with Mantella's xtts server.
* disable_nsfw = Globally disable all NSFW functionality.
* restrict_nonfollower_functions = By default, CHIM will have all of its actions available to all NPC's. This option disables functions that make sense only for followers, when interacting with non-follower NPC's.
* stop_narrator_context_leak = Prevent companions from being aware of what the narrator has said recently.
* always_enable_functions = Enable functions during rechat. Fun for expanded party play.
* PROMPT_HEAD_OVERRIDE = Override the prompt head for all profiles in a single place.
* force_CHIM_name_to_ingame_name = Force the CHIM player name to match the player's in-game name for compatibility with mods that change the player's name.
* commands_to_purge = Remove any CHIM commands that you don't like. Removes "TakeASeat" by default.
* xtts_server_override = Set the XTTS server in one place. Useful if you have to update this often for use with runpods.
* disable_worn_equipment = Disable the worn equipment system, and fall back to keyword based equipment awareness.
* A full list of up-to-date options that can be configured can be found [here](https://github.com/MinLL/MinAI/blob/main/minai_plugin/config.php)

### Sunhelm
* Players can now request food (A full sunhelm meal) to be served to them by servers or innkeepers.
* The AI will be aware of the player's general hunger, thirst, and fatigue levels.

### Dirt And Blood
* The AI will be aware of the player's personal cleanliness, and to what degree they are covered in blood. Does not include clothing.

### Nether's Follower Framework (NFF)
* Allows the player to order followers to start / stop looting the nearby area.
* This will be expanded greatly in the future to provide further integrations.

### General
* Players can carry out a number of routine vanilla interactions through natural dialogue. Currently supported integrations are:
  * Renting a room from innkeepers
  * Arranging for carriage rides to any location
  * Receiving training in skills from NPC's

### NSFW
This mod enables a number of optional [nsfw](https://github.com/MinLL/MinAI/blob/main/nsfw.md) integrations that are disabled by default. These will not effect your game unless you have the nsfw mods installed.

# Installation
## Hard Requirements
* This mod requires a functional installation of either CHIM, Mantella, or both (and their respective dependencies). DO THIS FIRST and seek assistance in those forums. When you are up and running well, return here and continue installation as follows:
* Papyrus Tweaks is required. Install the version appropriate for your game (SE/AE/VR).
* Requires the latest version of JContainers.
* Requires the latest version of Papyrus Extender.

## Soft Requirements
* See the features section. All supported mods are soft requirements. 
* Sapience requires SPID in order to function.

## Installation Steps (CHIM)
* Download and install this mod through your mod organizer of choice.
* Copy the minai_plugin folder to your Herika Server plugins directory (this is in your wsl VM, under This PC in File Explorer). You can easily access this folder by running the "tools/AI-FF Plugins Folder" file in your DwemerAI4Skyrim3 directory.
* After installing the plugin, run the CHIM Server Update file that came with DwemerDistro.
* In the web UI for CHIM, validate that the plugin has loaded by clicking "Plugins" on the top right.
* Navigate to the configuration page for MinAI (From the plugins page), and configure the mod to your liking.

## Installation Steps (MANTELLA)
* Download and install this mod's archive through your mod organizer of choice.
* Use the Mantella Web Interface to configure the prompts for this mod (main, multi-npc, and radiant). I ship two sets of prompts: A very kinky set for submissive female characters in the example configuration, and a more vanilla set in vanilla_prompts.txt. If you're not sure which to use, I'd suggest using the vanilla prompts. Replace the skyrim, multi-npc, and radiant prompts with the ones provided by this mod.
* In the Mantella Web Interface under Other, set the "Max Count Events" setting to a minimum of 15. I use 50 with the full set of integrations.
* (Recommended, Optional) Enable Radiant Dialogue in Mantella's MCM setting. This has a lot of very good and fun interactions when combined with this mod.
* (Optional) If you want a specific character to roleplay in a specific manner, or have a specific personality, edit the skyrim_characters.csv file that ships with Mantella to update that character's bio.
* (Optional) Change the language model that's being used. I am using nousresearch/hermes-3-llama-3.1-70b, and have had good results with it. Feel free to try other models though, and share your experience!

# Known Issues
* The AI can be unpredictable at times. This is due to the nature of using an LLM. Refining the prompt, or customizing the personality of the npc you're interacting with can help with this.
* Sometimes the AI refuses to use the keywords that trigger events (Such as -teaseweak-). If this happens, remind the NPC to use the keywords. This is much more of an issue with Mantella than CHIM, where this issue does not really happen much.
