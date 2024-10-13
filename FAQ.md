# Frequently Asked Questions

## Things aren't working at all! I keep seeing a message about first time setup complete every time I load the game.
This is caused by you not having all of the hard requirements installed (Particularly Papyrus Tweaks). Validate that you have them installed, and that you have up to date versions of them. If you did not have them, install them and then revert to a save prior to installing MinAI.

## The Action Registry is empty!
This is caused by you not having all of the hard requirements installed (Particularly Papyrus Tweaks). Validate that you have them installed, and that you have up to date versions of them. If you did not have them, install them and then revert to a save prior to installing MinAI.

## Actions aren't working!
NSFW actions are only enabled if you have either Sexlab or ostim installed. If you don't have either, most of the actions and context will not be injected. In order to troubleshoot this you should:
1) Validate that you have all of the requirements installed.
2) Validate that your character name in-game exactly matches your character name in the AI-FF UI.
3) Validate that the actions are showing up as enabled in the in-game MCM's Action Registry.
4) Look at your ai log, and look at the actions that are being sent to the LLM. In the list of actions, you should see actions from MinAI (Such as "AVAILABLE ACTION: Grope : Grope the target"). If you see actions here, it means that both the in-game plugin and server-side plugin are working. It's likely that the LLM is just deciding not to use the actions in this case. If you don't see the actions in the list, it means that they are not being exposed for the LLM. If they are not present, validate that you have the server plugin and the skyrim plugin both installed correctly, and look at your Papyrus log.
5) Look at your ai log, and look at the output from the LLM. There will be an "action" field in the response from the LLM. This is where the commands that the LLM executes will be specified. If the commands are not here, it means that the LLM is not sending them. If the commands are here and aren't working, you will need to examine your Papyrus log.
6) Enable Papyrus logging, and look in your log for errors related to MinAI. Anything coming from MinAI with the log level of "ERROR" or "FATAL" is probably why things are not working for you.

