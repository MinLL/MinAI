scriptname minai_AIFF extends Quest

; Modules
minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious
minai_Config config

bool bHasAIFF = False

int Property contextUpdateInterval Auto
int Property playerContextUpdateInterval Auto
string[] vibratorCommands

Actor player
VoiceType NullVoiceType
bool isInitialized
minai_MainQuestController main
minai_Followers followers

int Property actionRegistry Auto

Function Maintenance(minai_MainQuestController _main)
  contextUpdateInterval = 30
  ; This is inefficient. We need to more selectively set specific parts of the context rather than repeatedly re-set everything.
  ; Things like arousal need to update this often probably, most things don't.
  ; TODO: Break this out and fix this.
  playerContextUpdateInterval = 5
  main = _main
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  if !config
    main.Fatal("Could not load configuration - script version mismatch with esp")
  EndIf
  player = Game.GetPlayer()
  Main.Info("- Initializing for AIFF.")

  sex = (Self as Quest)as minai_Sex
  survival = (Self as Quest)as minai_Survival
  arousal = (Self as Quest)as minai_Arousal
  devious = (Self as Quest)as minai_DeviousStuff
  followers = Game.GetFormFromFile(0x0913, "MinAI.esp") as minai_Followers
  if (!followers)
    Main.Fatal("Could not load followers script - Mismatched script and esp versions")
  EndIf
  bHasAIFF = True
  ; Hook into AIFF
  RegisterForModEvent("AIFF_CommandReceived", "CommandDispatcher")
  RegisterForModEvent("AIFF_TextReceived", "OnTextReceived")
  NullVoiceType = Game.GetFormFromFile(0x01D70E, "AIAgent.esp") as VoiceType
  if (!NullVoiceType)
    Main.Error("Could not load null voice type")
  EndIf
  if isInitialized
    SetContext(player)
    RegisterForSingleUpdate(playerContextUpdateInterval)
  EndIf
  vibratorCommands = new String[10]
  vibratorCommands[0] = "ExtCmdTeaseWithVibratorVeryWeak"
  vibratorCommands[1] = "ExtCmdTeaseWithVibratorWeak"
  vibratorCommands[2] = "ExtCmdTeaseWithVibratorMedium"
  vibratorCommands[3] = "ExtCmdTeaseWithVibratorStrong"
  vibratorCommands[4] = "ExtCmdTeaseWithVibratorVeryStrong"
  vibratorCommands[5] = "ExtCmdStimulateWithVibratorVeryWeak"
  vibratorCommands[6] = "ExtCmdStimulateWithVibratorWeak"
  vibratorCommands[7] = "ExtCmdStimulateWithVibratorMedium"
  vibratorCommands[8] = "ExtCmdStimulateWithVibratorStrong"
  vibratorCommands[9] = "ExtCmdStimulateWithVibratorVeryStrong"
  InitializeActionRegistry()
EndFunction



Function StoreActorVoice(actor akTarget)
  if akTarget == None 
    Return
  EndIf
  VoiceType voice = akTarget.GetVoiceType()
  if !voice || voice == NullVoiceType ; AIFF dynamically replaces NPC's voices with "null voice type". Don't store this.
    Return
  EndIf
  ; Fix broken xtts support in AIFF for VR by exposing the voiceType to the plugin for injection
  SetActorVariable(akTarget, "voiceType", voice)
EndFunction


Function SetContext(actor akTarget)
  if !akTarget
    Main.Warn("AIFF - SetContext() called with none target")
    return
  EndIf
  if (!IsInitialized())
    Main.Warn("SetContext(" + akTarget + ") - Still Initializing.")
    return
  EndIf  
  Main.Debug("AIFF - SetContext(" + akTarget.GetDisplayName() + ") START")
  devious.SetContext(akTarget)
  arousal.SetContext(akTarget)
  survival.SetContext(akTarget)
  followers.SetContext(akTarget)
  StoreActorVoice(akTarget)
  StoreKeywords(akTarget)
  StoreFactions(akTarget)
  if config.disableAIAnimations && akTarget != player
    SetAnimationBusy(1, akTarget.GetActorBase().GetName())
  EndIf
  Main.Debug("AIFF - SetContext(" + akTarget.GetDisplayName() + ") FINISH")
EndFunction



bool Function HasIllegalCharacters(string theString)
  return (StringUtil.Find(theString, "'") != -1)
EndFunction

Function SetActorVariable(Actor akActor, string variable, string value)
  if (!IsInitialized())
    Main.Info("SetActorVariable() - Still Initializing.")
    return
  EndIf
  if (!bHasAIFF)
    return
  EndIf
  string actorName = main.GetActorName(akActor) ; Damned khajit
  if (HasIllegalCharacters(actorName) || HasIllegalCharacters(variable) || HasIllegalCharacters(value))
    Main.Error("SetActorVariable(" + variable + "): Not persisting value for " + actorName + " due to illegal character: " + value)
    return
  EndIf
  Main.Debug("Set actor value for actor " + actorName + " "+ variable + " to " + value)
  AIAgentFunctions.logMessage("_minai_" + actorName + "//" + variable + "@" + value, "setconf")
EndFunction


Function RegisterEvent(string eventLine, string eventType)
  if (!IsInitialized())
    Main.Info("RegisterEvent() - Still Initializing.")
    return
  EndIf
  if (!bHasAIFF)
    return
  EndIf
  AIAgentFunctions.logMessage(eventLine, eventType)
EndFunction

Event OnTextReceived(String speakerName, String sayLine)
  Main.Info("OnTextReceived(" + speakerName + "): " + sayLine)
EndEvent


Event CommandDispatcher(String speakerName,String  command, String parameter)
  Main.Info("AIFF - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")
  Actor akActor = AIAgentFunctions.getAgentByName(speakerName)
  if !akActor
    return
  EndIf
  SetContext(akActor)
  if vibratorCommands.Find(command) >= 0
    command = "MinaiGlobalVibrator"
  EndIf
  ExecuteAction(command)
EndEvent


Function ChillOut()
  if bHasAIFF
    if (!IsInitialized())
      Main.Info("ChillOut() - Still Initializing.")
      return
    EndIf
  if (!bHasAIFF)
    return
  EndIf
    AIAgentFunctions.logMessage("Relax and enjoy","force_current_task")
  EndIf
EndFunction

Function SetAnimationBusy(int busy, string name)
  if bHasAIFF
    if busy == 0 && config.disableAIAnimations
      Main.Warn("Not reenabling animations - AI animations are disabled")
    EndIf
    AIAgentFunctions.setAnimationBusy(busy,name)
  EndIf
EndFunction


Function SetModAvailable(string mod, bool yesOrNo)
  if bHasAIFF
    SetActorVariable(player, "mod_" + mod, yesOrNo)
  EndIf
EndFunction


Function StoreFactions(actor akTarget)
  ; Not sure how to get the editor ID here (Eg, JobInnKeeper).
  ; GetName returns something like "Bannered Mare Services".
  ; Manually check the ones we're interested in.
  
  string allFactions = devious.GetFactionsForActor(akTarget)  
  allFactions += arousal.GetFactionsForActor(akTarget)
  allFactions += survival.GetFactionsForActor(akTarget)
  allFactions += sex.GetFactionsForActor(akTarget)
  allFactions += followers.GetFactionsForActor(akTarget)
  ; Causing illegal characters that break sql too often
  Faction[] factions = akTarget.GetFactions(-128, 127)
  int i = 0
  while i < factions.Length
   allFactions += factions[i].GetName() + ","
   i += 1
  EndWhile
  SetActorVariable(akTarget, "AllFactions", allFactions)
EndFunction


; Helper function for keyword management
String Function GetKeywordIfExists(actor akTarget, string keywordStr, Keyword theKeyword)
  if theKeyword == None
    return ""
  EndIf
  if (akTarget.WornHasKeyword(theKeyword))
    return keywordStr + ","
  EndIf
  return ""
EndFunction

; Helper function for faction management
String Function GetFactionIfExists(actor akTarget, string factionStr, Faction theFaction)
  if (akTarget.IsInFaction(theFaction))
    return factionStr + ","
  EndIf
  return ""
EndFunction


Function StoreKeywords(actor akTarget)
  string keywords = devious.GetKeywordsForActor(akTarget)
  keywords += arousal.GetKeywordsForActor(akTarget)
  keywords += survival.GetKeywordsForActor(akTarget)
  keywords += sex.GetKeywordsForActor(akTarget)
  keywords += followers.GetKeywordsForActor(akTarget)
  SetActorVariable(akTarget, "AllKeywords", keywords)
EndFunction


bool Function IsInitialized()
  return isInitialized
EndFunction

Function SetInitialized()
  Main.Info("AIFF initialization complete.")
  isInitialized = True
EndFunction


Event OnUpdate()
  if (!IsInitialized())
    UnregisterForUpdate()
    return;
  EndIf
  SetContext(player)
  UpdateActions()
  RegisterForSingleUpdate(playerContextUpdateInterval)
EndEvent

bool Function HasAIFF()
  return bHasAIFF
EndFunction


actor[] Function GetNearbyAI()
  actor[] actors = AIAgentFunctions.findAllNearbyAgents()
  int i = 0
  while i < actors.length
    main.Info("Found nearby actor: " + actors[i].GetActorBase().GetName())
    if config.disableAIAnimations
      SetAnimationBusy(1, actors[i].GetActorBase().GetName())
    EndIf
    i += 1
  endwhile
  return actors
EndFunction


Function RegisterAction(string actionName, string mcmName, string mcmDesc, string mcmPage, int enabled, float interval, float exponent, int maxInterval, float decayWindow, bool hasMod)
  if !bHasAIFF
    return
  EndIf
  if JMap.getObj(actionRegistry, actionName) != 0
    Main.Warn("ActionRegistry: " + actionName + " already registered. Skipping.")
    return
  EndIf
  int actionObj = JMap.Object()
  JValue.Retain(actionObj)
  JMap.SetStr(actionObj, "name", actionName) ; ExtCmdFoo
  JMap.SetStr(actionObj, "mcmName", mcmName) ; Foo
  JMap.SetStr(actionObj, "mcmDesc", mcmDesc) ; Foo
  JMap.SetStr(actionObj, "mcmPage", mcmPage) ; Foo
  JMap.setInt(actionObj, "enabled", enabled)
  JMap.setInt(actionObj, "enabledDefault", enabled)
  JMap.setFlt(actionObj, "interval", interval)
  JMap.setFlt(actionObj, "intervalDefault", interval)
  JMap.setFlt(actionObj, "exponent", exponent)
  JMap.setFlt(actionObj, "exponentDefault", exponent)
  JMap.setInt(actionObj, "maxInterval", maxInterval)
  JMap.setInt(actionObj, "maxIntervalDefault", maxInterval)
  JMap.setFlt(actionObj, "decayWindow", decayWindow)
  JMap.setFlt(actionObj, "decayWindowDefault", decayWindow)
  JMap.setFlt(actionObj, "lastExecuted", 0.0)
  JMap.setInt(actionObj, "currentInterval", 0)
  if hasMod
    JMap.setInt(actionObj, "hasMod", 1)
  else
    JMap.setInt(actionObj, "hasMod", 0)
  EndIf
  JMap.setObj(actionRegistry, actionName, actionObj)
  Main.Info("ActionRegistry: Registered new action: " + actionName)
EndFunction


Function ResetActionRegistry()
  if actionRegistry != 0
    Main.Info("ActionRegistry: Resetting...")
    string[] actions = JMap.allKeysPArray(actionRegistry)
    int i = 0
    while i < actions.Length
      JMap.SetObj(actionRegistry, actions[i], JValue.Release(JMap.GetObj(actionRegistry, actions[i])))
      i += 1
    EndWhile
    actionRegistry = JValue.Release(actionRegistry)
  EndIf
  InitializeActionRegistry()    
EndFunction


Function InitializeActionRegistry()
  if actionRegistry == 0
    actionRegistry = JMap.Object()
    JValue.Retain(actionRegistry)
    Main.Info("ActionRegistry: Initialized.")
  else
    Main.Info("ActionRegistry: Already initialized.")
  EndIf
EndFunction


Function ResetAllActionBackoffs()
  Main.Info("ActionRegistry: Resetting all action backoffs...")
  string[] actions = JMap.allKeysPArray(actionRegistry)
  int i = 0
  while i < actions.Length
    ResetActionBackoff(actions[i], true)
    i += 1
  EndWhile
EndFunction


Function ResetActionBackoff(string actionName, bool bypassCooldown)
  if (!bHasAIFF)
    return
  EndIf
  int actionObj = JMap.GetObj(actionRegistry, actionName)
  if actionObj == 0
    Main.Warn("ActionRegistry: Could not find action " + actionName + " to reset backoff.")
    return
  EndIf
  
  float interval = JMap.getFlt(actionObj, "interval")
  float exponent = JMap.getFlt(actionObj, "exponent")
  float decayWindow = JMap.getFlt(actionObj, "decayWindow")
  float lastExecuted = JMap.getFlt(actionObj, "lastExecuted")
  int currentInterval = JMap.getInt(actionObj, "currentInterval")
  float nextExecution = lastExecuted + (interval * Math.pow(exponent, currentInterval))
  float currentTime = Utility.GetCurrentRealTime()
  bool isEnabled
  bool offCooldown = (currentTime > nextExecution)
  if JMap.GetInt(actionObj, "enabled") == 0
    isEnabled = False
  elseif currentInterval == 0
    isEnabled = True
  else
    isEnabled = offCooldown
  EndIf
  if (currentInterval != 0 && currentTime > lastExecuted + decayWindow) || bypassCooldown
    JMap.SetFlt(actionObj, "lastExecuted", 0.0)
    JMap.SetInt(actionObj, "currentInterval", 0)
    JMap.SetObj(actionRegistry, actionName, actionObj)
    Main.Info("ActionRegistry: " + actionName + " backoff has decayed back to base values.")
  EndIf
  if offCooldown || bypassCooldown    
    AIAgentFunctions.logMessage("_minai_ACTION//" + actionName + "@" + isEnabled, "setconf")
    if lastExecuted > 0.01
      Main.Info("ActionRegistry: Backoff for " + actionName + " reset.")
    EndIf
  Else
    Main.Debug("ActionRegistry: " + actionName + " still on cooldown (" + currentTime + " | " + nextExecution +")")
  EndIf
EndFunction


Function ExecuteAction(string actionName)
  int actionObj = JMap.GetObj(actionRegistry, actionName)
  if actionObj == 0
    Main.Warn("ActionRegistry: Could not find action " + actionName + " to log execution.")
    return
  EndIf
  if (!bHasAIFF)
    return
  EndIf
  bool isEnabled = True
  int enabled = JMap.getInt(actionObj, "enabled")
  float interval = JMap.getFlt(actionObj, "interval")
  float exponent = JMap.getFlt(actionObj, "exponent")
  int maxInterval = JMap.getInt(actionObj, "maxInterval")
  float lastExecuted = JMap.getFlt(actionObj, "lastExecuted")
  int currentInterval = JMap.getInt(actionObj, "currentInterval")
  float currentTime = Utility.GetCurrentRealTime()
  float nextExecution = 0.0
  if JMap.GetInt(actionObj, "enabled") == 0
    isEnabled = False
  else
    ; Backoff implemention
    if lastExecuted <= 0.01
      ; Apply cooldown after first action rather than waiting for repeated actions
      lastExecuted = currentTime
    EndIf
    nextExecution = lastExecuted + (interval * Math.pow(exponent, currentInterval))
    isEnabled = (currentTime > nextExecution)
    if currentInterval < maxInterval
      JMap.SetInt(actionObj, "currentInterval", currentInterval + 1)
    EndIf
    JMap.SetFlt(actionObj, "lastExecuted", currentTime)
    JMap.SetObj(actionRegistry, actionName, actionObj)
  EndIf
  Main.Debug("ActionRegistry: Executed " + actionName +" ( " + isEnabled + " ), interval=" + interval + ", exponent= " + exponent +", maxInterval = " + maxInterval + ", currentInterval = " + currentInterval + ", currentTime = " + currentTime + ", nextExecution =  " + nextExecution)
  AIAgentFunctions.logMessage("_minai_ACTION//" + actionName + "@" + isEnabled, "setconf")
EndFunction


Function UpdateActions()
  string[] actions = JMap.allKeysPArray(actionRegistry)
  int i = 0
  while i < actions.Length
    ResetActionBackoff(actions[i], false)
    i += 1
  EndWhile
EndFunction

Function ResetAction(string actionName)
  int actionObj = JMap.GetObj(actionRegistry, actionName)
  JMap.setInt(actionObj, "enabled", JMap.GetInt(actionObj, "enabledDefault"))
  JMap.setFlt(actionObj, "interval", JMap.getFlt(actionObj, "intervalDefault"))
  JMap.setFlt(actionObj, "exponent", JMap.getFlt(actionObj, "exponentDefault"))
  JMap.setInt(actionObj, "maxInterval", JMap.GetInt(actionObj, "maxIntervalDefault"))
  JMap.setFlt(actionObj, "decayWindow", JMap.getFlt(actionObj, "decayWindowDefault"))
  JMap.SetObj(actionRegistry, actionName, actionObj)
EndFunction