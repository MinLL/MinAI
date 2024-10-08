# MinAI
Bridge between LLMs and various Skyrim Mods. Documentation for modders can be found [here](https://github.com/MinLL/MinAI/blob/main/ModdersGuide.md).
 
 ## Description

This mod serves as an interface bridging Large Language Models (LLM)'s with a number of Skyrim's mods (NSFW and not), as well as a number of vanilla features.. This mod's goal is to enable completely unscripted, dynamically generated, fully voiced roleplay (nsfw and otherwise) scenarios that integrate with in-game mechanics and features with supported mods.

Due to this utilizing an LLM, the results can be unpredictable. Sometimes the AI can be uncooperative or unreasonable. It works pretty well most of the time though. There are absolutely no predefined scenarios in this mod - Anything you encounter is generated based off of the circumstances in your world, the NPC's personality you're interacting with, and the LLM's whims.

This release should be considered a beta. It may be janky, have bugs, or strange NPC behavior from the LLM. I have tested this quite a bit over several play-throughs and it's been working well. I am looking for additional feedback on potential integrations. Due to the nature of this mod's integration, it is extremely unlikely to break your savegame in future updates, which will most likely always be backwards compatible.

This mod requires you to fund an openrouter.ai account, or run a model locally. The costs for leveraging the LLM are quite cheap - in 4 days of play while developing this mod, I spent less than $1.00 USD in credits - you should expect to pay a few cents for a given play-session. I highly recommend using openrouter instead of trying to run a model locally, as it requires a prohibitive amount of hardware to run locally. I had worse results using an rtx 3090 exclusively dedicated to the AI than just leveraging openrouter.

## Features and Currently Supported Integrations

### Mantella or AI Follower Framework
* Both Mantella and AIFF are supported by this mod as soft-dependencies. Run one or the other, or both at the same time. At the time of beta14 they have roughly equivalent feature parity (AIFF excels at some things like Sex scenes). As time goes on, AIFF will be focused on for development. Mantella's feature-set is effectively frozen.
* Update as of Beta32: The majority of this mod's more advanced features are built for AIFF at this point, due to limitations with the Mantella API. Mantella is still supported with it's current feature set.

## Action Registry (AIFF)
* All commands can be enabled or disabled in the MCM.
* All commands can be configured with cooldowns, with exponential backoff parameters. Effectively, this means that you can set a cooldown on a command, and have the cooldown increase every time that command is used, until enough time passes for the cooldown to reset back to base values.

## AIFF Extensions
* This mod contains a number of quality of life improvements for AIFF.
### AIFF-Specific Configuration Options:
(found in File Explorer: \\wsl.localhost\DwemerAI4Skyrim3\var\www\html\HerikaServer\ext\minai_plugin\config.php)
* force_voice_type = Force the voice type sent to xtts to be the NPC's base voice type. This is useful for compatibility with Mantella's xtts server.
* disable_nsfw = Globally disable all NSFW functionality.
* restrict_nonfollower_functions = By default, AIFF will have all of its actions available to all NPC's. This option disables functions that make sense only for followers, when interacting with non-follower NPC's.
* stop_narrator_context_leak = Prevent companions from being aware of what the narrator has said recently.
* always_enable_functions = Enable functions during rechat. Fun for kinky party play.
* PROMPT_HEAD_OVERRIDE = Override the prompt head for all profiles in a single place.
* force_aiff_name_to_ingame_name = Force the AIFF player name to match the player's in-game name for compatibility with mods that change the player's name.
* commands_to_purge = Remove any AIFF commands that you don't like. Removes "TakeASeat" by default.
* xtts_server_override = Set the XTTS server in one place. Useful if you have to update this often for use with runpods.
* disable_worn_equipment = Disable the worn equipment system, and fall back to keyword based equipment awareness.


### Sexlab and OStim:
* This mod will let the LLM know when Sex is happening, and what type of sex it is. The AI will be aware of other NPC's having sex, or the player having sex.
* Set commands for the LLM can optionally be set to a configurable arousal threshold for the NPC. If enabled, this means that they will only be willing to participate in / engage sex through the LLM if their arousal is over a configurable threshold.
* If the player can convince the NPC to do so, they will have sex with the player. NPC's can also convince eachother to have sex.
* NPC's may decide to masturbate, depending on their arousal levels (And dependent upon having a supported Arousal mod installed)
* Supports multiple actors - If more than one NPC in a conversation decides they want to have sex, an orgy will begin.
* Have natural dialogue during sex which influences the scene. For example, I tested starting a scene by giving a blowjob to an actor, and asked them to "put it inside of me", which caused them to switch to vaginal sex.
* AI's can decide to transition sex types on their own during an encounter. It's not uncommon for an AI to want to "finish on your chest" for example, as a female character. Depends on the personality of the AI.
* (AIFF) Actors have vastly improved awareness of sex scene details in AIFF over Mantella.
* (Sexlab, Optional) This mod ships with a json file for configuration of prompts that are injected at each stage of a given animation. This can be edited to improve AI awareness of what is happening during a given sex scene. See data/minai/sexlab_descriptions.json

## Worn Equipment Customization
* You can customize the LLM's awareness of any piece of equipment that you are wearing in order to control how it perceives / reacts to specific gear.
* Currently you can do this by accessing http://10.0.0.144:8081/HerikaServer/ext/minai_plugin/we_manager.html (Replace the IP address with the IP of your AIFF server). A link will be provided to this from within the plugins page in the future.
* If you want to use the old keyword based system instead, you can disable this system in config.php.

### CBPC
* This mod will connect the physics from CBPC to the LLM. The LLM will be aware of both where (breasts, ass, etc) and when the player touches themselves, or other actors, and will react to and comment on it. This works particularly well during sex, as the location the LLM will be aware of the location that the penis collides with. This requires solid CBPC configurations. This was tested with these: https://www.nexusmods.com/skyrimspecialedition/mods/82745?tab=description
* This can be further expanded by enabling additional colliders in your CBPC configuration. You could for example, enable detection when the head collides with the vagina, etc.
* In VR, this works particularly well, since you you can freely control your hands.
* This has integration with Devious Devices - The LLM will be aware if you try to touch yourself / others through a chastity belt, or other devices as an example.

### Devious Devices
* NPC's will be aware of, and react to all devices worn by eachoother, and the player.
* The AI can decide to remotely activate the player's vibrating piercings/plugs at 10 different levels of strength (1-5 tease only, 1-5 potential climax). 
* The AI can remotely shock the player using the standard DD functions.
* The AI can decide to reward the character with an orgasm, or edge her instead.
* The AI can detect when the player is edged, or has an orgasm through DD, and will react to it. I've had the AI penalize me for having an orgasm without permission in some scenarios.
* The AI can (Optionally) decide to lock certain devices on the player if enabled in the MCM.

### Sexlab Aroused + Keywords (Tawoba, Tewoba, Babodialogue)
* NPC's will be able to tell if they (or the player) are at heightened levels of arousal, and may incorporate it into their actions and responses.
* NPC's will be aware of, and react to all sexlab aroused keywords. I was getting some pretty amusing / interesting comments and reactions while wearing a set of Iron or Steel Bikini Armor from TAWOBA.
* The AI will sometimes sexually harass you if you're wearing skimpy armor. Spanking, groping, pinching nipples, etc.

### The New Gentlemen
* LLM will be aware of exposed penis sizes.
* Currently, auto assigned addons are finicky (to be addressed in a future TNG update). Manually assign a type and size to PC/NPC with TNG hotkey to ensure LLM awareness.

### Deviously Accessible
* Has support for the Telvanni quest from Deviously Accessible - If doing the quest, the Narrator will be replaced with the telvanni mage's personality. He will be aware of how well you've been doing, and of the dreams you've been having recently.
* Has WIP support for the Eldritch Horror quest from Deviously Accessible as another alternative narrator.

### Devious Followers
* The AI will be aware that it is a devious follower, and will roleplay as such. It will try to tease you and trick you.
* The AI is aware of how much gold the player currently owes, and if they are in debt. The further in debt the player is, the stricter the AI becomes.
* The AI is aware of how many days are left on the player's contract.
* The AI is aware of all deals that are active, and will incorporate them into its behavior / responses. For example, if you have the "ask for spanks daily rule", the AI will actually make you do this.  See the screenshots for a completely unscripted scenario of exactly that happening in my game. Not all rules have this same level of integration / support, this is WIP as I play through the content, I will continue adding more.
* Skooma rule integration. If under the skooma rule, you can ask your devious follower for drugs, and he will provide them.
* The Devious Follower is very fond of remotely activating piercings / plugs (This varies depending on the personality of the NPC you chose as your follower), and generally harassing the player.
* The Devious Follower will present new rules to you, and actively negotiate them with you when your debt is high. If you verbally agree to the deal, it will be accepted by the devious followers ramework.

### Spank That Ass
* The AI will utilize Spank That Ass to spank the player's ass or tits if they want to do so. Note: This currently requires Devious Followers in order to be enabled.

### Sexlab Approach
* The AI will utilize Sexlab Approach to kiss or hug the player if they want to do so.

### Sexlab Horrible Harassment
* The AI will utilize Horrible Harassment to molest the player if they want to do so.

### Sunhelm
* Players can now request food (A full sunhelm meal) to be served to them by servers or innkeepers.
* The AI will be aware of the player's general hunger, thirst, and fatigue levels.

## Nether's Follower Framework (NFF)
* Allows the player to order followers to start / stop looting the nearby area.
* This will be expanded greatly in the future to provide further integrations.

### General
* The player can convince NPC's to trade with them, dress, or undress.
* NPC's can grope the player if they want. This event needs to be expanded. It's pretty simple at the moment.
* NPC's can pinch the player's nipples if they want. This event needs to be expanded. It's pretty simple at the moment.
* Players can carry out a number of routine vanilla interactions through natural dialogue. Currently supported integrations are:
  * Renting a room from innkeepers
  * Arranging for carriage rides to any location
  * Receiving training in skills from NPC's
  

### Game Interface for Toys (GIFT)
* Every appropriate interaction that the AI can do is picked up by GIFT, and can be configured to vibrate, etc.

# Installation
## Hard Requirements
* This mod requires a functional installation of either AI Follower Framework, Mantella, or both (and their respective dependencies). DO THIS FIRST and seek assistance in those forums. When you are up and running well, return here and continue installation as follows:
* Papyrus Tweaks is required. Install the version appropriate for your game (SE/AE/VR).

## Soft Requirements
* See the features section. All supported mods are soft requirements. Highly suggest having at a minimum either OStim or Sexlab.

## Installation Steps (AI Follower Framework)
* Download and install this mod through your mod organizer of choice.
* Copy the minai_plugin folder to your Herika Server plugins directory (this is in your wsl VM, under This PC in File Explorer). You can easily access this folder by running the tools/AI-FF Plugins Folder file in your DwemerAI4Skyrim3 directory.
* In the web UI for AIFF, validate that the plugin has loaded by clicking "Plugins" on the top right.
* Customize the prompt to your liking. This version of MinAI is much less prescriptive and heavy-handed on what the prompt needs to be.
* (Optional) In the Herika Server (wsl) Plugins Folder - minai_plugin/config.php, enable or disable "force_voice_type" for Mantella XTTS compatibility.
* (Optional) Configure any other options in minai_prompt/config.php to your liking.

## Installation Steps (MANTELLA)
* Download and install this mod's archive through your mod organizer of choice.
* Use the Mantella Web Interface to configure the prompts for this mod (main, multi-npc, and radiant). I ship two sets of prompts: A very kinky set for submissive female characters in the example configuration, and a more vanilla set in vanilla_prompts.txt. If you're not sure which to use, I'd suggest using the vanilla prompts. Replace the skyrim, multi-npc, and radiant prompts with the ones provided by this mod.
* In the Mantella Web Interface under Other, set the "Max Count Events" setting to a minimum of 15. I use 50 with the full set of integrations.
* (Recommended, Optional) Enable Radiant Dialogue in Mantella's MCM setting. This has a lot of very good and fun interactions when combined with this mod.
* (Optional) If you want a specific character to roleplay in a specific manner, or have a specific personality, edit the skyrim_characters.csv file that ships with Mantella to update that character's bio.
* (Optional) Change the language model that's being used. I am using nousresearch/hermes-3-llama-3.1-70b, and have had good results with it. Feel free to try other models though, and share your experience!

# Known Issues
* The AI can be unpredictable at times. This is due to the nature of using an LLM. Refining the prompt, or customizing the personality of the npc you're interacting with can help with this.
* Sometimes the AI refuses to use the keywords that trigger events (Such as -teaseweak-). If this happens, remind the NPC to use the keywords. This is much more of an issue with Mantella than AIFF, where this issue does not really happen much.
