# MinAI
A significant expansion to CHIM that brings AI to the entirety of the Skyrim world, and bridges LLMs with various Skyrim Mods. 

Documentation for modders can be found [here](https://github.com/MinLL/MinAI/blob/main/ModdersGuide.md). FAQ can be found [here](https://github.com/MinLL/MinAI/blob/main/FAQ.md).

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/S6S51B7MJA)
 
## Description

MinAI is a comprehensive enhancement to CHIM that transforms Skyrim into a living, breathing world where every NPC possesses true artificial intelligence. This mod serves as a sophisticated interface between Large Language Models (LLMs) and Skyrim's vast ecosystem of mods and vanilla features, enabling unprecedented levels of dynamic interaction and roleplay.

### Core Features
* **Universal NPC Intelligence**: Every NPC in the world can be interacted with naturally through conversation
* **Dynamic Roleplay Scenarios**: Completely unscripted, AI-generated interactions that adapt to your character, the world state, and NPC personalities
* **Seamless Integration**: Deep integration with popular Skyrim mods and vanilla features
* **Contextual Awareness**: NPCs understand and react to their environment, relationships, and world state
* **Natural Dialogue**: Fully voiced conversations that feel natural and contextual
* **Dungeon Master Tools**: Create custom scenarios and guide NPC behavior through voice or text input
* **Smart Diary System**: Dynamic diary updates that reflect your adventures and relationships

### Technical Highlights
* **Modular Architecture**: Highly maintainable and extensible codebase
* **Enhanced Context System**: Sophisticated context management that provides rich, relevant information to the LLM
* **Action Framework**: Flexible system for registering and managing NPC actions
* **Configurable Integration**: Each feature can be enabled/disabled through the MCM menu

### World Integration
* **Environmental Awareness**: NPCs understand weather, time of day, location, and surroundings
* **Relationship Dynamics**: Complex relationship tracking between NPCs and the player
* **Crime System**: Sophisticated bounty and crime tracking across all holds
* **Item Management**: Natural item exchange and trading through conversation
* **Survival Systems**: Integration with popular survival mods for realistic needs

### NSFW
* This mod enables a number of optional [nsfw](https://github.com/MinLL/MinAI/blob/main/nsfw.md) integrations that are disabled by default. These will not effect your game unless you have the nsfw mods installed.


Due to utilizing an LLM, interactions can be unpredictable at times. The AI may occasionally be uncooperative or unreasonable, but it generally provides engaging and immersive experiences. There are no predefined scenarios - everything is generated dynamically based on your world state, NPC personalities, and the LLM's contextual understanding.

This mod requires an openrouter.ai account or local model setup. We recommend using openrouter over local models, as it provides better results even compared to dedicated hardware setups.

## Features and Currently Supported Integrations

## CHIM Extensions
* This mod contains a number of quality of life improvements for CHIM.
### Action Registry (CHIM)
* All commands can be enabled or disabled in the MCM.
* All commands can be configured with cooldowns, with exponential backoff parameters. Effectively, this means that you can set a cooldown on a command, and have the cooldown increase every time that command is used, until enough time passes for the cooldown to reset back to base values.

### Roleplay Assistant
* This system can optionally preprocess player messages to better match their character's roleplay style
* Can transform casual modern speech into lore-appropriate Skyrim dialogue that will automatically adapt to the character's personality, roleplay, and the situation.
* Allows players to prompt their character to respond to situations without explicit direction
* Uses the summary connector from either the narrator profile (if enabled) or the default profile
* Examples of transformations:
  * "Min: What the fuck?" → "Min: By the Nine, what in Oblivion is this?!"
  * "Min: I'm really tired" → "Min: These weary bones could use some rest at an inn..."
  * "Min: That's awesome!" → "Min: By Ysmir, that's incredible!"

### Sapience
* This mod exposes an option allowing you to dynamically enable / disable AI for all actors near the player. This generally means that you can just walk up to any NPC in the world, and start interacting with them seamlessly. This also implements radiant dialogue, except more powerful / less buggy.

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

### Gourmet
* NPCs are aware of the player's intoxication state from alcohol consumption
* NPCs can detect various alcohol effects including damage/fortify effects on magicka and stamina
* NPCs are aware of drug effects including Skooma, Eversnow, and Sleeping Tree Sap
* Tracks both beneficial and harmful effects from substances
* Integrates with visual effects to enhance roleplay around substance use

### Requiem
* NPCs can detect alcohol intoxication from Requiem's effects
* NPCs are aware of Skooma usage through Requiem's drug system
* Seamlessly integrates with Gourmet's effects when both mods are present

### Dirt And Blood
* The AI will be aware of the player's personal cleanliness, and to what degree they are covered in blood. Does not include clothing.
* The NPCs are aware of their own dirtiness, though calibrated for the norms of Skyrim where clean people are nobles and elites.
* With More Soaps extension, NPCs will be aware of your fragrence.

### Nether's Follower Framework (NFF)
* Allows the player to order followers to start / stop looting the nearby area.
* This will be expanded greatly in the future to provide further integrations.

### Frostfall
* The NPCs are aware of the temprature of the environment, if the player looks cold, how warmly people are dressed, and if someone is wet.
* The NPCs know if you are near a fire, and if you need to be due to the cold. 
* NPCs know if someone is nearing frostbite or has it, or if someone is nearing death in the cold.

### Environmental Awareness
* NPCs know if they have been or could be intimidated by the player. They'll remember the player having used intimidation against them. Maybe they keep it a secret. 
* NPCs know each other's occupations, except that shady occupations disguise themselves. Who is a merchant or a blacksmith is obvious from clear social cues to the people who live there.
* NPCs will know who amongst themselves and the player is wearing cheap or expensive clothing.
* NPCs are informed about what they can readily see regarding characters around them: if someone is short, tall, and if they match up against the player in a fight. 
* NPCs are aware of each other's shoes, clothing, race, and symbols of cultural allegiance like shields with icons.
* NPCs know who is riding a horse, swimming, sleeping and sitting.
* Added time of day descriptors like "noon", "dawn", "dead of night" to help the LLM have more natural dialog like "are you enjoying the sunset?" 

### Item Management System
* NPCs can dynamically give items or gold to the player through natural conversation
* NPCs can request and take items from the player's inventory
* The system intelligently handles cases where NPCs don't have enough of an item to give or when players don't have requested items
* NPCs are aware of both their own inventory contents and the player's inventory (with reasonable limits)
* Common uses include:
  * NPCs giving quest items, rewards, or gifts to the player
  * NPCs requesting payment, quest items, or supplies from the player
  * Trading specific items between NPCs and the player
  * Merchants, vendors, and general NPCs can all participate in item exchanges

### NAT
* NAT weathers are supported and recognized by the LLM (In addition to the general weather system).

### General
* NPCs will know their Skyrim relationship rank with Player
* Players can carry out a number of routine vanilla interactions through natural dialogue. Currently supported integrations are:
  * Renting a room from innkeepers
  * Arranging for carriage rides to any location
  * Receiving training in skills from NPC's

### Enhanced Context System
* Completely overhauled context system with modular and maintainable architecture
* New system for registering context elements that can be individually enabled/disabled
* Context sections are logically organized with clear headers for LLM clarity
* Strategic context distribution to avoid inappropriate RP from strangers
* New context preview feature in config.html for narrator, NPC->player, and NPC->NPC perspectives
* Context sections include:
  * Physical descriptions and equipment
  * Character states (arousal, fertility, following status)
  * Survival metrics (hunger, thirst, temperature)
  * Environmental details (weather, location, time)
  * Relationship dynamics and power structures
  * Crime and bounty information
  * Mental states and influences

### Performance Improvements
* Reduced CHIM server/database load by 50-80%
* Optimized context system for better LLM processing
* Added context slop cleanup feature (experimental)
* Improved action usage across all models

### Item Management System
* NPCs can dynamically give items or gold to the player through natural conversation
* NPCs can request and take items from the player's inventory
* The system intelligently handles cases where NPCs don't have enough of an item to give or when players don't have requested items
* NPCs are aware of both their own inventory contents and the player's inventory (with reasonable limits)
* Common uses include:
  * NPCs giving payment, rewards, or gifts to the player
  * NPCs requesting payment, paying bounties, or supplies from the player
  * Trading specific items between NPCs and the player
  * Merchants, vendors, and general NPCs can all participate in item exchanges
  
### Crime System
* Guards throughout Skyrim are aware of the player's bounties in their hold and sometimes in other holds
* Higher-ranking guards (captains, commanders, officers) are more likely to know about bounties in other holds
* During conversations, guards can:
  * Add small bounties for minor infractions (trespassing, petty theft, disrespect)
  * Add medium bounties for moderate crimes (assault, significant theft, property damage)
  * Add large bounties for serious crimes (murder, attacking guards, major theft)
  * Arrest the player for their crimes, sending them directly to jail
  * Clear the player's bounty in their hold (through persuasion, bribery, or other means)
* The Narrator / Self Narrator has detailed bounty information showing all holds where the player is wanted
* The system automatically detects which hold's jurisdiction the guard belongs to

# MinAI Diary Hotkey Feature

## Overview

This feature adds a new hotkey to MinAI that allows players to update diaries on demand. The hotkey's behavior changes based on the player's current state:

1. **When crouching**: Updates only the narrator's diary
2. **When standing and not looking at an NPC**: Updates all follower diaries and the narrator's diary (similar to the sleep update)
3. **When looking at an NPC**: Updates only that specific NPC's diary

## Benefits

- **Convenience**: No need to wait for sleep to update diaries
- **Targeted updates**: Update specific NPC diaries without updating all of them
- **Narrator focus**: Quickly update just the narrator's diary when needed
- **Immersive**: The crouching mechanic provides an immersive way to "focus" on the narrator


## Usage

1. Set the diary hotkey in the MCM menu under the "General" tab
2. Use the hotkey in different situations:
   - Crouch and press the hotkey to update the narrator's diary
   - Stand normally and press the hotkey to update all diaries
   - Look at an NPC and press the hotkey to update just that NPC's diary

# MinAI Dungeon Master Hotkeys

## Overview

This feature adds two new hotkeys to MinAI that allow players to act as a "dungeon master" and introduce events or information to the game world:

1. **Dungeon Master Voice**: Record voice input or send a generic event prompt
2. **Dungeon Master Text**: Type a custom message to send to an NPC or The Narrator

The dungeon master's messages are treated as authoritative information about the game world, allowing players to create custom scenarios and guide NPC responses.

## Benefits

- **Enhanced Storytelling**: Introduce custom events, challenges, or plot developments
- **World Building**: Add details to the game world that NPCs will acknowledge and respond to
- **Roleplaying**: Create more dynamic and interactive roleplaying scenarios
- **NPC Direction**: Guide NPC behavior by providing them with new information
- **Scenario Creation**: Set up custom scenarios without needing to modify the game
- **Flexible Narration**: Send messages to specific NPCs or to The Narrator for general world events

## Usage

1. Set the dungeon master hotkeys in the MCM menu under the "General" tab
2. For targeted messages to an NPC:
   - Look at the NPC you want to send a message to
3. For general narration:
   - Don't target any NPC (look at empty space or objects)
   - The message will be sent to The Narrator
4. For voice input:
   - Press and hold the Dungeon Master Voice key to record your message
   - Release quickly to send a generic event prompt without recording
5. For text input:
   - Press the Dungeon Master Text key
   - Type your message in the text entry box that appears
   - The message will be sent to the targeted NPC or The Narrator

## Examples

Here are some examples of how you can use the dungeon master feature:

### When targeting an NPC:
- **Create Personal Challenges**: "You feel a strange magical effect that temporarily weakens your spells."
- **Add Character Background**: "You suddenly remember an important detail from your past."
- **Guide Roleplay**: "You notice something suspicious about the person across the room."

### When targeting The Narrator (no NPC targeted):
- **Create Weather Events**: "A sudden thunderstorm begins, with lightning striking nearby."
- **Introduce NPCs**: "A group of bandits appears on the road ahead."
- **Set Up Scenarios**: "A hidden treasure chest is visible behind the waterfall."
- **Create Atmosphere**: "The tavern is unusually crowded tonight, with many patrons celebrating."

# Installation
## Requirements
* This mod requires a functional installation of [CHIM](https://www.nexusmods.com/skyrimspecialedition/mods/126330) and its dependencies. DO THIS FIRST and seek assistance in those forums. When you are up and running well, return here and continue installation as follows:
* [Papyrus Tweaks NG](https://www.nexusmods.com/skyrimspecialedition/mods/77779).
* [JContainers SE](https://www.nexusmods.com/skyrimspecialedition/mods/16495).
* [powerofthree's Papyrus Extender](https://www.nexusmods.com/skyrimspecialedition/mods/22854). (VR: also install [Papyrus Extender VR](https://www.nexusmods.com/skyrimspecialedition/mods/58296))
* [Spell Perk Item Distributor (SPID)](https://www.nexusmods.com/skyrimspecialedition/mods/36869), if using MinAI's Sapience feature. (VR: [Spell Perk Item Distributor (SPID) VR](https://www.nexusmods.com/skyrimspecialedition/mods/59121))
* See the features section. All supported mods are soft requirements. 

## Installation Steps
* Download and install this mod through your mod organizer of choice.
* Use the CHIM plugin manager to install the plugin (Server Plugins -> Plugin Manager)
* Navigate to the configuration page for MinAI (From the plugins page), and configure the mod to your liking.

# Known Issues
* The AI can be unpredictable at times. This is due to the nature of using an LLM. Refining the prompt, or customizing the personality of the npc you're interacting with can help with this.

