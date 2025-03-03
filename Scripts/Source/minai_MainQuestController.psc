ScriptName minai_MainQuestController extends Quest

GlobalVariable minai_WhichAI
actor playerRef

; AI
minai_Mantella minMantella
minai_AIFF minAIFF

; Modules
minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious
minai_VR vr
minai_Config config
minai_Followers followers
minai_CombatManager combat
minai_SapienceController sapience
minai_Reputation reputation  
minai_DirtAndBlood dirtAndBlood
minai_EnvironmentalAwareness envAwareness
minai_Util MinaiUtil  
Spell minai_ToggleSapienceSpell
minai_FertilityMode fertility

bool bHasMantella = False;
bool bHasAIFF = False;
float lastRequestTime
actor[] nearbyAI
bool Property PlayerInCombat auto
int Property currentVersion Auto

Event OnInit()
  Maintenance()
EndEvent

Int Function GetVersion()
  return 122
EndFunction

Function CheckForCriticalDependencies()
  Info("Checking for critical dependencies...")
  int[] pTweaksVersion = PapyrusTweaks.GetPapyrusTweaksVersion()
  if !pTweaksVersion
    Fatal("Papyrus Tweaks NG is not installed. This is a critical dependency. Revert to a prior save and install the mod before continuing.")
  Else
    Info("Papyrus Tweaks NG detected: " + pTweaksVersion[0] + "." + pTweaksVersion[1] + "." + pTweaksVersion[2])
  EndIf
EndFunction

Function Maintenance()
  playerRef = game.GetPlayer()
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  if !config
    Fatal("Could not load configuration - script version mismatch with esp")
  EndIf
  Info("Maintenance() - minai v" +GetVersion() + " initializing.")
  CheckForCriticalDependencies()
  ; Set keybinds
  SetSapienceKey()
  SetNarratorKey()
  SetSingKey()
  SetNarratorTextKey()
  SetRoleplayKey()
  SetRoleplayTextKey()
  ; Register for Mod Events
  ; Public interface functions
  RegisterForModEvent("MinAI_RegisterEvent", "OnRegisterEvent")
  RegisterForModEvent("MinAI_RequestResponse", "OnRequestResponse")
  RegisterForModEvent("MinAI_RequestResponseDialogue", "OnRequestResponseDialogue")
  RegisterForModEvent("MinAI_SetContext", "OnSetContext")
  RegisterForModEvent("MinAI_SetContextNPC", "OnSetContextNPC")      
  RegisterForModEvent("MinAI_RegisterAction", "OnRegisterAction")
  RegisterForModEvent("MinAI_RegisterActionNPC", "OnRegisterActionNPC")
  
  Info("Checking for installed mods...")

  minai_WhichAI = Game.GetFormFromFile(0x0907, "MinAI.esp") as GlobalVariable
  minMantella = (Self as Quest) as minai_Mantella
  minAIFF = (Self as Quest) as minai_AIFF
  sex = (Self as Quest) as minai_Sex
  survival = (Self as Quest) as minai_Survival
  arousal = (Self as Quest) as minai_Arousal
  devious = (Self as Quest) as minai_DeviousStuff
  fertility = (Self as Quest) as minai_FertilityMode
  vr = Game.GetFormFromFile(0x090E, "MinAI.esp") as minai_VR
  followers = Game.GetFormFromFile(0x0913, "MinAI.esp") as minai_Followers
  combat = (Self as Quest) as minai_CombatManager
  sapience = Game.GetFormFromFile(0x091D, "MinAI.esp") as minai_SapienceController
  reputation = (Self as Quest) as minai_Reputation
  MinaiUtil = (Self as Quest) as minai_Util
  MinaiUtil.Maintenance()
  dirtAndBlood = (Self as Quest) as minai_DirtAndBlood
  envAwareness = (Self as Quest) as minai_EnvironmentalAwareness
  minai_ToggleSapienceSpell = Game.GetFormFromFile(0x0E93, "MinAI.esp") as Spell
  if (!followers)
    Fatal("Could not load followers script - Mismatched script and esp versions")
  EndIf
  
  bHasMantella = (Game.GetModByName("Mantella.esp") != 255)
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  
  ;; Initialize AIFF first so that the action registry is initialized
  if bHasAIFF
    minAIFF.Maintenance(Self)
    minAIFF.SetActorVariable(playerRef, "isSinging", false)
    minAIFF.SetActorVariable(playerRef, "isTalkingToNarrator", false)
  EndIf
  dirtAndBlood.Maintenance(Self)
  envAwareness.Maintenance(Self)
  sex.Maintenance(Self)
  survival.Maintenance(Self)
  arousal.Maintenance(Self)
  devious.Maintenance(Self)
  fertility.Maintenance(Self)
  vr.Maintenance(Self)
  followers.Maintenance(Self)
  combat.Maintenance(Self)
  sapience.Maintenance(Self)
  reputation.Maintenance(Self)
  
  if bHasMantella
    minMantella.Maintenance(Self)
  EndIf
  if bHasAIFF
    if (!minAIFF.IsInitialized())
      Debug.Notification("MinAI - First time setup complete. Save/reload to enable mod functionality")
      minAIFF.SetInitialized()
    Else
      nearbyAI = minAIFF.GetNearbyAI()
    EndIf
    minAIFF.ResetAllActionBackoffs()
  EndIf
  lastRequestTime = 0.0
  currentVersion = GetVersion()
  Info("Initialization complete.")
EndFunction




Function RegisterAction(String eventLine)
  ;Debug("RegisterAction(" + eventLine + ")")
  if bHasMantella
    minMantella.RegisterAction(eventLine)
  EndIf
EndFunction

Function RegisterEvent(String eventLine, string eventType = "")
  ;Debug("RegisterEvent(" + eventLine + ", " + eventType + ")")
  if bHasMantella
    minMantella.RegisterEvent(eventLine)
  EndIf
  if bHasAIFF
    if eventType == ""
      eventType = "info_sexscene"
    EndIf
    minAIFF.RegisterEvent(eventLine, eventType)
  EndIf
  
EndFunction


Function RequestLLMResponse(string eventLine, string eventType)
  if bHasAIFF
    float currentTime = Utility.GetCurrentRealTime()
    if currentTime - lastRequestTime > config.requestResponseCooldown
      lastRequestTime = currentTime
      Info("Requesting response from LLM: " + eventLine)
      minAIFF.AIRequestMessage(eventLine, eventType)
    Else
      RegisterEvent(eventLine, eventType)
    EndIf
  elseif bHasMantella
    RegisterEvent(eventLine, eventType)
   EndIf
EndFunction


Function RequestLLMResponseFromActor(string eventLine, string eventType, string name)
  if bHasAIFF
    float currentTime = Utility.GetCurrentRealTime()
    if currentTime - lastRequestTime > config.requestResponseCooldown
      lastRequestTime = currentTime
      Info("Requesting response from LLM: " + eventLine)
      minAIFF.AIRequestMessageForActor(eventLine, eventType, name)
    Else
      RegisterEvent(eventLine, eventType)
    EndIf
  elseif bHasMantella
    RegisterEvent(eventLine, eventType)
   EndIf
EndFunction


Function RequestLLMResponseNPC(string speaker, string eventLine, string target, string type = "npc_talk")
  if bHasAIFF
    float currentTime = Utility.GetCurrentRealTime()
    string lineToSend = speaker + "@" + target + "@" + eventLine
    if currentTime - lastRequestTime > config.requestResponseCooldown
      lastRequestTime = currentTime
      Info("Requesting response from LLM: " + lineToSend)
      minAIFF.AIRequestMessageForActor(lineToSend, type, target)
    EndIf
  elseif bHasMantella
    RegisterEvent(eventLine, "npc_chat")
   EndIf
EndFunction



; deprecated - use global function from minai_Util
string Function GetActorName(actor akActor)
  return MinaiUtil.GetActorName(akActor)
EndFunction


string Function GetYouYour(actor akCaster)
  if akCaster != playerRef
    return GetActorName(akCaster) + "'s"
  endif
  return "your"
EndFunction

int function CountMatch(string sayLine, string lineToMatch)
  int count = 0
  int index = 0
  while index != -1 && count < 30
    index = StringUtil.Find(sayLine, lineToMatch, index+1)
    count += 1
  endWhile
  return count
EndFunction

; deprecated use from minai_Util
Function Log(String str, string lvl)
  MinaiUtil.Log(str, lvl)
EndFunction

; deprecated use from minai_Util
Function Fatal(String str)
  MinaiUtil.Fatal(str)
EndFunction

; deprecated use from minai_Util
Function Error(String str)
  MinaiUtil.Error(str)
EndFunction

; deprecated use from minai_Util
Function Warn(String str)
  MinaiUtil.Warn(str)
EndFunction

; deprecated use from minai_Util
Function Info(String str)
  MinaiUtil.Info(str)
EndFunction

; deprecated use from minai_Util
Function Debug(String str)
  MinaiUtil.Debug(str)
EndFunction

; deprecated use from minai_Util
Function DebugVerbose(String str)
  MinaiUtil.DebugVerbose(str)
EndFunction


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
  RequestLLMResponseFromActor(eventLine, eventType, targetName)
EndEvent



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
    minAIFF.StoreContext(modName, eventKey, eventValue, "everyone", ttl)
  elseif bHasMantella
    ; Not persistent, but better than nothing for mantella users
    RegisterEvent(eventValue, "info_context")
  endif
EndEvent

; Set persistent context to be included in every LLM request for a specific NPC until TTL expires.
; int handle = ModEvent.Create("MinAI_SetContextNPC")
;  if (handle)
;    ModEvent.PushString(handle, modName)
;    ModEvent.PushString(handle, eventKey)
;    ModEvent.PushString(handle, eventValue)
;    ModEvent.PushString(handle, npcName)
;    ModEvent.PushInt(handle, ttl)
;    ModEvent.Send(handle)
;  endIf
Event OnSetContextNPC(string modName, string eventKey, string eventValue, string npcName, int ttl)
  Info("OnSetContextNPC(" + modName + " => " + eventKey + " (TTL: " + ttl + ") ["+npcName+"]): " + eventValue)
  if bHasAIFF
    minAIFF.StoreContext(modName, eventKey, eventValue, npcName, ttl)
  elseif bHasMantella
    ; Not persistent, but better than nothing for mantella users
    RegisterEvent(eventValue, "info_context")
  endif
EndEvent


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
		minaiff.RegisterAction("ExtCmd"+actionName, actionName, mcmDescription, "External", enabled, cooldown, 2, 5, 300, true, true)
    minaiff.StoreAction(actionName, actionPrompt, enabled, ttl, targetDescription, targetEnum, "everyone")
  elseif bHasMantella
    ; Nothing to do for mantella.
  endif
EndEvent



; Register an action to only be available to a specific NPC
; int handle = ModEvent.Create("MinAI_RegisterActionNPC")
;  if (handle)
;    ModEvent.PushString(handle, actionName) ; Cannot contain spaces! Example, "SitDown".
;    ModEvent.PushString(handle, actionPrompt)
;    ModEvent.PushString(handle, mcmDescription)
;    ModEvent.PushString(handle, npcName)
;    ModEvent.PushString(handle, targetDescription)
;    ModEvent.PushString(handle, targetEnum)
;    ModEvent.PushInt(handle, enabled)
;    ModEvent.PushFloat(handle, cooldown)
;    ModEvent.PushInt(handle, ttl)
;    ModEvent.Send(handle)
;  endIf
Event OnRegisterActionNPC(string actionName, string actionPrompt, string mcmDescription, string npcName, string targetDescription, string targetEnum, int enabled, float cooldown, int ttl)
  Info("OnRegisterActionNPC(" + actionName + " => " + enabled + " (Cooldown: " + cooldown + ")): " + actionPrompt)
  if bHasAIFF
		minaiff.RegisterAction("ExtCmd"+actionName, actionName, mcmDescription, "External", enabled, cooldown, 2, 5, 300, true, true)
    minaiff.StoreAction(actionName, actionPrompt, enabled, ttl, targetDescription, targetEnum, npcName)
  elseif bHasMantella
    ; Nothing to do for mantella.
  endif
EndEvent


Function SendTestEvent()
  int handle = ModEvent.Create("MinAI_RegisterEvent")
  if (handle)
    ModEvent.PushString(handle, "testevent")
    ModEvent.PushString(handle, "info_testevent")
    ModEvent.Send(handle)
  endIf
EndFunction

Function SetTestContext()
  int handle = ModEvent.Create("MinAI_SetContext")
  if (handle)
    ModEvent.PushString(handle, "testmod")
    ModEvent.PushString(handle, "testkey")
    ModEvent.PushString(handle, "testvalue")
    ModEvent.PushInt(handle, 1200)
    ModEvent.Send(handle)
  endIf
EndFunction

Function SetTestContextNPC()
  int handle = ModEvent.Create("MinAI_SetContextNPC")
  if (handle)
    ModEvent.PushString(handle, "testmod")
    ModEvent.PushString(handle, "testkeynpc")
    ModEvent.PushString(handle, "testvaluenpc")
    ModEvent.PushString(handle, "Uthgerd the Unbroken")
    ModEvent.PushInt(handle, 1200)
    ModEvent.Send(handle)
  endIf
EndFunction

Function SetTestContextPlayer()
  int handle = ModEvent.Create("MinAI_SetContextNPC")
  if (handle)
    ModEvent.PushString(handle, "testmod")
    ModEvent.PushString(handle, "testkeyplayer")
    ModEvent.PushString(handle, "testvalueplayer")
    ModEvent.PushString(handle, GetActorName(playerRef))
    ModEvent.PushInt(handle, 1200)
    ModEvent.Send(handle)
  endIf
EndFunction 

Function RegisterTestAction()
  int handle = ModEvent.Create("MinAI_RegisterAction")
  if (handle)
    ModEvent.PushString(handle, "testaction")
    ModEvent.PushString(handle, "Use the test action")
    ModEvent.PushString(handle, "Test Action Description")
    ModEvent.PushString(handle, "Target (Actor, NPC)")
    ModEvent.PushString(handle, "my,list,of,targets")
    ModEvent.PushInt(handle, 1)
    ModEvent.PushFloat(handle, 5)
    ModEvent.PushInt(handle, 1200)
    ModEvent.Send(handle)
  endIf
EndFunction

Function RegisterTestActionNPC()
  int handle = ModEvent.Create("MinAI_RegisterActionNPC")
  if (handle)
    ModEvent.PushString(handle, "testactionnpc")
    ModEvent.PushString(handle, "Use the test action npc")
    ModEvent.PushString(handle, "Test Action Description")
    ModEvent.PushString(handle, "Uthgerd the Unbroken")
    ModEvent.PushString(handle, "Target (Actor, NPC)")
    ModEvent.PushString(handle, "my,list,of,targets")
    ModEvent.PushInt(handle, 1)
    ModEvent.PushFloat(handle, 5)
    ModEvent.PushInt(handle, 1200)
    ModEvent.Send(handle)
  endIf
EndFunction

Function RegisterTestActionPlayer()
  int handle = ModEvent.Create("MinAI_RegisterActionNPC")
  if (handle)
    ModEvent.PushString(handle, "testactionplayer")
    ModEvent.PushString(handle, "Use the test action player")
    ModEvent.PushString(handle, "Test Action Description")
    ModEvent.PushString(handle, GetActorName(PlayerRef))
    ModEvent.PushString(handle, "Target (Actor, NPC)")
    ModEvent.PushString(handle, "my,list,of,targets")
    ModEvent.PushInt(handle, 1)
    ModEvent.PushFloat(handle, 5)
    ModEvent.PushInt(handle, 1200)
    ModEvent.Send(handle)
  endIf
EndFunction

Function TestModEvents()
  SendTestEvent()
  SetTestContext()
  SetTestContextNPC()
  SetTestContextPlayer()
  RegisterTestAction()
  RegisterTestActionNPC()
  RegisterTestActionPlayer()
EndFunction


Function AddSpellsToPlayer()
  Info("Adding spells to player")
  playerRef.AddSpell(minai_ToggleSapienceSpell)
EndFunction

Function RemoveSpellsFromPlayer()
  Info("Removing spells from player")
  playerRef.RemoveSpell(minai_ToggleSapienceSpell)
EndFunction

Function SetSapienceKey(bool showNotification = false)
    ; Register new key if valid
    if (config.toggleSapienceKey != -1)
        RegisterForKey(config.toggleSapienceKey)
        if showNotification
            Debug.Notification("Sapience toggle key mapped to " + config.toggleSapienceKey)
        endif
    endIf
EndFunction

Event OnKeyDown(int keyCode)
    ; Don't process key events if game is paused
    If(Utility.IsInMenuMode())
        return
    EndIf
    
    If(keyCode == config.ToggleSapienceKey)
        minAiff.ToggleSapience()
    ElseIf(keyCode == config.singKey)
        OnSingKeyPressed()
        sapience.ResetAndStartNextUpdate()
    ElseIf(keyCode == config.narratorKey)
        OnNarratorKeyPressed()
        sapience.ResetAndStartNextUpdate()
    ElseIf(keyCode == config.narratorTextKey)
        OnNarratorTextKeyPressed()
        sapience.ResetAndStartNextUpdate()
    ElseIf(keyCode == config.roleplayKey)
        OnRoleplayKeyPressed()
        sapience.ResetAndStartNextUpdate()
    ElseIf(keyCode == config.roleplayTextKey)
        OnRoleplayTextKeyPressed()
        sapience.ResetAndStartNextUpdate()
    EndIf
EndEvent

Event OnKeyUp(int keyCode, float holdTime)
    ; Don't process key events if game is paused
    If(Utility.IsInMenuMode())
        return
    EndIf
    
    If(keyCode == config.singKey)
        OnSingKeyReleased(holdTime)
        sapience.ResetAndStartNextUpdate()
    ElseIf(keyCode == config.narratorKey)
        OnNarratorKeyReleased(holdTime)
        sapience.ResetAndStartNextUpdate()
    ElseIf(keyCode == config.roleplayKey)
        OnRoleplayKeyReleased(holdTime)
        sapience.ResetAndStartNextUpdate()
    EndIf
EndEvent

; Handler functions for the key presses
Function OnSingKeyPressed()
    If(bHasAIFF)
        Info("Starting singing recording")
        minAIFF.SetActorVariable(playerRef, "isSinging", true)
        minAIFF.AIRecordSoundEx(config.singKey)
        Debug.Notification("Hold to record singing, release quickly to sing without recording")
    Else
        Debug.Notification("AIFF not installed - singing requires AIFF")
    EndIf
EndFunction

Function OnSingKeyReleased(float holdTime)
    If(bHasAIFF)
        Info("Stopping singing recording")
        minAIFF.AIStopRecording(config.singKey)
        
        ; Only send message if key was held for less than 1 second
        if holdTime < 1.0
            string playerName = GetActorName(playerRef)
            string eventLine = playerName + " wants to sing"
            minAIFF.AIRequestMessageForActor(eventLine, "minai_sing", playerName)
            Debug.Notification("Quick press - generating song")
        else
            Debug.Notification("Recording stopped")
        EndIf
    EndIf
EndFunction

Function OnNarratorKeyPressed()
    If(bHasAIFF)
        Info("Starting narrator recording")
        minAIFF.SetActorVariable(playerRef, "isTalkingToNarrator", true)
        minAIFF.AIRecordSoundEx(config.narratorKey)
        Debug.Notification("Hold to record message for narrator, release quickly to talk without recording")
    Else
        Debug.Notification("AIFF not installed - narrator conversations require AIFF")
    EndIf
EndFunction

Function OnNarratorKeyReleased(float holdTime)
    If(bHasAIFF)
        ; Reset the last request time to prevent immediate response from other systems
        lastRequestTime = Utility.GetCurrentRealTime()
        Info("Stopping narrator recording")
        minAIFF.AIStopRecording(config.narratorKey)
        
        ; Only send message if key was held for less than 1 second
        if holdTime < 1.0
            minAIFF.AIRequestMessage("", "minai_narrator_talk")
            Debug.Notification("Quick press - asking narrator to speak")
        else
            Debug.Notification("Recording stopped")
        EndIf
    EndIf
EndFunction

; Functions to handle key registration
Function SetSingKey(bool showNotification = false)
    ; Register new key if valid
    if (config.singKey != -1)
        RegisterForKey(config.singKey)
        if showNotification
            Debug.Notification("Sing key mapped to " + config.singKey)
        endif
    endIf
EndFunction

Function SetNarratorKey(bool showNotification = false)
    ; Register new key if valid
    if (config.narratorKey != -1)
        RegisterForKey(config.narratorKey)
        if showNotification
            Debug.Notification("Narrator key mapped to " + config.narratorKey)
        endif
    endIf
EndFunction

Function OnNarratorTextKeyPressed()
    If(bHasAIFF)
        Info("Opening narrator text input")
        minAIFF.SetActorVariable(playerRef, "isTalkingToNarrator", true)
        
        ; Open text input menu
        UIExtensions.OpenMenu("UITextEntryMenu") 
        string messageText = UIExtensions.GetMenuResultString("UITextEntryMenu")
        
        If(messageText != "")
            ; Reset the last request time to prevent immediate response
            lastRequestTime = Utility.GetCurrentRealTime()
            string playerName = GetActorName(playerRef)
            minAIFF.AIRequestMessage(messageText, "minai_narrator_talk")
            Debug.Notification("Message sent to narrator")
        EndIf
    Else
        Debug.Notification("AIFF not installed - narrator conversations require AIFF")
    EndIf
EndFunction

; Add to key registration functions
Function SetNarratorTextKey(bool showNotification = false)
    ; Register new key if valid
    if (config.narratorTextKey != -1)
        RegisterForKey(config.narratorTextKey)
        if showNotification
            Debug.Notification("Narrator text key mapped to " + config.narratorTextKey)
        endif
    endIf
EndFunction

Function OnRoleplayKeyPressed()
    If(bHasAIFF)
        Info("Starting roleplay recording")
        minAIFF.SetActorVariable(playerRef, "isRoleplaying", true)
        minAIFF.AIRecordSoundEx(config.roleplayKey)
        Debug.Notification("Hold to record, release quickly to roleplay without recording")
    Else
        Debug.Notification("AIFF not installed - roleplay requires AIFF")
    EndIf
EndFunction

Function OnRoleplayKeyReleased(float holdTime)
    If(bHasAIFF)
        ; Reset the last request time to prevent immediate response
        lastRequestTime = Utility.GetCurrentRealTime()
        Info("Stopping roleplay recording")
        minAIFF.AIStopRecording(config.roleplayKey)
        
        ; Only send message if key was held for less than 1 second
        if holdTime < 1.0
            minAIFF.AIRequestMessage("", "minai_roleplay")
            Debug.Notification("Quick press - generating roleplay response")
        else
            Debug.Notification("Recording stopped")
        EndIf
    EndIf
EndFunction

Function OnRoleplayTextKeyPressed()
    If(bHasAIFF)
        Info("Opening roleplay text input")
        minAIFF.SetActorVariable(playerRef, "isRoleplaying", true)
        
        ; Open text input menu
        UIExtensions.OpenMenu("UITextEntryMenu") 
        string messageText = UIExtensions.GetMenuResultString("UITextEntryMenu")
        
        If(messageText != "")
            ; Reset the last request time to prevent immediate response
            lastRequestTime = Utility.GetCurrentRealTime()
            minAIFF.AIRequestMessage(messageText, "inputtext")
            Debug.Notification("Message sent")
        EndIf
    Else
        Debug.Notification("AIFF not installed - roleplay requires AIFF")
    EndIf
EndFunction

; Add to key registration functions
Function SetRoleplayKey(bool showNotification = false)
    ; Register new key if valid
    if (config.roleplayKey != -1)
        RegisterForKey(config.roleplayKey)
        if showNotification
            Debug.Notification("Roleplay key mapped to " + config.roleplayKey)
        endif
    endIf
EndFunction

Function SetRoleplayTextKey(bool showNotification = false)
    ; Register new key if valid
    if (config.roleplayTextKey != -1)
        RegisterForKey(config.roleplayTextKey)
        if showNotification
            Debug.Notification("Roleplay text key mapped to " + config.roleplayTextKey)
        endif
    endIf
EndFunction