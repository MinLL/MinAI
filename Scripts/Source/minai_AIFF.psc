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
minai_Crime crimeController

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
bool inventoryMutex
bool bHasFollowPlayer = False
Package FollowPlayerPackage
Faction FollowingPlayerFaction

int property inventoryTracker auto hidden

; Update throttling
int property updateTracker auto hidden

float property inventoryUpdateThrottle = 5.0 auto hidden

; Property for inventory burst tracking
int property inventoryBurstTracker auto hidden

; How many inventory events in a short window before throttling
int property inventoryEventThreshold = 10 auto hidden

; Time window in seconds for burst detection
float property inventoryBurstWindow = 1.0 auto hidden

; Whether to use inventory burst protection
bool property useInventoryBurstProtection = true auto hidden

; Maximum number of items to send in a single batch
int property maxInventoryBatchSize = 30 auto hidden

; Add new property to track last dialogue time for actors
int Property lastDialogueTimes Auto  ; JMap of actor names to timestamps

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
  inventoryMutex = False
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

  ; Initialize inventory tracking
  if inventoryTracker == 0
    Main.Info("Initializing inventory tracker")
    inventoryTracker = JMap.object()
    JValue.retain(inventoryTracker)
  endif

  ; Initialize inventory burst tracker
  if inventoryBurstTracker
    JValue.release(inventoryBurstTracker)
    inventoryBurstTracker = 0
  EndIf
  if inventoryBurstTracker == 0
    Main.Info("Initializing inventory burst tracker")
    inventoryBurstTracker = JMap.object()
    JValue.retain(inventoryBurstTracker)
  endif
  ; Initialize update tracker
  if updateTracker
    JValue.release(updateTracker)
    updateTracker = 0
  EndIf
  if updateTracker == 0
    updateTracker = JMap.object()
    JValue.retain(updateTracker)
  EndIf
  ; Set default throttle parameters
  if inventoryUpdateThrottle <= 0
    inventoryUpdateThrottle = 5.0  ; Default 5 second throttle
  EndIf
  
  ; Set default burst parameters if not already set
  if inventoryEventThreshold <= 0
    inventoryEventThreshold = 10   ; Default 10 events threshold
  EndIf
  
  if inventoryBurstWindow <= 0
    inventoryBurstWindow = 1.0     ; Default 1 second window
  EndIf
  
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
  crimeController = (Self as Quest) as minai_Crime
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
  
  Main.Info("MinAI AIFF Maintenance complete - Inventory tracking initialized")
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
          ; Release inventory object
          JValue.release(inventory)
        EndIf
        JMap.removeKey(inventoryTracker, actorNames[i])
        
        ; Also clean up update tracking data
        string invUpdateKey = actorNames[i] + "_invUpdate"
        JMap.removeKey(updateTracker, invUpdateKey)
        string invThrottleKey = actorNames[i] + "_invThrottle"
        JMap.removeKey(updateTracker, invThrottleKey)
        string invNeedScanKey = actorNames[i] + "_needScan"
        JMap.removeKey(updateTracker, invNeedScanKey)
        
        ; Clean up burst tracker data
        if inventoryBurstTracker && JMap.hasKey(inventoryBurstTracker, actorNames[i])
          int burstData = JMap.getObj(inventoryBurstTracker, actorNames[i])
          if burstData
            JValue.release(burstData)
          endif
          JMap.removeKey(inventoryBurstTracker, actorNames[i])
        endif
        
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
    Main.Warn("CHIM SetContext - Target is None")
    return
  EndIf
  if (!IsInitialized())
    Main.Warn("CHIM SetContext(" + akTarget + ") - Still Initializing.")
    return
  EndIf

  ; Initialize mutex map if needed
  if !contextMutexMap
    contextMutexMap = JMap.object()
    JValue.retain(contextMutexMap)
  EndIf

  ; Get actor name for mutex tracking
  string actorName = Main.GetActorName(akTarget)
  if actorName == ""
    Main.Warn("CHIM SetContext - Actor has no name")
    return
  EndIf
  
  ; Add trace logging for player
  if akTarget == player && config.logLevel >= 4
    MinaiUtil.Debug("JMap Sizes:")
    MinaiUtil.Debug("- contextMutexMap: " + JMap.count(contextMutexMap) + " entries")
    MinaiUtil.Debug("- actionRegistry: " + JMap.count(actionRegistry) + " entries") 
    MinaiUtil.Debug("- sapientActors: " + JMap.count(sapientActors) + " entries")
    MinaiUtil.Debug("- lastDialogueTimes: " + JMap.count(lastDialogueTimes) + " entries")
    MinaiUtil.Debug("- updateTracker: " + JMap.count(updateTracker) + " entries")
    MinaiUtil.Debug("- inventoryTracker: " + JMap.count(inventoryTracker) + " entries")
    MinaiUtil.Debug("- inventoryBurstTracker: " + JMap.count(inventoryBurstTracker) + " entries")
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
  PersistInventory(akTarget)
  
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
    devious.SetContext(akTarget)
    reputation.SetContext(akTarget)
    dirtAndBlood.SetContext(akTarget)
    relationship.SetContext(akTarget)
    fertility.SetContext(akTarget)
    sex.SetContext(akTarget)
    StoreKeywords(akTarget)
    StoreFactions(akTarget)
    crimeController.SetContext(akTarget)
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
  
  ; Check for actors that need a full inventory scan due to throttling
  CheckThrottledActors()
  
  ; Use the base update interval
  RegisterForSingleUpdate(config.highFrequencyUpdateInterval)
EndEvent

; Function to check for actors that need a full inventory scan due to throttling
Function CheckThrottledActors()
  if !updateTracker || !inventoryBurstTracker
    Main.Warn("CHIM CheckThrottledActors - updateTracker or inventoryBurstTracker is None")
    return
  EndIf
  
  ; Get all keys from updateTracker that end with "_needScan"
  string[] keys = JMap.allKeysPArray(updateTracker)
  int i = 0
  int processedCount = 0
  
  while i < keys.Length
    string actorKey = keys[i]
    if StringUtil.Find(actorKey, "_needScan") > 0
      ; Extract actor name from key (remove "_needScan")
      string actorName = StringUtil.Substring(actorKey, 0, StringUtil.Find(actorKey, "_needScan"))
      int needScan = JMap.getInt(updateTracker, actorKey)
      
      ; Check if this actor needs a scan
      if needScan == 1
        ; Find the actor by name
        Actor foundActor = AIGetAgentByName(actorName)
        if foundActor
          Main.Info("Processing throttled actor scan for " + actorName)
          TrackActorInventory(foundActor)
          processedCount += 1
        elseif Main.GetActorName(player) == actorName
          Main.Info("Processing throttled actor scan for player")
          TrackActorInventory(player)
          processedCount += 1
        else
          ; Actor not found, just remove the flag
          JMap.setInt(updateTracker, actorKey, 0)
          Main.Debug("Removed scan flag for absent actor " + actorName)
        EndIf
      EndIf
    EndIf
    
    i += 1
  endwhile
  
  if processedCount > 0
    Main.Info("Processed full inventory scans for " + processedCount + " throttled actors")
  EndIf
EndFunction

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

; Simplified implementation of inventory tracking

Function TrackActorInventory(actor akActor)
  if !akActor
    return
  EndIf
  
  string actorName = Main.GetActorName(akActor)
  if actorName == ""
    return
  EndIf
  
  ; Get current inventory array or create a new one
  int currentInventory = JMap.getObj(inventoryTracker, actorName)
  if !currentInventory
    currentInventory = JArray.object()
    JValue.retain(currentInventory)
    JMap.setObj(inventoryTracker, actorName, currentInventory)
  endif
  
  ; Check if a full scan was scheduled due to rapid inventory changes
  bool needScan = JMap.getInt(updateTracker, actorName + "_needScan") == 1
  
  ; Handle throttling reset
  bool wasThrottled = false
  if needScan && useInventoryBurstProtection
    int burstData = JMap.getObj(inventoryBurstTracker, actorName)
    if burstData && JMap.getInt(burstData, "throttled") == 1
      JMap.setInt(burstData, "throttled", 0)
      JMap.setInt(burstData, "count", 0)
      JMap.setFlt(burstData, "startTime", Utility.GetCurrentRealTime())
      Main.Info("Resetting inventory throttling for " + actorName + " after full scan")
      wasThrottled = true
    EndIf
  EndIf
  
  Main.Debug("TrackActorInventory - Scanning inventory for " + actorName)
  
  ; Get total number of items in inventory
  int itemCount = akActor.GetNumItems()
  int scannedCount = 0
  
  ; Clear existing inventory before scan if this was a throttled actor
  if wasThrottled
    JArray.clear(currentInventory)
    Main.Debug("Cleared existing inventory data for " + actorName + " before full scan")
  EndIf
  
  ; Iterate through each item
  int i = 0
  while i < itemCount
    ; Get the form at index i
    Form itemForm = akActor.GetNthForm(i)
    
    if itemForm
      ; Get item count to check if it exists (>0)
      int count = akActor.GetItemCount(itemForm)
      if count > 0
        ; Check if the form already exists in the array before adding
        if JArray.findForm(currentInventory, itemForm) == -1
          ; Store just the Form object in the array
          JArray.addForm(currentInventory, itemForm)
          scannedCount += 1
        EndIf
      EndIf
    EndIf
    
    i += 1
  endwhile
  
  Main.Debug("TrackActorInventory - Scanned " + scannedCount + " items for " + actorName + " (total inventory size: " + itemCount + ")")
  
  ; Clear the need scan flag
  if needScan
    JMap.setInt(updateTracker, actorName + "_needScan", 0)
  EndIf
  
  PersistInventory(akActor)
EndFunction

Function PersistInventory(actor akActor)
  ; Skip if actor is None or we're not using this system
  if !akActor || !bHasAIFF
    return
  EndIf
  
  string actorName = Main.GetActorName(akActor)
  if actorName == ""
    Main.Warn("PersistInventory - Actor has no name")
    return
  EndIf
  
  ; Apply throttling if needed - only update once per throttle period
  float currentTime = Utility.GetCurrentRealTime() ; Use real-time seconds
  float timeSinceLastUpdate = currentTime - GetActorInventoryLastUpdate(akActor)
  float actorThrottle = GetActorInventoryThrottle(akActor)
  
  if timeSinceLastUpdate < actorThrottle
    Main.Debug("PersistInventory - Throttling inventory update for " + actorName + " (last update was " + timeSinceLastUpdate + " seconds ago, throttle is " + actorThrottle + " seconds)")
    return
  EndIf
  
  ; Get actor's inventory array
  int actorInventory = JMap.getObj(inventoryTracker, actorName)
  if !actorInventory
    Main.Debug("PersistInventory - No inventory tracking object found for " + actorName)
    return
  EndIf
  
  ; Get current inventory data for the actor
  int itemCount = JArray.count(actorInventory)
  if itemCount == 0
    Main.Debug("PersistInventory - Inventory tracking empty for " + actorName)
    return
  EndIf
  
  ; Update the last inventory time
  SetActorInventoryLastUpdate(akActor, currentTime)
  
  ; Process inventory as batches if needed
  int totalItems = itemCount
  int processedItems = 0
  int batchCount = 1
  int maxItems = maxInventoryBatchSize
  bool needsBatching = totalItems > maxItems
  
  ; Determine total number of batches
  int totalBatches = 1
  if needsBatching
    totalBatches = Math.Ceiling(totalItems as float / maxItems)
  EndIf
  
  int i = 0
  
  ; Process all items in batches
  while processedItems < totalItems
    ; Start a new batch
    string batchData = ""
    int itemsInBatch = 0
    
    ; Determine batch status
    string batchStatus
    if totalBatches == 1
      batchStatus = "final" ; Only one batch, mark as final
    elseif batchCount == 1
      batchStatus = "initial" ; First batch of many
    elseif batchCount == totalBatches
      batchStatus = "final" ; Last batch of many
    else
      batchStatus = "partial" ; Middle batch
    endif
    
    ; Process up to maxItems per batch
    while i < itemCount && itemsInBatch < maxItems
      Form itemForm = JArray.getForm(actorInventory, i)
      
      ; Only process valid forms
      if itemForm
        ; Get count directly from the actor
        int count = akActor.GetItemCount(itemForm)
        
        ; Skip items that no longer exist in inventory
        if count > 0
          ; Get item details directly from the form
          string itemName = itemForm.GetName()
          string modName = minai_Util.FormToModName(itemForm)
          int formTypeId = itemForm.GetType()
          string modIndex = minai_Util.FormToModIndexHex(itemForm)
          string formId = minai_Util.FormToHex(itemForm)
          if formId == ""
            formId = "0x" + itemForm.GetFormID()
          EndIf
          
          ; Skip items with no name or from unknown mods
          if itemName != "" && modName != ""
            ; Add separator between items
            if batchData != ""
              batchData += "~"
            EndIf
            
            ; Format: formId@modName@itemName@formTypeId@modIndex@count
            batchData += formId + "@" + modName + "@" + itemName + "@" + formTypeId + "@" + modIndex + "@" + count
          else
            if itemName == ""
              Main.Debug("PersistInventory - SKIPPING item: Empty name for " + formId)
            endif
            
            if modName == ""
              Main.Debug("PersistInventory - SKIPPING item: Empty mod name for " + formId)
            endif
          EndIf
        endif
      endif
      
      itemsInBatch += 1
      processedItems += 1
      i += 1
    EndWhile
    
    ; Send this batch to server - always send if it's the final batch, even if empty
    if batchData != "" || batchStatus == "final"
      ; Log message stats
      if needsBatching
        Main.Debug("Persisting inventory for " + actorName + " - Batch " + batchCount + "/" + totalBatches + " with " + itemsInBatch + " items (status: " + batchStatus + ")")
      else
        Main.Debug("Persisting inventory for " + actorName + " - " + itemsInBatch + " items, data length: " + StringUtil.GetLength(batchData) + " chars (status: " + batchStatus + ")")
      EndIf
      
      ; Handle empty final batch case
      if batchData == "" && batchStatus == "final"
        Main.Debug("Sending empty final batch for " + actorName)
      EndIf
      
      ; Store the actor name with the batch data and status
      string actorBatchData = actorName + "@" + batchStatus + "@" + batchData
      
      ; Send the batch data to the server
      AILogMessage(actorBatchData, "minai_storeitem_batch")
      
      ; Add a small delay between batches to avoid overwhelming the server
      if needsBatching && processedItems < totalItems
        Utility.Wait(0.1)
      EndIf
      
      batchCount += 1
    EndIf
  EndWhile
  
  if totalItems == 0
    Main.Debug("PersistInventory - No items to persist for " + actorName)
  EndIf
EndFunction

; Function to check if an actor's inventory events are currently being throttled
bool Function IsInventoryThrottled(actor akActor)
  if !akActor || !useInventoryBurstProtection
    return false
  EndIf
  
  string actorName = Main.GetActorName(akActor)
  if actorName == ""
    return false
  EndIf
  
  ; Get actor's burst tracking data
  int burstData = JMap.getObj(inventoryBurstTracker, actorName)
  if !burstData
    return false
  EndIf
  
  ; Check if throttled flag is set
  return JMap.getInt(burstData, "throttled") == 1
EndFunction

; Function to mark actor for full inventory scan
Function MarkActorForFullScan(actor akActor)
  if !akActor
    return
  EndIf
  
  string actorName = Main.GetActorName(akActor)
  if actorName == ""
    return
  EndIf
  
  ; Mark for scan in update tracker
  JMap.setInt(updateTracker, actorName + "_needScan", 1)
  
  Main.Info("Marked " + actorName + " for full inventory scan due to rapid changes")
EndFunction

; Function to handle inventory change event with throttling

Function SetThrottled(int burstData, actor akActor)
  Main.Warn("Inventory tracking throttled for " + Main.GetActorName(akActor))
  JMap.setInt(burstData, "throttled", 1)
  MarkActorForFullScan(akActor)
EndFunction

int Function GetBurstData(string actorName)
  int burstData = JMap.getObj(inventoryBurstTracker, actorName)
  if !burstData
    burstData = JMap.object()
    JValue.retain(burstData)
    JMap.setFlt(burstData, "startTime", Utility.GetCurrentRealTime())
    JMap.setInt(burstData, "count", 0)
    JMap.setInt(burstData, "throttled", 0)
    JMap.setObj(inventoryBurstTracker, actorName, burstData)
  EndIf
  return burstData
EndFunction


bool Function OnInventoryChanged(actor akActor, Form akBaseItem, int aiItemCount, bool abAdded)
  bool isThrottled = IsInventoryThrottled(akActor)
  if inventoryMutex && !isThrottled
    int attempts = 0
    while inventoryMutex && !isThrottled
      Utility.Wait(0.5)
      attempts += 1
      if attempts > 10 
        Main.Warn("OnInventoryChanged - Failed to acquire inventory mutex, backing off")
        ; Throttle
        SetThrottled(GetBurstData(Main.GetActorName(akActor)), akActor)
        ; Make sure we don't leave mutex locked even during throttle
        inventoryMutex = False
        return true
      EndIf
      isThrottled = IsInventoryThrottled(akActor)
    endWhile
  EndIf
  
  if isThrottled
    Main.Warn("Made it past spinlock in OnInventoryChanged, but inventory is throttled. Backing off.")
    inventoryMutex = False
    return true
  EndIf
  
  inventoryMutex = True
  
  if !akActor || !akBaseItem
    inventoryMutex = False  ; Clear mutex before returning
    return false
  EndIf
  
  ; Get actor name for logging
  string actorName = Main.GetActorName(akActor)
  if actorName == ""
    Main.Warn("OnInventoryChanged - Actor has no name, skipping")
    inventoryMutex = False  ; Clear mutex before returning
    return false
  EndIf
  
  ; Check for burst protection if enabled
  if useInventoryBurstProtection
    float currentTime = Utility.GetCurrentRealTime()
    
    ; Get or create burst tracking data for this actor
    int burstData = GetBurstData(actorName)
    
    ; Check if we're in a new time window
    float startTime = JMap.getFlt(burstData, "startTime")
    if currentTime - startTime > inventoryBurstWindow
      ; Reset for new window
      JMap.setFlt(burstData, "startTime", currentTime)
      JMap.setInt(burstData, "count", 1)
      JMap.setInt(burstData, "throttled", 0)
    else
      ; Increment count in current window
      int eventCount = JMap.getInt(burstData, "count") + 1
      JMap.setInt(burstData, "count", eventCount)
      
      ; Check if we've exceeded threshold and should throttle
      if eventCount > inventoryEventThreshold
        if JMap.getInt(burstData, "throttled") == 0
          Main.Info("Inventory burst detected for " + actorName + " - Throttling and scheduling full scan")
          JMap.setInt(burstData, "throttled", 1)
          MarkActorForFullScan(akActor)
        EndIf
        
        ; If already throttled, skip processing this event
        inventoryMutex = False  ; Clear mutex before returning
        return true  ; Return true to indicate throttling
      EndIf
    EndIf
  EndIf
  
  ; Get basic item info for logging
  string itemName = akBaseItem.GetName()
  string formId = minai_Util.FormToHex(akBaseItem)
  if formId == ""
    formId = "0x" + akBaseItem.GetFormID()
  EndIf
  
  ; Track all inventory changes
  string changeType
  if abAdded
    changeType = "added"
  else
    changeType = "removed"
  endif
  Main.Debug("Inventory " + changeType + " for " + actorName + ": " + aiItemCount + "x " + itemName + " (" + formId + ")")
  
  ; Get the current actor inventory array
  int actorInventory = JMap.getObj(inventoryTracker, actorName)
  if !actorInventory
    ; Create new inventory object if none exists
    actorInventory = JArray.object()
    JValue.retain(actorInventory)
    JMap.setObj(inventoryTracker, actorName, actorInventory)
    Main.Debug("Created new inventory array for " + actorName)
  EndIf
  
  ; Get the actual current count from the actor
  int actualCount = akActor.GetItemCount(akBaseItem)
  
  ; Find the item's index in the array if it exists
  int itemIndex = JArray.findForm(actorInventory, akBaseItem)
  
  ; Update array based on current item count
  if actualCount > 0
    ; Add item if it's not already in the array
    if itemIndex == -1
      JArray.addForm(actorInventory, akBaseItem)
      Main.Debug("Added " + itemName + " to " + actorName + " inventory tracking")
    EndIf
  else
    ; Remove item if it's in the array and count is zero
    if itemIndex != -1
      JArray.eraseForm(actorInventory, akBaseItem)
      Main.Debug("Removed " + itemName + " from " + actorName + " inventory tracking")
    EndIf
  EndIf

  PersistInventory(akActor)
  
  inventoryMutex = False  ; Clear mutex before returning
  return false  ; Return false to indicate no throttling occurred
EndFunction

; Function to clean up inventory tracking data
Function CleanupInventoryTracker()
  if !inventoryTracker
    return
  EndIf
  
  string[] actorNames = JMap.allKeysPArray(inventoryTracker)
  int i = 0
  while i < actorNames.Length
    int inventory = JMap.getObj(inventoryTracker, actorNames[i])
    if inventory
      JValue.release(inventory)
    EndIf
    i += 1
  EndWhile
  
  JValue.release(inventoryTracker)
  inventoryTracker = JMap.object()
  JValue.retain(inventoryTracker)
  
  Main.Info("Cleaned up inventory tracker")
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


; Public function to check if an actor's inventory events are being throttled
; Other scripts can use this to decide whether to register for events
bool Function IsActorInventoryThrottled(actor akActor)
  if !akActor || !useInventoryBurstProtection
    return false
  EndIf
  
  return IsInventoryThrottled(akActor)
EndFunction

; Function to enable or disable inventory burst protection
Function SetInventoryBurstProtection(bool enable)
  useInventoryBurstProtection = enable
  
  if enable
    Main.Info("Inventory burst protection enabled")
  else
    Main.Info("Inventory burst protection disabled")
  EndIf
  
  ; If disabling, clear any throttled state
  if !enable
    string[] actorNames = JMap.allKeysPArray(inventoryBurstTracker)
    int i = 0
    while i < actorNames.Length
      int burstData = JMap.getObj(inventoryBurstTracker, actorNames[i])
      if burstData
        JMap.setInt(burstData, "throttled", 0)
      EndIf
      i += 1
    endwhile
    
    Main.Info("Cleared throttling state for all actors")
  EndIf
EndFunction

; Function to adjust burst detection parameters
Function SetBurstThresholdParameters(int threshold, float windowSeconds)
  if threshold > 0
    inventoryEventThreshold = threshold
  EndIf
  
  if windowSeconds > 0
    inventoryBurstWindow = windowSeconds
  EndIf
  
  Main.Info("Set inventory burst parameters: threshold=" + inventoryEventThreshold + ", window=" + inventoryBurstWindow + "s")
EndFunction

; Function to get the last update time for an actor's inventory in seconds
float Function GetActorInventoryLastUpdate(actor akActor)
  if !akActor
    return 0.0
  EndIf
  
  string actorName = Main.GetActorName(akActor)
  if actorName == ""
    return 0.0
  EndIf
  
  string updateKey = actorName + "_invUpdate"
  return JMap.getFlt(updateTracker, updateKey, 0.0)
EndFunction

; Function to set the last update time for an actor's inventory in seconds
Function SetActorInventoryLastUpdate(actor akActor, float currentTime)
  if !akActor
    return
  EndIf
  
  string actorName = Main.GetActorName(akActor)
  if actorName == ""
    return
  EndIf
  
  string updateKey = actorName + "_invUpdate"
  JMap.setFlt(updateTracker, updateKey, currentTime)
EndFunction

; Function to get the throttle time for an actor's inventory in seconds
float Function GetActorInventoryThrottle(actor akActor)
  if !akActor
    return inventoryUpdateThrottle
  EndIf
  
  string actorName = Main.GetActorName(akActor)
  if actorName == ""
    return inventoryUpdateThrottle
  EndIf
  
  string throttleKey = actorName + "_invThrottle"
  return JMap.getFlt(updateTracker, throttleKey, inventoryUpdateThrottle)
EndFunction
