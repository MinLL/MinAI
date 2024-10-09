# MinAI Modder's Guide
MinAI exposes an API to allow mods to easily integrate with AIFF / MinAI without having to write a server plugin, and simplifies the process of making your mod compatible with AI. Currently there are four mod events exposed to facilitate this, which you can send to MinAI.

## MinAI_RegisterEvent
This mod event is used to let the LLM know that an event has happened in game. It will be included in the context, and the LLM will be aware of it / possibly incorporate it into future responses. This does not prompt the LLM to respond immediately.

### Parameters
eventLine is a string containing the event that happened in game. For example, "Min set up a tent.".
For eventType, several are available, depending on what you are trying to convey:
* chat: Indicates that the player has said something in-game.
* info: Indicates that an event has occurred in--game.
```
; Inform the LLM that something has happened, without requesting the LLM to respond immediately.
; int handle = ModEvent.Create("MinAI_RegisterEvent")
;  if (handle)
;    ModEvent.PushString(handle, eventLine)
;    ModEvent.PushString(handle, eventType)
;    ModEvent.Send(handle)
;  endIf
Event OnRegisterEvent(string eventLine, string eventType)
  Info("OnRegisterEvent(" + eventType + "): " + eventLine)
  RegisterEvent(eventLine, eventType)
EndEvent
```

## MinAI_RequestResponse
This mod event is used to let the LLM know that an event has happened in game. It will be included in the context, and the LLM will be prompted to immediately react to it.

### Parameters
eventLine is a string containing the event that happened in game. For example, "Min set up a tent.".
For eventType, several are available, depending on what you are trying to convey:
* chat: Indicates that the player has said something in-game.
* chatnf: Indicates that the player has said something in-game (And requests a response to this)
* info: Indicates that an event has occurred in--game.
targetName is the name of the NPC that should respond. Specify this as "everyone" if you don't want a response from anyone in particular.

```
; Inform the LLM that something has happened, and request a specific actor to respond.
; Use "everyone" for targetName if you don't want a specific response.
; int handle = ModEvent.Create("MinAI_RequestResponse")
;  if (handle)
;    ModEvent.PushString(handle, eventLine)
;    ModEvent.PushString(handle, eventType)
;    ModEvent.PushString(handle, targetName)
;    ModEvent.Send(handle)
;  endIf
Event OnRequestResponse(string eventLine, string eventType, string targetName)
  Info("OnRequestResponse(" + eventType + " => " + targetName + "): " + eventLine)
  RequestLLMResponse(eventLine, eventType, targetName)
EndEvent
```

## MinAI_RequestResponseDialogue
This mod event is used to let the LLM know that an actor has spoken in game. It will be included in the context, and the LLM will be prompted to immediately react to it.

### Parameters
* eventLine is a string containing the event that happened in game. For example, "Min set up a tent.".
* speakerName is a string containing the name of the actor that spoke.
* targetName is the name of the NPC that should respond. Specify this as "everyone" if you don't want a response from anyone in particular.

```
; Inform the LLM that an actor has spoken, and request a specific actor to respond.
; Use "everyone" for targetName if you don't want a specific response.
; int handle = ModEvent.Create("MinAI_RequestResponseDialogue")
;  if (handle)
;    ModEvent.PushString(handle, speakerName)
;    ModEvent.PushString(handle, eventLine)
;    ModEvent.PushString(handle, targetName)
;    ModEvent.Send(handle)
;  endIf
Event OnRequestResponseDialogue(string speakerName, string eventLine, string targetName)
  Info("OnRequestResponse(" + speakerName + " => " + targetName + "): " + eventLine)
  RequestLLMResponseNPC(speakerName, eventLine, targetName)
EndEvent
```

## MinAI_SetContext
This mod event is used to set persistent context to be included in all future prompts. This is useful if there is some mod state that you want to always be exposed. For example, you could use this to implement LLM awareness of the player's current arousal level, hunger, and so forth.


Subsequent calls to this mod event with the same mod name / key will update the value with whatever you specify. For example, if you had a key "hunger", you could set this to "Min is Very hungry", "Min is not hungry at all", etc, depending on your mod's own internal state.

### Parameters
* modName is a string containing the name of the mod that owns this context. This should be the name of your mod.
* eventKey is a string containing the name of the event. Use this to differentiate between different sets of context you want to expose. For example, you might have one named "arousal", and one named "thirst".
* eventValue is a string containing the text you want included in the context. This should provide whatever description you want the LLM to be aware of. For example, "Min is very hungry.".
* TTL is how long this (in seconds) this event should be shown in the context after it is registered. Set this to 0 to immediately disable your event. Set this to a large value to avoid it expiring if you want it to be persistent.

```
; Set persistent context to be included in every LLM request until TTL expires.
; int handle = ModEvent.Create("MinAI_SetContext")
;  if (handle)
;    ModEvent.PushString(handle, modName)
;    ModEvent.PushString(handle, eventKey)
;    ModEvent.PushString(handle, eventValue)
;    ModEvent.PushInt(handle, ttl)
;    ModEvent.Send(handle)
;  endIf
Event OnSetContext(string modName, string eventKey, string eventValue, int ttl)
  Info("OnSetContext(" + modName + " => " + eventKey + " (TTL: " + ttl + ")): " + eventValue)
  if bHasAIFF
    minAIFF.StoreContext(modName, eventKey, eventValue, ttl)
  elseif bHasMantella
    ; Not persistent, but better than nothing for mantella users
    RegisterEvent(eventValue, "info_context")
  endif
EndEvent
```

## MinAI_RegisterAction
This mod event is used to expose new actions that you want the LLM to be able to execute. In short, you can provide instructions to the LLM on how to use your action, and it will invoke it, ultimately triggering a callback within your papyrus code.

```
; Register an action
; int handle = ModEvent.Create("MinAI_RegisterAction")
;  if (handle)
;    ModEvent.PushString(handle, actionName) ; Cannot contain spaces! Example, "SitDown".
;    ModEvent.PushString(handle, actionPrompt)
;    ModEvent.PushString(handle, mcmDescription)
;    ModEvent.PushString(handle, targetDescription)
;    ModEvent.PushString(handle, targetEnum)
;    ModEvent.PushInt(handle, enabled)
;    ModEvent.PushFloat(handle, cooldown)
;    ModEvent.PushInt(handle, ttl)
;    ModEvent.Send(handle)
;  endIf
Event OnRegisterAction(string actionName, string actionPrompt, string mcmDescription, string targetDescription, string targetEnum, int enabled, float cooldown, int ttl)
  Info("OnRegisterAction(" + actionName + " => " + enabled + " (Cooldown: " + cooldown + ")): " + actionPrompt)
  if bHasAIFF
		minaiff.RegisterAction("ExtCmd"+actionName, actionName, mcmDescription, "External", enabled, cooldown, 2, 5, 300, true)
    minaiff.StoreAction(actionName, actionPrompt, enabled, ttl, targetDescription, targetEnum)
  elseif bHasMantella
    ; Nothing to do for mantella.
  endif
EndEvent
```

### Example
```
Function RegisterTestAction(int enabled)
  int handle = ModEvent.Create("MinAI_RegisterAction")
  if (handle)
    ModEvent.PushString(handle, "testaction") ; The name of the action. Must not contain spaces.
    ModEvent.PushString(handle, "Use the test action") ; Instructions for the LLM on how to use your action.
    ModEvent.PushString(handle, "Test Action Description") ; The description for your action in the MCM.
    ModEvent.PushString(handle, "Target (Actor, NPC)") ; The type of target for your action. Leave empty if not applicable.
    ModEvent.PushString(handle, "my,list,of,targets") ; The list of targets for your action. Leave empty if not applicable. 
    ModEvent.PushInt(handle, enabled)
    ModEvent.PushFloat(handle, 5) ; 5 second cooldown
    ModEvent.PushInt(handle, 1200) ; 1200 second TTL
    ModEvent.Send(handle)
  endIf
EndFunction

Function Maintenance()
  RegisterTestAction(1) ; Enable the action for the LLM.
  ; After registering an action, you would register for a modevent to be informed when the command is executed (Like such)
  RegisterForModEvent("AIFF_CommandReceived", "CommandDispatcher")
EndFunction

; speakerName is the NPC that triggered your action.
; Command is the function name.
; Parameter is the target that the LLM selected for the action.
Event CommandDispatcher(String speakerName,String  command, String parameter)
  Actor akActor = AIAgentFunctions.getAgentByName(speakerName)
  if (command=="ExtCmdtestaction")
    ; Do the thing here.
  EndIf
EndEvent
```
