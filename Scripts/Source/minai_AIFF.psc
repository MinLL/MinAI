scriptname minai_AIFF extends Quest

; Modules
minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious
minai_Config config
minai_Reputation reputation
minai_DirtAndBlood dirtAndBlood
minai_Relationship relationship
minai_EnvironmentalAwareness envAwareness
minai_Util MinaiUtil 
minai_CombatManager combat

; Per-actor mutex for SetContext
int Property contextMutexMap Auto  ; JMap of actor names to mutex states

minai_FertilityMode fertility
GlobalVariable minai_DynamicSapienceToggleStealth
bool bHasAIFF = False

int Property contextUpdateInterval Auto ; Deprecated, use config.contextUpdateInterval instead
int Property playerContextUpdateInterval Auto ; Deprecated, use config.highFrequencyUpdateInterval instead
string[] vibratorCommands

Actor player
VoiceType NullVoiceType
bool isInitialized
minai_MainQuestController main
minai_Followers followers
Keyword AIAssisted
Spell ContextSpell
GlobalVariable minai_SapienceEnabled
int Property actionRegistry Auto
int sapientActors = 0

bool bHasFollowPlayer = False
Package FollowPlayerPackage
Faction FollowingPlayerFaction

; Add new property to track last dialogue time for actors
int Property lastDialogueTimes Auto  ; JMap of actor names to timestamps

; Add new property for update tracking
int Property updateTracker Auto  ; JMap for tracking last update times

Function InitFollow()
  bHasFollowPlayer = False
  FollowPlayerPackage = Game.GetFormFromFile(0x000E8E, "MinAI.esp") as Package
  If FollowPlayerPackage == None
    Main.Error("[FollowTarget] Could not load follow player package - Mismatched script and esp versions")
  EndIf

  FollowingPlayerFaction = Game.GetFormFromFile(0x000E8B, "MinAI.esp") as Faction
  If FollowingPlayerFaction == None
    Main.Error("[FollowTarget] Could not load follow player faction - Mismatched script and esp versions")
  EndIf

  Main.Debug("[FollowTarget] FollowTarget initialized")
  bHasFollowPlayer = True
EndFunction

Function Maintenance(minai_MainQuestController _main)
  if (main.GetVersion() != main.CurrentVersion)
    Main.Info("AIFF - Maintenance: Version update detected. Resetting action registry.")
    ResetActionRegistry()
  EndIf

  ; Initialize and cleanup mutex map
  if contextMutexMap != 0
    JValue.release(contextMutexMap)
  endif
  contextMutexMap = JMap.object()
  JValue.retain(contextMutexMap)

  ; Initialize update tracker
  if updateTracker != 0
    JValue.Release(updateTracker)
  endif
  updateTracker = JMap.object()
  JValue.retain(updateTracker)
  
  main = _main
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  if !config
    main.Fatal("Could not load configuration - script version mismatch with esp")
  EndIf
  player = Game.GetPlayer()
  Main.Info("- Initializing for AIFF.")
  AIAssisted = Game.GetFormFromFile(0x217a8,"AIAgent.esp") as Keyword
  if !AIAssisted
    main.Fatal("You are running an old / outdated version of AI Follower Framework. Some functionality will be broken.")
  EndIf
  ContextSpell = Game.GetFormFromFile(0x090A, "MinAI.esp") as Spell
  minai_DynamicSapienceToggleStealth = Game.GetFormFromFile(0x0E97, "MinAI.esp") as GlobalVariable
  if (!minai_DynamicSapienceToggleStealth)
    Main.Error("Could not retrieve minai_DynamicSapienceToggleStealth from esp")
  EndIf
  sex = (Self as Quest)as minai_Sex
  survival = (Self as Quest)as minai_Survival
  arousal = (Self as Quest)as minai_Arousal
  devious = (Self as Quest)as minai_DeviousStuff
  dirtAndBlood = (Self as Quest)as minai_DirtAndBlood
  relationship = (Self as Quest)as minai_Relationship
  envAwareness = (Self as Quest)as minai_EnvironmentalAwareness
  fertility = (Self as Quest)as minai_FertilityMode
  minaiUtil = (Self as Quest) as minai_Util
  combat = (Self as Quest) as minai_CombatManager
  followers = Game.GetFormFromFile(0x0913, "MinAI.esp") as minai_Followers
  reputation = (Self as Quest) as minai_Reputation
  if (!followers)
    Main.Fatal("Could not load followers script - Mismatched script and esp versions")
  EndIf
  bHasAIFF = True
  ; Hook into AIFF
  RegisterForModEvent("CHIM_CommandReceived", "CommandDispatcher")
  RegisterForModEvent("CHIM_TextReceived", "OnTextReceived")
  RegisterForModEvent("CHIM_NPC", "OnAIActorChange")
  NullVoiceType = Game.GetFormFromFile(0x01D70E, "AIAgent.esp") as VoiceType
  minai_SapienceEnabled = Game.GetFormFromFile(0x091A, "MinAI.esp") as GlobalVariable
  if (!NullVoiceType)
    Main.Error("Could not load null voice type")
  EndIf
  if isInitialized
    AILogMessage("initializing","minai_init")
    RegisterForSingleUpdate(config.highFrequencyUpdateInterval)
  EndIf
  if (Game.GetModByName("MinAI_AIFF.esp") != 255)
    Main.Fatal("You are are running an old version of the beta with min_AIFF.esp. This file is no longer required. Delete this file.")
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
  ; Test, remove this later
  ; StoreContext("minai", "testKey", "This is dynamically persisted context!", 1200)
  InitFollow()
  CleanupStates()

  if !lastDialogueTimes
    ; Initialize the dialogue times map
    lastDialogueTimes = JMap.Object()
    JValue.Retain(lastDialogueTimes)
  EndIf

  if config.preserveQueue
    EnablePreserveQueue()
  EndIf
EndFunction


Function CleanupStates()
  actor[] actors = GetNearbyAI()
  int i = 0
  while i < actors.Length
    actor akTarget = actors[i]
    SetActorVariable(akTarget, "inCombat", akTarget.GetCombatState() > 0)
    SetActorVariable(akTarget, "hostileToPlayer", akTarget.IsHostileToActor(player))
    SetActorVariable(akTarget, "isBleedingOut", akTarget.isBleedingOut())
    SetActorVariable(akTarget, "scene", akTarget.GetCurrentScene())
    EndFollowTarget(actors[i], true)
    i += 1
  EndWhile
EndFunction

Function StartFollowTarget(actor akNpc, actor akTarget)
  If (!bHasFollowPlayer)
    Main.Debug("[FollowTarget] StartFollowTarget follow not enable")
    return
  EndIf
  
  If (akNpc == None || akTarget == None)
    Main.Error("[FollowTarget] Npc or target is none")
    return
  EndIf
  If  (akNpc.GetCurrentScene() != None)
    Main.Warn("[FollowTarget] Npc is in a scene, ignoring follow command")
    return
  EndIf

  akNpc.AddToFaction(FollowingPlayerFaction)
  ; Distance can be configured with faction rank. 1 = close, 2 = medium, 3 = far
  akNpc.SetFactionRank(FollowingPlayerFaction, 1)
  ActorUtil.AddPackageOverride(akNpc, FollowPlayerPackage, 100, 0)
  akNpc.EvaluatePackage()

  ; refresh faction
  StoreFactions(akNpc)

  Main.Info("[FollowTarget] " + akNpc.GetDisplayName() + " is now following " + akTarget.GetDisplayName())
  Debug.Notification(akNpc.GetDisplayName() + " is now following " + akTarget.GetDisplayName())
EndFunction

Function EndFollowTarget(actor akNpc, bool beQuiet = false)
  If (!bHasFollowPlayer)
    Main.Debug("[FollowTarget] EndFollowTarget follow not enable")
    return
  EndIf

  If (akNpc == None)
    Main.Error("[FollowTarget] Npc is none")
    return
  EndIf

  ActorUtil.RemovePackageOverride(akNpc, FollowPlayerPackage)
  akNpc.RemoveFromFaction(FollowingPlayerFaction)

  ; refresh faction
  StoreFactions(akNpc)
  Main.Info("[FollowTarget] " + akNpc.GetDisplayName() + " is no longer following anyone")
  if (!beQuiet)
    Debug.Notification(akNpc.GetDisplayName() + " is no longer following anyone")
  EndIf
EndFunction

Function CheckIfActorShouldStillFollow(actor akNpc)
  If (akNpc == None)
    Main.Debug("[FollowTarget] Npc is none")
    return
  EndIf
  If (akNpc == player)
    Main.Debug("[FollowTarget] Npc is the player, ignoring follow command")
    return
  EndIf

  If (akNpc.IsInFaction(FollowingPlayerFaction) && akNpc.GetCurrentPackage() != FollowPlayerPackage)
    Main.Debug("[FollowTarget] Npc is not following anyone, End following target")
    EndFollowTarget(akNpc)
  ElseIf (akNpc.GetCurrentPackage() == FollowPlayerPackage && !akNpc.IsInFaction(FollowingPlayerFaction))
    Main.Debug("[FollowTarget] Still following target, but not in faction, End following target")
    EndFollowTarget(akNpc)  
  ElseIf (followers.IsFollower(akNpc))
    Main.Debug("[FollowTarget] Is a follower now, clean up follow")
    EndFollowTarget(akNpc, true)
  EndIf
EndFunction

Function StoreActorVoice(actor akTarget)
  if akTarget == None 
    Return
  EndIf
  VoiceType voice = akTarget.GetVoiceType()
  if !voice || voice == NullVoiceType ; AIFF dynamically replaces NPC's voices with "null voice type". Don't store this.
    Main.Warn("Not storing voice type for " + Main.GetActorName(akTarget) + ": Voice type is invalid")
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

  ; Initialize mutex map if needed
  if !contextMutexMap
    contextMutexMap = JMap.object()
    JValue.retain(contextMutexMap)
  EndIf

  ; Get actor name for mutex tracking
  string actorName = Main.GetActorName(akTarget)
  
  ; Add trace logging for player
  if akTarget == player && config.logLevel >= 6
    MinaiUtil.Debug("JMap Sizes:")
    MinaiUtil.Debug("- contextMutexMap: " + JMap.count(contextMutexMap) + " entries")
    MinaiUtil.Debug("- actionRegistry: " + JMap.count(actionRegistry) + " entries") 
    MinaiUtil.Debug("- sapientActors: " + JMap.count(sapientActors) + " entries")
    MinaiUtil.Debug("- lastDialogueTimes: " + JMap.count(lastDialogueTimes) + " entries")
    MinaiUtil.Debug("- updateTracker: " + JMap.count(updateTracker) + " entries")
  EndIf
  
  ; Check if this actor's context is already being set
  if JMap.getInt(contextMutexMap, actorName) == 1
    Main.Warn("AIFF - SetContext(" + actorName + ") - Already setting context for this actor, skipping")
    return
  EndIf

  ; Set mutex for this actor
  JMap.setInt(contextMutexMap, actorName, 1)
  
  Main.Debug("AIFF - SetContext(" + actorName + ") START")
  
  ; Only update player-specific context if it's the player
  if akTarget == Player
    AILogMessage("_minai_PLAYER//playerName@" + Main.GetActorName(player), "setconf")
    AILogMessage("_minai_PLAYER//nearbyActors@" + GetNearbyAiStr(), "setconf")
  EndIf

  ; Cache the current game time to avoid multiple calls
  float currentGameTime = Utility.GetCurrentGameTime()
  
  ; Get actor's unique ID for tracking
  string actorKey = Main.GetActorName(akTarget)
  
  ; Only update voice if it hasn't been stored yet
  if !JMap.hasKey(updateTracker, actorKey + "_voice")
    StoreActorVoice(akTarget)
    JMap.setFlt(updateTracker, actorKey + "_voice", currentGameTime)
  endif

  ; Update high-frequency states (every update)
  Main.Debug("AIFF - SetContext(" + actorName + ") - Setting high-frequency states")
  devious.SetContext(akTarget)
  arousal.SetContext(akTarget)
  envAwareness.SetContext(akTarget)
  combat.SetContext(akTarget)
  
  ; Update medium-frequency states
  if !JMap.hasKey(updateTracker, actorKey + "_med") || (currentGameTime - JMap.getFlt(updateTracker, actorKey + "_med")) * 24 * 3600 >= config.mediumFrequencyUpdateInterval
    Main.Debug("AIFF - SetContext(" + actorName + ") - Setting medium-frequency states")
    survival.SetContext(akTarget)
    followers.SetContext(akTarget)
    JMap.setFlt(updateTracker, actorKey + "_med", currentGameTime)
  endif
  
  ; Update low-frequency states
  if !JMap.hasKey(updateTracker, actorKey + "_low") || (currentGameTime - JMap.getFlt(updateTracker, actorKey + "_low")) * 24 * 3600 >= config.lowFrequencyUpdateInterval
    Main.Debug("AIFF - SetContext(" + actorName + ") - Setting low-frequency states")
    reputation.SetContext(akTarget)
    dirtAndBlood.SetContext(akTarget)
    relationship.SetContext(akTarget)
    fertility.SetContext(akTarget)
    sex.SetContext(akTarget)
    StoreKeywords(akTarget)
    StoreFactions(akTarget)
    JMap.setFlt(updateTracker, actorKey + "_low", currentGameTime)
  endif

  if config.disableAIAnimations && akTarget != player
    SetAnimationBusy(1, Main.GetActorName(akTarget))
  EndIf

  ; Release mutex for this actor
  JMap.setInt(contextMutexMap, actorName, 0)
  
  Main.Debug("AIFF - SetContext(" + actorName + ") FINISH")
EndFunction


Function SetAISexEnabled(bool enabled)
  if bHasAIFF
    AILogMessage("_minai_PLAYER//enableAISex@" + enabled, "setconf")
  EndIf
EndFunction


Function SetActorVariable(Actor akActor, string variable, string value)
  if (!IsInitialized())
    Main.Info("SetActorVariable() - Still Initializing.")
    return
  EndIf
  if (!bHasAIFF)
    return
  EndIf
  string actorName = main.GetActorName(akActor)
  Main.DebugVerbose("Set actor value for actor " + actorName + " "+ variable + " to " + value)
  AILogMessage("_minai_" + actorName + "//" + variable + "@" + value, "setconf")
EndFunction


Function RegisterEvent(string eventLine, string eventType)
  if (!IsInitialized())
    Main.Info("RegisterEvent() - Still Initializing.")
    return
  EndIf
  if (!bHasAIFF)
    return
  EndIf
  AILogMessage(eventLine, eventType)
EndFunction

Event OnTextReceived(String speakerName, String sayLine)
  Main.Info("OnTextReceived(" + speakerName + "): " + sayLine)
EndEvent


Event CommandDispatcher(String speakerName,String  command, String parameter)
  Main.Info("AIFF - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")
  Actor akActor = AIGetAgentByName(speakerName)
  if vibratorCommands.Find(command) >= 0
    command = "MinaiGlobalVibrator"
  EndIf
  ExecuteAction(command)
  if !akActor
    return
  EndIf
  SetContext(akActor)
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
    ; AILogMessage("Relax and enjoy","force_current_task")
  EndIf
EndFunction

Function SetAnimationBusy(int busy, string name)
  if bHasAIFF
    if busy == 0 && config.disableAIAnimations
      Main.Warn("Not reenabling animations - AI animations are disabled")
    EndIf
    AISetAnimationBusy(busy,name)
  EndIf
EndFunction


Function SetModAvailable(string mod, bool yesOrNo)
  if bHasAIFF
    SetActorVariable(player, "mod_" + mod, yesOrNo)
  EndIf
EndFunction

string Function GetFactionsForActor(actor akTarget)
  string ret = ""
  ret += GetFactionIfExists(akTarget, "FollowingPlayerFaction", FollowingPlayerFaction)
  return ret
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
  allFactions += GetFactionsForActor(akTarget)
  ; Causing illegal characters that break sql too often
  Faction[] factions = akTarget.GetFactions(-128, 127)
  int i = 0
  while i < factions.Length
    string factionName = factions[i].GetName()
    If factionName == ""
      factionName = GetVanillaFactionName(factions[i].GetFormID())
    EndIf
    If factionName != ""
      allFactions += factionName + ","
    EndIf
    i += 1
  EndWhile
  SetActorVariable(akTarget, "AllFactions", allFactions)
EndFunction

; check for some of the vanilla factions with blank names
string Function GetVanillaFactionName(int factionId)
  If factionId == "378957" ; 0005C84D
    return "PotentialFollowerFaction"
  ElseIf factionId == "378958" ; 0005C84E
    return "CurrentFollowerFaction" ; also includes some follower animals like Vigilance not found in PotentialFollowerFaction
  ElseIf factionId == "33653669" ; 020183A5 (load order dependent, but dawnguard should always be 02)
    return "DLC1SeranaFaction" ; Serana doesn't join the normal follower factions
  EndIf
  return ""
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
    return
  EndIf
  SetContext(player)
  UpdateActions()
  
  ; Use the base update interval
  RegisterForSingleUpdate(config.highFrequencyUpdateInterval)
EndEvent

bool Function HasAIFF()
  return bHasAIFF
EndFunction


actor[] Function GetNearbyAI()
  actor[] actors = AIFindAllNearbyAgents()
  int i = 0
  if config.disableAIAnimations
    AISetConf("_animations",0,0,"")
  EndIf
  while i < actors.length
    main.Info("Found nearby actor: " + Main.GetActorName(actors[i]))
    if config.disableAIAnimations
      SetAnimationBusy(1, Main.GetActorName(actors[i]))
    EndIf
    if minai_SapienceEnabled.GetValueInt() != 1
      TrackContext(actors[i])
    EndIf
    i += 1
  endwhile
  return actors
EndFunction


String Function GetNearbyAIStr()
  actor[] actors = AIFindAllNearbyAgents()
  string ret = ""
  int i = 0
  if config.disableAIAnimations
    AISetConf("_animations",0,0,"")
  EndIf
  while i < actors.Length
    ret += Main.GetActorName(actors[i])
    if i != actors.Length - 1
      ret += ","
    EndIf
    if minai_SapienceEnabled.GetValueInt() != 1
      TrackContext(actors[i])
    EndIf
    i += 1
  EndWhile
  return ret
EndFunction

Function TrackContext(actor akActor)
  ; Make sure that agent has the right keyword
  if !akActor.HasSpell(ContextSpell)
    Main.Info("Adding Context Spell to " + Main.GetActorName(akActor))
    akActor.AddSpell(ContextSpell)
  EndIf
EndFunction

Function RegisterAction(string actionName, string mcmName, string mcmDesc, string mcmPage, int enabled, float interval, float exponent, int maxInterval, float decayWindow, bool hasMod, bool forceUpdate=false)
  if !bHasAIFF
    return
  EndIf
  int actionObj = JMap.getObj(actionRegistry, actionName)
  bool updating = false
  if actionObj != 0 && !forceUpdate
    Main.Info("Not registering action " + actionName + ": Action already is registered.")
    return
  EndIf
  if actionObj != 0
    updating = true
  else
    actionObj = JMap.Object()
    JValue.Retain(actionObj)
  EndIf
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
  config.ActionRegistryIsDirty = true
  if updating
    Main.Info("ActionRegistry: Updated existing action: " + actionName)
  else
    Main.Info("ActionRegistry: Registered new action: " + actionName)
  EndIf
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
  int hasMod = JMap.getInt(actionObj, "hasMod")
  if hasMod == 0
    Main.Debug("ActionRegistry: The mod for action " + actionName + " is not installed, skipping backoff reset.")
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
    AILogMessage("_minai_ACTION//" + actionName + "@" + isEnabled, "setconf")
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
  Main.Info("ActionRegistry: Executed " + actionName +" ( " + isEnabled + " ), interval=" + interval + ", exponent= " + exponent +", maxInterval = " + maxInterval + ", currentInterval = " + currentInterval + ", currentTime = " + currentTime + ", nextExecution =  " + nextExecution)
  AILogMessage("_minai_ACTION//" + actionName + "@" + isEnabled, "setconf")
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

Event OnAIActorChange(string npcName, string actionName)
  Main.Info("OnAIActorChange(" + npcName + "): " + actionName)
  if actionName == "Add"
    actor agent = AIGetAgentByName(npcName)
    if !agent
      Main.Error("OnAIActorChange: Could not find NPC to add context spell to")
      return
    EndIf
    if minai_SapienceEnabled.GetValueInt() != 1
      TrackContext(agent)
    EndIf
  EndIf
  ; Can't process spell removal here, since the actor will already be gone from the aiff system at this point. The context script will clean that up instead. 
EndEvent



Function StoreContext(string modName, string eventKey, string eventValue, string npcName, int ttl)
  if (!IsInitialized())
    Main.Info("StoreContext() - Still Initializing.")
    return
  EndIf
  if (!bHasAIFF)
    return
  EndIf
  Main.Debug("StoreContext(" + modName +", " + eventKey + ", " + ttl +"): " + eventValue)
  AILogMessage(modName + "@" + eventKey + "@" + eventValue + "@" + npcName + "@" + ttl, "storecontext")
EndFunction


Function StoreAction(string actionName, string actionPrompt, int enabled, int ttl, string targetDescription, string targetEnum, string npcName)
	if (!IsInitialized())
    Main.Info("StoreAction() - Still Initializing.")
    return
  EndIf
  if (!bHasAIFF)
    return
  EndIf
  Main.Debug("StoreAction(" + actionName +", " + enabled + ", " + ttl +"): " + actionPrompt)
	AILogMessage(actionName + "@" + actionPrompt + "@" + enabled + "@" + ttl + "@" + targetDescription + "@" + targetEnum + "@" + npcName, "registeraction")
EndFunction


Function TrackSapientActor(actor akTarget)
  if (sapientActors == 0)
    Main.Debug("Initializing sapient actors map")
    sapientActors = JMap.Object()
    JValue.Retain(sapientActors)
  EndIf
  JMap.SetForm(sapientActors, Main.GetActorName(akTarget), akTarget)
EndFunction

Function CleanupSapientActors()
  Main.Debug("SAPIENCE: CleanupSapientActors()")
  ; Cleanup actors that are not currently loaded
  string[] actorNames = JMap.allKeysPArray(sapientActors)
  actor[] nearbyActors = AIFindAllNearbyAgents()
  int i = 0
  while i < actorNames.Length
    actor akActor = JMap.GetForm(sapientActors, actorNames[i]) as Actor
    if !akActor
      Main.Warn("SAPIENCE: Could not validate that " + actorNames[i] + " is unloaded: Actor is none")
      RemoveActorAI(actorNames[i])
    EndIf
    bool loaded = akActor.Is3DLoaded()
    if !loaded || !nearbyActors.Find(akActor)
      Main.Debug("SAPIENCE: Actor " + actorNames[i] + " is no longer active.")
      RemoveActorAI(actorNames[i])
    else
      Main.Debug("SAPIENCE: Actor " + actorNames[i] + " is still active.")
    EndIf
    i += 1
  EndWhile
EndFunction


Function UpdateLastDialogueTime(string actorName)
  if !lastDialogueTimes
    lastDialogueTimes = JMap.Object()
    JValue.Retain(lastDialogueTimes)
  EndIf
  float currentTime = Utility.GetCurrentRealTime()
  JMap.SetFlt(lastDialogueTimes, actorName, currentTime)
  Main.Debug("Updated last dialogue time for " + actorName + " to " + currentTime)
EndFunction


Function RemoveActorAI(string targetName)
  float lastDialogueTime = JMap.GetFlt(lastDialogueTimes, targetName)
  float currentTime = Utility.GetCurrentRealTime()
  
  ; If actor had dialogue within last 60 seconds, don't remove
  ; They will get cleaned up later on location change by cleanup
  if (lastDialogueTime > 0 && (currentTime - lastDialogueTime) < 60.0 && minai_DynamicSapienceToggleStealth.GetValueInt() == 1)
    Main.Debug("SAPIENCE: Not removing " + targetName + " - recent dialogue activity")
    return
  EndIf

  Main.Info("SAPIENCE: Removing " + targetName + " from AI")
  AIRemoveAgentByName(targetName)
  
  ; Clean up all tracking data for this actor
  JMap.RemoveKey(sapientActors, targetName)
  JMap.RemoveKey(lastDialogueTimes, targetName)
  
  ; Clean up context mutex
  if contextMutexMap
    JMap.RemoveKey(contextMutexMap, targetName)
  EndIf
  
  ; Clean up update tracking data
  if updateTracker
    JMap.RemoveKey(updateTracker, targetName + "_voice")
    JMap.RemoveKey(updateTracker, targetName + "_med")
    JMap.RemoveKey(updateTracker, targetName + "_low")
  EndIf
EndFunction

Function EnableActorAI(actor akTarget)
  string targetName = Main.GetActorName(akTarget)
  if targetName == "" || targetName == "<Missing Name>"
    Main.Warn("SAPIENCE: Not adding missing npc, invalid name.")
    return
  EndIf
  Actor agent = AIGetAgentByName(targetName)
  if !agent
    Main.Info("SAPIENCE: Adding " + targetName + " to AI")
    AISetDrivenByAIA(akTarget, false)
    TrackContext(akTarget)
    TrackSapientActor(akTarget)
  EndIf
EndFunction

Function UpdateDiary(string targetName)
  if bHasAIFF
    AIRequestMessageForActor("Please, update your diary","diary", targetName)
  EndIf
EndFunction

Function UpdateProfile(string targetName)
  if bHasAIFF
    AIRequestMessageForActor("Please, update your profile", "updateprofile", targetName)
  EndIf
EndFunction

Function ToggleSapience()
  if minai_SapienceEnabled.GetValueInt() == 1
    Main.Info("SAPIENCE: Sapience disabled via toggle.")
    Debug.Notification("Sapience disabled.")
    minai_SapienceEnabled.SetValue(0)
  else
    Main.Info("SAPIENCE: Sapience enabled via toggle.")
    Debug.Notification("Sapience enabled.")
    minai_SapienceEnabled.SetValue(1)
  EndIf
EndFunction

Function EnablePreserveQueue()
  if bHasAIFF
    Main.Info("CHIM CONFIG: Preserving dialogue queue.")
    AISetConf("_preserve_queue", 1, 0, "")
  EndIf
EndFunction

Function DisablePreserveQueue()
  if bHasAIFF
    Main.Info("CHIM CONFIG: Not preserving dialogue queue.")
    AISetConf("_preserve_queue", 0, 0, "")
  EndIf
EndFunction

Function AISetConf(string configKey, int value1, int value2, string value3)
    if config.enableAIAgentSetConf && bHasAIFF && config.logLevel >= 6
        float startTime = Utility.GetCurrentRealTime()
        MinaiUtil.Trace("AISetConf(" + configKey + ", " + value1 + ", " + value2 + ", " + value3 + ") - START")
        int result = AIAgentFunctions.setConf(configKey, value1, value2, value3)
        MinaiUtil.Trace("AISetConf() - END - Result: " + result + " - Took " + (Utility.GetCurrentRealTime() - startTime) + " seconds")
    else
        AIAgentFunctions.setConf(configKey, value1, value2, value3)
    EndIf
EndFunction

Function AILogMessage(string msgText, string msgType)
    if config.enableAIAgentLogMessage && bHasAIFF && config.logLevel >= 6
        float startTime = Utility.GetCurrentRealTime()
        MinaiUtil.Trace("AILogMessage(" + msgText + ", " + msgType + ") - START")
        int result = AIAgentFunctions.logMessage(msgText, msgType)
        MinaiUtil.Trace("AILogMessage() - END - Result: " + result + " - Took " + (Utility.GetCurrentRealTime() - startTime) + " seconds")
    else
        AIAgentFunctions.logMessage(msgText, msgType)
    EndIf
EndFunction

Function AISetAnimationBusy(int busy, string name)
    if config.enableAIAgentSetAnimationBusy && bHasAIFF && config.logLevel >= 6
        float startTime = Utility.GetCurrentRealTime()
        MinaiUtil.Trace("AISetAnimationBusy(" + busy + ", " + name + ") - START")
        AIAgentFunctions.setAnimationBusy(busy, name)
        MinaiUtil.Trace("AISetAnimationBusy() - END - Took " + (Utility.GetCurrentRealTime() - startTime) + " seconds")
    else
        AIAgentFunctions.setAnimationBusy(busy, name)
    EndIf
EndFunction

Actor[] Function AIFindAllNearbyAgents()
    if config.enableAIAgentFindAllNearbyAgents && bHasAIFF && config.logLevel >= 6
        float startTime = Utility.GetCurrentRealTime()
        MinaiUtil.Trace("AIFindAllNearbyAgents() - START")
        Actor[] result = AIAgentFunctions.findAllNearbyAgents()
        MinaiUtil.Trace("AIFindAllNearbyAgents() - END - Found " + result.Length + " agents - Took " + (Utility.GetCurrentRealTime() - startTime) + " seconds")
        return result
    elseif config.enableAIAgentFindAllNearbyAgents && bHasAIFF
        return AIAgentFunctions.findAllNearbyAgents()
    EndIf
EndFunction

Actor Function AIGetAgentByName(string name)
    if config.enableAIAgentGetAgentByName && bHasAIFF && config.logLevel >= 6
        float startTime = Utility.GetCurrentRealTime()
        MinaiUtil.Trace("AIGetAgentByName(" + name + ") - START")
        Actor result = AIAgentFunctions.getAgentByName(name)
        MinaiUtil.Trace("AIGetAgentByName() - END - Found: " + (result != None) + " - Took " + (Utility.GetCurrentRealTime() - startTime) + " seconds")
        return result
    elseif config.enableAIAgentGetAgentByName && bHasAIFF
        return AIAgentFunctions.getAgentByName(name)
    EndIf
    return None
EndFunction

Function AIRemoveAgentByName(string name)
    if config.enableAIAgentRemoveAgentByName && bHasAIFF && config.logLevel >= 6
        float startTime = Utility.GetCurrentRealTime()
        MinaiUtil.Trace("AIRemoveAgentByName(" + name + ") - START")
        AIAgentFunctions.removeAgentByName(name)
        MinaiUtil.Trace("AIRemoveAgentByName() - END - Took " + (Utility.GetCurrentRealTime() - startTime) + " seconds")
    else
        AIAgentFunctions.removeAgentByName(name)
    EndIf
EndFunction

Function AISetDrivenByAIA(Actor akActor, bool driven)
    if config.enableAIAgentSetDrivenByAIA && bHasAIFF && config.logLevel >= 6
        float startTime = Utility.GetCurrentRealTime()
        MinaiUtil.Trace("AISetDrivenByAIA(" + Main.GetActorName(akActor) + ", " + driven + ") - START")
        AIAgentFunctions.setDrivenByAIA(akActor, driven)
        MinaiUtil.Trace("AISetDrivenByAIA() - END - Took " + (Utility.GetCurrentRealTime() - startTime) + " seconds")
    else
        AIAgentFunctions.setDrivenByAIA(akActor, driven)
    EndIf
EndFunction

Function AIRequestMessageForActor(string msgText, string msgType, string actorName)
    if config.enableAIAgentRequestMessageForActor && bHasAIFF && config.logLevel >= 6
        float startTime = Utility.GetCurrentRealTime()
        MinaiUtil.Trace("AIRequestMessageForActor(" + msgText + ", " + msgType + ", " + actorName + ") - START")
        int result = AIAgentFunctions.requestMessageForActor(msgText, msgType, actorName)
        MinaiUtil.Trace("AIRequestMessageForActor() - END - Result: " + result + " - Took " + (Utility.GetCurrentRealTime() - startTime) + " seconds")
    else
        AIAgentFunctions.requestMessageForActor(msgText, msgType, actorName)
    EndIf
EndFunction

Function AIRequestMessage(string msgText, string msgType)
    if config.enableAIAgentRequestMessage && bHasAIFF && config.logLevel >= 6
        float startTime = Utility.GetCurrentRealTime()
        MinaiUtil.Trace("AIRequestMessage(" + msgText + ", " + msgType + ") - START")
        int result = AIAgentFunctions.requestMessage(msgText, msgType)
        MinaiUtil.Trace("AIRequestMessage() - END - Result: " + result + " - Took " + (Utility.GetCurrentRealTime() - startTime) + " seconds")
    else
        AIAgentFunctions.requestMessage(msgText, msgType)
    EndIf
EndFunction

Function AILogMessageForActor(string msgText, string msgType, string actorName)
    if config.enableAIAgentLogMessageForActor && bHasAIFF && config.logLevel >= 6
        float startTime = Utility.GetCurrentRealTime()
        MinaiUtil.Trace("AILogMessageForActor(" + msgText + ", " + msgType + ", " + actorName + ") - START")
        int result = AIAgentFunctions.logMessageForActor(msgText, msgType, actorName)
        MinaiUtil.Trace("AILogMessageForActor() - END - Result: " + result + " - Took " + (Utility.GetCurrentRealTime() - startTime) + " seconds")
    else
        AIAgentFunctions.logMessageForActor(msgText, msgType, actorName)
    EndIf
EndFunction

Function AIRecordSoundEx(int keyCode)
    if config.enableAIAgentRecordSoundEx && bHasAIFF && config.logLevel >= 6
        float startTime = Utility.GetCurrentRealTime()
        MinaiUtil.Trace("AIRecordSoundEx(" + keyCode + ") - START")
        int result = AIAgentFunctions.recordSoundEx(keyCode)
        MinaiUtil.Trace("AIRecordSoundEx() - END - Result: " + result + " - Took " + (Utility.GetCurrentRealTime() - startTime) + " seconds")
    else
        AIAgentFunctions.recordSoundEx(keyCode)
    EndIf
EndFunction

Function AIStopRecording(int keyCode)
    if config.enableAIAgentStopRecording && bHasAIFF && config.logLevel >= 6
        float startTime = Utility.GetCurrentRealTime()
        MinaiUtil.Trace("AIStopRecording(" + keyCode + ") - START")
        int result = AIAgentFunctions.stopRecording(keyCode)
        MinaiUtil.Trace("AIStopRecording() - END - Result: " + result + " - Took " + (Utility.GetCurrentRealTime() - startTime) + " seconds")
    else
        AIAgentFunctions.stopRecording(keyCode)
    EndIf
EndFunction
