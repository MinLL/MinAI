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

; Items registry for LLM context
int Property itemRegistry Auto  ; JMap of form IDs to item data
int Property itemBatchSize = 50 Auto  ; How many items to send in one batch

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

; Add inventory tracking properties
int Property inventoryTracker Auto  ; JMap for tracking actor inventories
float Property inventoryUpdateThrottle = 1.0 Auto  ; Default minimum seconds between inventory updates

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
    Main.Info("CHIM - Maintenance: Version update detected. Resetting action registry.")
    ResetActionRegistry()
  EndIf

  ; Initialize and cleanup mutex map
  if contextMutexMap != 0
    JValue.release(contextMutexMap)
  endif
  contextMutexMap = JMap.object()
  JValue.retain(contextMutexMap)

  ; Initialize item registry
  if itemRegistry == 0
    Main.Info("Initializing item registry")
    itemRegistry = JMap.object()
    JValue.retain(itemRegistry)
    SeedCommonItems()
  endif

  ; Initialize inventory tracking
  if inventoryTracker == 0
    Main.Info("Initializing inventory tracker")
    inventoryTracker = JMap.object()
    JValue.retain(inventoryTracker)
  endif

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
  Main.Info("- Initializing for CHIM.")
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
  
  ; Clean up tracking data for actors no longer present
  if inventoryTracker && updateTracker
    ; Create a map of actor names that are currently present
    int presentActors = JMap.object()
    i = 0
    while i < actors.Length
      JMap.setInt(presentActors, Main.GetActorName(actors[i]), 1)
      i += 1
    EndWhile
    
    ; Check all actors in inventoryTracker and remove those not present
    string[] actorNames = JMap.allKeysPArray(inventoryTracker)
    i = 0
    int removedCount = 0
    while i < actorNames.Length
      ; Skip player - always keep player's data
      if actorNames[i] != Main.GetActorName(player) && !JMap.hasKey(presentActors, actorNames[i])
        ; Actor is not present, clean up their inventory data
        int inventory = JMap.getObj(inventoryTracker, actorNames[i])
        if inventory
          JValue.release(inventory)
        EndIf
        JMap.removeKey(inventoryTracker, actorNames[i])
        
        ; Also clean up update tracking data
        string invUpdateKey = actorNames[i] + "_invUpdate"
        JMap.removeKey(updateTracker, invUpdateKey)
        
        removedCount += 1
      EndIf
      i += 1
    EndWhile
    
    if removedCount > 0
      Main.Debug("CleanupStates - Removed tracking data for " + removedCount + " absent actors")
    EndIf
    
    ; Release the temporary map
    JValue.release(presentActors)
  EndIf
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
    Main.Warn("CHIM - SetContext() called with none target")
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
  if akTarget == player && config.logLevel >= 4
    MinaiUtil.Debug("JMap Sizes:")
    MinaiUtil.Debug("- contextMutexMap: " + JMap.count(contextMutexMap) + " entries")
    MinaiUtil.Debug("- actionRegistry: " + JMap.count(actionRegistry) + " entries") 
    MinaiUtil.Debug("- sapientActors: " + JMap.count(sapientActors) + " entries")
    MinaiUtil.Debug("- lastDialogueTimes: " + JMap.count(lastDialogueTimes) + " entries")
    MinaiUtil.Debug("- updateTracker: " + JMap.count(updateTracker) + " entries")
    MinaiUtil.Debug("- itemRegistry: " + JMap.count(itemRegistry) + " entries")
    MinaiUtil.Debug("- inventoryTracker: " + JMap.count(inventoryTracker) + " entries")
  EndIf
  
  ; Check if this actor's context is already being set
  if JMap.getInt(contextMutexMap, actorName) == 1
    Main.Warn("CHIM - SetContext(" + actorName + ") - Already setting context for this actor, skipping")
    return
  EndIf

  ; Set mutex for this actor
  JMap.setInt(contextMutexMap, actorName, 1)
  
  Main.Debug("CHIM - SetContext(" + actorName + ") START")
  
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
  Main.Debug("CHIM - SetContext(" + actorName + ") - Setting high-frequency states")
  arousal.SetContext(akTarget)
  envAwareness.SetContext(akTarget)
  combat.SetContext(akTarget)
  
  ; Update medium-frequency states
  if !JMap.hasKey(updateTracker, actorKey + "_med") || (currentGameTime - JMap.getFlt(updateTracker, actorKey + "_med")) * 24 * 3600 >= config.mediumFrequencyUpdateInterval
    Main.Debug("CHIM - SetContext(" + actorName + ") - Setting medium-frequency states")
    survival.SetContext(akTarget)
    followers.SetContext(akTarget)
    JMap.setFlt(updateTracker, actorKey + "_med", currentGameTime)
  endif
  
  ; Update low-frequency states
  if !JMap.hasKey(updateTracker, actorKey + "_low") || (currentGameTime - JMap.getFlt(updateTracker, actorKey + "_low")) * 24 * 3600 >= config.lowFrequencyUpdateInterval
    Main.Debug("CHIM - SetContext(" + actorName + ") - Setting low-frequency states")
    TrackActorInventory(akTarget)
    devious.SetContext(akTarget)
    reputation.SetContext(akTarget)
    dirtAndBlood.SetContext(akTarget)
    relationship.SetContext(akTarget)
    fertility.SetContext(akTarget)
    sex.SetContext(akTarget)
    StoreKeywords(akTarget)
    StoreFactions(akTarget)
    TrackActorInventory(akTarget)
    JMap.setFlt(updateTracker, actorKey + "_low", currentGameTime)
  endif

  if config.disableAIAnimations && akTarget != player
    SetAnimationBusy(1, Main.GetActorName(akTarget))
  EndIf

  ; Release mutex for this actor
  JMap.setInt(contextMutexMap, actorName, 0)
  
  Main.Debug("CHIM - SetContext(" + actorName + ") FINISH")
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
  Main.Info("CHIM - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")
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
  Main.Info("CHIM initialization complete.")
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
  ; Can't process spell removal here, since the actor will already be gone from the CHIM system at this point. The context script will clean that up instead. 
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
    JMap.RemoveKey(updateTracker, targetName + "_invUpdate")
    JMap.RemoveKey(updateTracker, targetName + "_invThrottle")
  EndIf
  
  ; Clean up inventory tracking data
  if inventoryTracker && JMap.hasKey(inventoryTracker, targetName)
    int inventory = JMap.getObj(inventoryTracker, targetName)
    if inventory
      JValue.release(inventory)
    EndIf
    JMap.removeKey(inventoryTracker, targetName)
    Main.Debug("SAPIENCE: Removed inventory tracking data for " + targetName)
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

Function PersistItemRegistry(bool useBatching = true)
  if (!IsInitialized() || !bHasAIFF)
    Main.Info("PersistItemRegistry() - Not initialized or AIFF not available.")
    return
  EndIf

  string[] formIds = JMap.allKeysPArray(itemRegistry)
  int i = 0
  int batchCount = 0
  string batchData = ""

  while i < formIds.Length
    int itemData = JMap.getObj(itemRegistry, formIds[i])
    if itemData != 0
      string modName = JMap.getStr(itemData, "modName")
      string formId = formIds[i]
      string itemName = JMap.getStr(itemData, "name")
      int formTypeId = JMap.getInt(itemData, "formTypeId")
      string description = JMap.getStr(itemData, "description")
      
      ; If no name is stored, try to get it from the form
      if itemName == ""
        Form itemForm = JMap.getForm(itemData, "form")
        if itemForm
          itemName = itemForm.GetName()
          ; Store name for future use
          if itemName != ""
            JMap.setStr(itemData, "name", itemName)
          EndIf
        EndIf
      EndIf

      if useBatching
        ; Add to batch
        if batchData != ""
          batchData += "~"  ; Use ~ as batch separator since | is reserved
        EndIf
        batchData += formId + "@" + modName + "@" + itemName + "@" + formTypeId + "@" + description

        batchCount += 1
        ; Send batch if we hit batch size or this is the last item
        if batchCount >= itemBatchSize || i == formIds.Length - 1
          AILogMessage(batchData, "minai_storeitem_batch")
          batchData = ""
          batchCount = 0
        EndIf
      else
        ; Send individually
        AILogMessage(formId + "@" + modName + "@" + itemName + "@" + formTypeId + "@" + description, "minai_storeitem")
      EndIf
    EndIf
    i += 1
  EndWhile
EndFunction

Function AddItemToRegistry(Form akForm, string description = "", string customName = "")
  if (!IsInitialized())
    Main.Info("AddItemToRegistry() - Not initialized.")
    return
  EndIf

  if !akForm
    Main.Warn("AddItemToRegistry() - Form is None")
    return
  EndIf

  ; Get form info using MinaiUtil functions
  string formId = minai_Util.FormToHex(akForm)
  string modName = minai_Util.FormToModName(akForm)
  int formTypeId = akForm.GetType()
  
  ; Use the custom name if provided, otherwise use the form's actual name
  string itemName = customName
  if itemName == ""
    itemName = akForm.GetName()
  EndIf

  ; Create or get existing item data
  int itemData = JMap.getObj(itemRegistry, formId)
  if itemData == 0
    itemData = JMap.object()
    JValue.retain(itemData)
  EndIf

  ; Update item data
  JMap.setStr(itemData, "modName", modName)
  JMap.setInt(itemData, "formTypeId", formTypeId)
  JMap.setForm(itemData, "form", akForm)
  JMap.setStr(itemData, "name", itemName)
  
  ; Store the description if provided
  if description != ""
    JMap.setStr(itemData, "description", description)
  EndIf
  
  JMap.setObj(itemRegistry, formId, itemData)

  ; Log different messages depending on whether this is a custom-named item
  if customName != "" && customName != akForm.GetName()
    Main.Debug("Added/Updated custom named item in registry: " + formId + " from " + modName + " (custom name: " + itemName + ", original name: " + akForm.GetName() + ")")
  else
    Main.Debug("Added/Updated item in registry: " + formId + " from " + modName + " (type: " + formTypeId + ", name: " + itemName + ")")
  EndIf
EndFunction

Function SetItemBatchSize(int size)
  if size < 1
    size = 1
  EndIf
  itemBatchSize = size
  Main.Debug("Set item batch size to " + size)
EndFunction

Function RemoveItemFromRegistry(string formId)
  if (!IsInitialized())
    Main.Info("RemoveItemFromRegistry() - Not initialized.")
    return
  EndIf

  int itemData = JMap.getObj(itemRegistry, formId)
  if itemData != 0
    JValue.release(itemData)
    JMap.removeKey(itemRegistry, formId)
    Main.Debug("Removed item from registry: " + formId)
  EndIf
EndFunction

Function ClearItemRegistry()
  if (!IsInitialized())
    Main.Info("ClearItemRegistry() - Not initialized.")
    return
  EndIf

  string[] formIds = JMap.allKeysPArray(itemRegistry)
  int i = 0
  while i < formIds.Length
    RemoveItemFromRegistry(formIds[i])
    i += 1
  endwhile

  Main.Debug("Cleared item registry")
EndFunction

int Function GetItemFromPartialID(string partialFormId, string modName = "")
  ; Convert partial form ID to full form ID
  string fullFormId = GetFullFormID(partialFormId, modName)
  if !fullFormId
    Main.Warn("GetItemFromPartialID() - Failed to generate full form ID from " + partialFormId + " and " + modName)
    return 0
  EndIf
  
  ; Get the item data from registry
  return JMap.getObj(itemRegistry, fullFormId)
EndFunction

string Function GetFullFormID(string partialFormId, string modName = "")
  ; Validate input format (0x123456)
  if !partialFormId || StringUtil.GetLength(partialFormId) != 8 || StringUtil.Find(partialFormId, "0x") != 0
    Main.Warn("GetFullFormID() - Invalid partial form ID format: " + partialFormId)
    return ""
  EndIf

  ; Get just the 6-digit ID portion
  string sixDigitId = StringUtil.Substring(partialFormId, 2, 6)
  
  ; Variables to track best match when no mod name specified
  string bestMatch = ""
  int highestModIndex = -1
  bool multipleMatches = false
  
  ; Iterate through registry to find matching item(s)
  string[] formIds = JMap.allKeysPArray(itemRegistry)
  int i = 0
  while i < formIds.Length
    int itemData = JMap.getObj(itemRegistry, formIds[i])
    if itemData != 0
      ; Check if form ID ends with our 6 digits
      if StringUtil.Find(formIds[i], sixDigitId) != -1
        if modName != "" ; If mod name specified, must match exactly
          string storedModName = JMap.getStr(itemData, "modName")
          if storedModName == modName
            return formIds[i]
          EndIf
        else ; No mod name specified, track highest mod index match
          string storedModName = JMap.getStr(itemData, "modName")
          int currentModIndex = Game.GetModByName(storedModName)
          if currentModIndex > highestModIndex
            if bestMatch != ""
              multipleMatches = true
            EndIf
            bestMatch = formIds[i]
            highestModIndex = currentModIndex
          EndIf
        EndIf
      EndIf
    EndIf
    i += 1
  EndWhile
  
  ; If no mod name specified and we found matches, log and return best match
  if modName == "" && bestMatch != ""
    if multipleMatches
      Main.Info("GetFullFormID() - Multiple matches found for " + partialFormId + ", using match from highest mod index")
    EndIf
    return bestMatch
  EndIf
  
  String debugMsg = "GetFullFormID() - No matching item found for " + partialFormId
  if modName != ""
    debugMsg += " in mod " + modName
  EndIf
  Main.Debug(debugMsg)
  return ""
EndFunction

; Proof of concept. Will add a proper system for this later.
Function SeedCommonItems()
  if (!IsInitialized())
    Main.Info("SeedCommonItems() - Not initialized.")
    return
  EndIf

  Main.Info("Seeding item registry with common Skyrim items...")

  ; Currency
  AddItemToRegistry(Game.GetFormFromFile(0x00000F, "Skyrim.esm"), "The currency of Skyrim, used for trading goods and services.", "Gold") ; Gold/Septim

  ; Common Food
  AddItemToRegistry(Game.GetFormFromFile(0x064B31, "Skyrim.esm"), "A crisp, sweet fruit commonly grown in orchards throughout Skyrim.") ; Apple
  AddItemToRegistry(Game.GetFormFromFile(0x064B33, "Skyrim.esm"), "A staple food item found in nearly every household in Skyrim.") ; Bread
  AddItemToRegistry(Game.GetFormFromFile(0x064B34, "Skyrim.esm"), "An orange root vegetable often used in soups and stews.") ; Carrot
  AddItemToRegistry(Game.GetFormFromFile(0x064B35, "Skyrim.esm"), "A large wheel of cheese that can be cut into wedges.") ; Cheese Wheel
  AddItemToRegistry(Game.GetFormFromFile(0x064B36, "Skyrim.esm"), "A wedge cut from a larger wheel of cheese.") ; Cheese Wedge
  AddItemToRegistry(Game.GetFormFromFile(0x064B38, "Skyrim.esm"), "A starchy tuber vegetable commonly used in cooking.") ; Potato
  AddItemToRegistry(Game.GetFormFromFile(0x064B39, "Skyrim.esm"), "A red, juicy fruit used in various recipes.") ; Tomato
  AddItemToRegistry(Game.GetFormFromFile(0x064B3A, "Skyrim.esm"), "A sugary dessert treat prized throughout Skyrim.") ; Sweet Roll
  AddItemToRegistry(Game.GetFormFromFile(0x064B3D, "Skyrim.esm"), "A leafy green vegetable that can be eaten raw or cooked.") ; Cabbage
  AddItemToRegistry(Game.GetFormFromFile(0x064B3F, "Skyrim.esm"), "A slender vegetable with a mild onion-like flavor.") ; Leek
  
  ; Common Drinks
  AddItemToRegistry(Game.GetFormFromFile(0x034C5E, "Skyrim.esm"), "A refined wine made from grapes grown in the higher elevations of Skyrim.") ; Alto Wine
  AddItemToRegistry(Game.GetFormFromFile(0x034C5F, "Skyrim.esm"), "A common alcoholic beverage made from fermented grapes.", "Wine") ; Wine
  AddItemToRegistry(Game.GetFormFromFile(0x034C60, "Skyrim.esm"), "A traditional Nordic alcoholic beverage made from fermented honey.") ; Nord Mead
  AddItemToRegistry(Game.GetFormFromFile(0x034C61, "Skyrim.esm"), "A popular alcoholic beverage made from fermented grains.") ; Beer
  AddItemToRegistry(Game.GetFormFromFile(0x034C6A, "Skyrim.esm"), "A premium mead produced by the influential Black-Briar family in Riften.") ; Black-Briar Mead
  
  ; Common Ingredients
  AddItemToRegistry(Game.GetFormFromFile(0x06BC0A, "Skyrim.esm"), "A common cooking ingredient used to season food and preserve meat.") ; Salt Pile
  AddItemToRegistry(Game.GetFormFromFile(0x06BC0E, "Skyrim.esm"), "A pungent bulb used in cooking to add flavor to various dishes.") ; Garlic
  AddItemToRegistry(Game.GetFormFromFile(0x06BC10, "Skyrim.esm"), "A grain used for baking bread and brewing alcoholic beverages.") ; Wheat
  
  ; Common Crafting Materials
  AddItemToRegistry(Game.GetFormFromFile(0x05ACE4, "Skyrim.esm"), "A basic metal ingot used for crafting weapons and armor.") ; Iron Ingot
  AddItemToRegistry(Game.GetFormFromFile(0x05ACE5, "Skyrim.esm"), "A refined metal ingot stronger than iron, used for crafting better weapons and armor.") ; Steel Ingot
  AddItemToRegistry(Game.GetFormFromFile(0x05ACE3, "Skyrim.esm"), "Tanned animal hide used for crafting light armor and various items.") ; Leather
  AddItemToRegistry(Game.GetFormFromFile(0x0800E4, "Skyrim.esm"), "Narrow strips of leather used in various crafting recipes.") ; Leather Strips
  AddItemToRegistry(Game.GetFormFromFile(0x05AD93, "Skyrim.esm"), "Chopped wood used for building and as fuel for fires.") ; Firewood
  
  ; Common Soul Gems
  AddItemToRegistry(Game.GetFormFromFile(0x02E4E2, "Skyrim.esm"), "The smallest soul gem, capable of holding petty souls.") ; Petty Soul Gem
  AddItemToRegistry(Game.GetFormFromFile(0x02E4E3, "Skyrim.esm"), "A small soul gem capable of holding lesser souls.") ; Lesser Soul Gem
  AddItemToRegistry(Game.GetFormFromFile(0x02E4E4, "Skyrim.esm"), "A medium-sized soul gem capable of holding common souls.") ; Common Soul Gem
  AddItemToRegistry(Game.GetFormFromFile(0x02E4E5, "Skyrim.esm"), "A large soul gem capable of holding greater souls.") ; Greater Soul Gem
  AddItemToRegistry(Game.GetFormFromFile(0x02E4E6, "Skyrim.esm"), "The largest standard soul gem, capable of holding grand souls.") ; Grand Soul Gem
  
  ; Common Potions
  AddItemToRegistry(Game.GetFormFromFile(0x03EADE, "Skyrim.esm"), "A weak potion that restores a small amount of health.") ; Minor Healing Potion
  AddItemToRegistry(Game.GetFormFromFile(0x03EADF, "Skyrim.esm"), "A weak potion that restores a small amount of magicka.") ; Minor Magicka Potion
  AddItemToRegistry(Game.GetFormFromFile(0x03EAE0, "Skyrim.esm"), "A weak potion that restores a small amount of stamina.") ; Minor Stamina Potion
  
  ; Common Misc Items
  AddItemToRegistry(Game.GetFormFromFile(0x01D4EC, "Skyrim.esm"), "A tool used to unlock doors and containers without the proper key.") ; Lockpick
  AddItemToRegistry(Game.GetFormFromFile(0x06851E, "Skyrim.esm"), "A portable light source used to illuminate dark areas.") ; Torch
  AddItemToRegistry(Game.GetFormFromFile(0x04B0BA, "Skyrim.esm"), "Burnt wood used for drawing and writing.") ; Charcoal
  AddItemToRegistry(Game.GetFormFromFile(0x04B0BC, "Skyrim.esm"), "A roll of paper used for writing notes and letters.") ; Roll of Paper
  AddItemToRegistry(Game.GetFormFromFile(0x04B0BD, "Skyrim.esm"), "A container of ink used with quills for writing on paper.") ; Inkwell

  Main.Info("Finished seeding item registry with common items")
  PersistItemRegistry()
EndFunction

Function PopulateInventoryEventFilter(Actor akActor)
  if !akActor || !itemRegistry
    return
  EndIf
  
  Main.Debug("Adding inventory event filters for " + Main.GetActorName(akActor))
  
  ; Add all items from registry as individual filters
  string[] formIds = JMap.allKeysPArray(itemRegistry)
  int i = 0
  int filteredCount = 0
  
  while i < formIds.Length
    int itemData = JMap.getObj(itemRegistry, formIds[i])
    if itemData != 0
      Form itemForm = JMap.getForm(itemData, "form")
      if itemForm
        akActor.AddInventoryEventFilter(itemForm)
        filteredCount += 1
      EndIf
    EndIf
    i += 1
  EndWhile
  
  Main.Debug("Added " + filteredCount + " inventory event filters for " + Main.GetActorName(akActor))
EndFunction

Function RemoveInventoryEventFilters(Actor akActor)
  if !akActor || !itemRegistry
    return
  EndIf
  
  Main.Debug("Removing inventory event filters for " + Main.GetActorName(akActor))
  
  ; Remove all items from registry as individual filters
  string[] formIds = JMap.allKeysPArray(itemRegistry)
  int i = 0
  
  while i < formIds.Length
    int itemData = JMap.getObj(itemRegistry, formIds[i])
    if itemData != 0
      Form itemForm = JMap.getForm(itemData, "form")
      if itemForm
        akActor.RemoveInventoryEventFilter(itemForm)
      EndIf
    EndIf
    i += 1
  EndWhile
EndFunction

Function TrackActorInventory(actor akActor)
  if !akActor
    return
  EndIf
  
  string actorName = Main.GetActorName(akActor)
  if actorName == ""
    return
  EndIf
  
  ; Get current inventory state
  int currentInventory = JMap.object()
  JValue.retain(currentInventory)
  
  ; Get all items from registry
  string[] formIds = JMap.allKeysPArray(itemRegistry)
  int i = 0
  while i < formIds.Length
    int itemData = JMap.getObj(itemRegistry, formIds[i])
    if itemData != 0
      Form itemForm = JMap.getForm(itemData, "form")
      if itemForm
        int count = akActor.GetItemCount(itemForm)
        if count > 0
          JMap.setInt(currentInventory, formIds[i], count)
        EndIf
      EndIf
    EndIf
    i += 1
  EndWhile
  
  ; Store in tracker
  JMap.setObj(inventoryTracker, actorName, currentInventory)
  
  ; Check if we should update server
  float currentTime = Utility.GetCurrentRealTime()
  string invUpdateKey = actorName + "_invUpdate"
  float lastUpdate = JMap.getFlt(updateTracker, invUpdateKey, 0.0)
  
  ; Check if custom throttle exists, otherwise use default
  string throttleKey = actorName + "_invThrottle"
  float actorThrottle = JMap.getFlt(updateTracker, throttleKey, inventoryUpdateThrottle)

  if currentTime - lastUpdate >= actorThrottle
    PersistInventory(akActor)
    JMap.setFlt(updateTracker, invUpdateKey, currentTime)
  EndIf
EndFunction

Function PersistInventory(actor akActor)
  if !akActor || !bHasAIFF
    return
  EndIf
  
  string actorName = Main.GetActorName(akActor)
  if actorName == ""
    Main.Warn("PersistInventory - Actor has no name, cannot persist inventory")
    return
  EndIf
  
  int currentInventory = JMap.getObj(inventoryTracker, actorName)
  if !currentInventory
    Main.Debug("PersistInventory - No inventory data found for " + actorName)
    return
  EndIf
  
  ; Build inventory string
  string inventoryData = ""
  string[] formIds = JMap.allKeysPArray(currentInventory)
  int i = 0
  int itemCount = 0
  
  Main.Debug("PersistInventory - Building inventory data for " + actorName + " with " + formIds.Length + " items")
  
  while i < formIds.Length
    if inventoryData != ""
      inventoryData += "~"  ; Use ~ as batch separator since | is reserved
    EndIf
    string formId = formIds[i]
    int count = JMap.getInt(currentInventory, formId)
    inventoryData += formId + "&" + count
    
    ; Get item name for detailed logging if needed
    if config.logLevel >= 6
      int itemData = JMap.getObj(itemRegistry, formId)
      string itemName = "Unknown Item"
      if itemData != 0
        itemName = JMap.getStr(itemData, "name")
        if itemName == ""
          Form itemForm = JMap.getForm(itemData, "form")
          if itemForm
            itemName = itemForm.GetName()
          EndIf
        EndIf
      EndIf
      MinaiUtil.Trace("  - " + itemName + " (" + formId + "): " + count)
    EndIf
    
    itemCount += 1
    i += 1
  EndWhile
  
  ; Send to server
  if inventoryData != ""
    ; Log message stats before sending
    Main.Debug("Persisting inventory for " + actorName + " - " + itemCount + " items, data length: " + StringUtil.GetLength(inventoryData) + " chars")
      ; Set as a single variable if not too long
      SetActorVariable(akActor, "Inventory", inventoryData)
  else
    Main.Debug("PersistInventory - No items to persist for " + actorName)
    ; Clear any existing inventory data
    SetActorVariable(akActor, "Inventory", "")
  EndIf
EndFunction

Function OnInventoryChanged(actor akActor, Form akBaseItem, int aiItemCount, bool abAdded)
  if !akActor || !akBaseItem
    return
  EndIf
  
  ; Get actor name for logging
  string actorName = Main.GetActorName(akActor)
  string itemName = akBaseItem.GetName()
  string formId = minai_Util.FormToHex(akBaseItem)
  
  ; Only track if item is in our registry
  if !JMap.hasKey(itemRegistry, formId)
    if config.logLevel >= 5  ; Debug level
      Main.Debug("OnInventoryChanged - Ignoring non-tracked item: " + itemName + " (" + formId + ") for " + actorName)
    EndIf
    return
  EndIf
  
  ; Log the inventory change
  string changeType
  if abAdded
    changeType = "added"
  else
    changeType = "removed"
  endif
  Main.Debug("Inventory " + changeType + " for " + actorName + ": " + aiItemCount + "x " + itemName + " (" + formId + ")")
  
  ; Get the current actor inventory map
  int actorInventory = JMap.getObj(inventoryTracker, actorName)
  if !actorInventory
    ; Create new inventory object if none exists
    actorInventory = JMap.object()
    JValue.retain(actorInventory)
    JMap.setObj(inventoryTracker, actorName, actorInventory)
    Main.Debug("Created new inventory tracking object for " + actorName)
  EndIf
  
  ; Update the specific item count directly
  int currentCount = JMap.getInt(actorInventory, formId, 0)
  int newCount = 0
  
  if abAdded
    newCount = currentCount + aiItemCount
  else
    newCount = currentCount - aiItemCount
    if newCount < 0
      newCount = 0  ; Prevent negative counts
    EndIf
  EndIf
  
  ; Set the updated count or remove if zero
  if newCount > 0
    JMap.setInt(actorInventory, formId, newCount)
    Main.Debug("Updated " + actorName + " inventory: " + itemName + " count = " + newCount)
  else
    JMap.removeKey(actorInventory, formId)
    Main.Debug("Removed " + itemName + " from " + actorName + " inventory tracking")
  EndIf
  
  ; Check if we should update server based on throttle
  float currentTime = Utility.GetCurrentRealTime()
  string invUpdateKey = actorName + "_invUpdate"
  float lastUpdate = JMap.getFlt(updateTracker, invUpdateKey, 0.0)

  if currentTime - lastUpdate >= inventoryUpdateThrottle
    Main.Debug("Throttle elapsed, persisting inventory for " + actorName)
    PersistInventory(akActor)
    JMap.setFlt(updateTracker, invUpdateKey, currentTime)
  else
    float timeRemaining = inventoryUpdateThrottle - (currentTime - lastUpdate)
    Main.Debug("Inventory update throttled for " + actorName + " - " + timeRemaining + " seconds remaining before next update")
  EndIf
EndFunction

; Function to set a custom inventory update throttle for a specific actor
Function SetActorInventoryThrottle(actor akActor, float throttleTime)
  if !akActor
    Main.Warn("SetActorInventoryThrottle - Actor is None")
    return
  EndIf
  
  string actorName = Main.GetActorName(akActor)
  if actorName == ""
    Main.Warn("SetActorInventoryThrottle - Actor has no name")
    return
  EndIf
  
  ; Validate the throttle value - must be at least 0.1 seconds
  if throttleTime < 0.1
    throttleTime = 0.1
  EndIf
  
  ; Store the custom throttle value directly in updateTracker
  string throttleKey = actorName + "_invThrottle"
  JMap.setFlt(updateTracker, throttleKey, throttleTime)
  Main.Info("Set custom inventory throttle for " + actorName + " to " + throttleTime + " seconds")
EndFunction
