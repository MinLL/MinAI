 scriptname minai_VR extends Quest


minai_MainQuestController main
minai_AIFF aiff
minai_Sex  sex
minai_DeviousStuff devious
minai_Config config
minai_Arousal arousal

actor playerRef
bool bHasAIFF

string[] BreastNodes
string[] BellyNodes
string[] ButtNodes
string[] PenisNodes
string[] VaginalNodes
string[] AnalNodes
string[] PromptKeys ; List of keys to trigger an AI reaction

float lastCollisionSpeechTime
float lastMoanTime
 
GlobalVariable useCBPC

int touchedLocations = 0

string BREASTS_KEY = "Breasts"
string BUTT_KEY = "Ass"
string VAGINAL_KEY = "Pussy"
string ANAL_KEY = "Anal"
string BELLY_KEY = "Belly"
string PENIS_KEY = "Penis"
string OTHER_KEY = "Body Non-Sexually"
string ACTOR_KEY = "ActorName"
string ACTORREF_KEY = "ActorRef"
string GENITAL_COLLISION_KEY = "GenitalCollision"

bool hitThreshold
float hitValue
string locationHit
bool collisionMutex
; How long a part has to be touched cumulatively within a collisionCooldown window to count

function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  if !config
    Main.Fatal("Could not load configuration - script version mismatch with esp")
  EndIf
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  sex = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_Sex
  devious = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_DeviousStuff
  arousal = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_Arousal
  Main.Info("Initializing VR Module.")
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)

  useCBPC = Game.GetFormFromFile(0x0910, "MinAI.esp") as GlobalVariable

  if useCBPC.GetValueInt() == 1
    Main.Info("Enabling CBPC")
    
    lastCollisionSpeechTime = 0.0
    lastMoanTime = 0.0
    if (touchedLocations == 0)
      Main.Debug("Initializing touched locations map")
      touchedLocations = JMap.Object()
      JValue.Retain(touchedLocations)
    EndIf
    ClearTouchedLocations()
    
    InitNodeDefinitions()
    RegisterForModEvent("CBPCPlayerCollisionWithFemaleEvent", "OnCollision")
    RegisterForModEvent("CBPCPlayerCollisionWithMaleEvent", "OnCollision")  
    RegisterForModEvent("CBPCPlayerGenitalCollisionWithFemaleEvent", "OnCollision")
    RegisterForModEvent("CBPCPlayerGenitalCollisionWithMaleEvent", "OnCollision")
    RegisterForSingleUpdate(config.collisionCooldown)
  Else
    Main.Info("CBPC is disabled")
  EndIf
EndFunction


Function InitNodeDefinitions()
  ButtNodes = new String[2]
  ButtNodes[0] = "NPC L Butt"
  ButtNodes[1] = "NPC R Butt"

  BreastNodes = new String[8]
  BreastNodes[0] = "L Breast01"
  BreastNodes[1] = "L Breast02"
  BreastNodes[2] = "L Breast03"
  BreastNodes[3] = "R Breast01"
  BreastNodes[4] = "R Breast02"
  BreastNodes[5] = "R Breast03"
  BreastNodes[6] = "NPC L Breast"
  BreastNodes[7] = "NPC R Breast"
  
  BellyNodes = new String[3]
  BellyNodes[0] = "HDT Belly"
  BellyNodes[1] = "NPC Spine1"
  BellyNodes[2] = "NPC Spine2"

  penisNodes = new String[9]
  penisNodes[0] = "NPC Genitals01"
  penisNodes[1] = "NPC Genitals02"
  penisNodes[2] = "NPC Genitals03"
  penisNodes[3] = "NPC Genitals04"
  penisNodes[4] = "NPC Genitals05"
  penisNodes[5] = "NPC Genitals06"
  penisNodes[6] = "SOSScrotum"
  penisNodes[7] = "NPC GenitalsScrotum"
  penisNodes[8] = "GenitalScrotumLag"

  vaginalNodes = new String[5]
  vaginalNodes[0] = "NPC Pelvis"
  vaginalNodes[1] = "NPC L Pussy02"
  vaginalNodes[2] = "NPC R Pussy02"
  vaginalNodes[3] = "Clitoral1"
  vaginalNodes[4] = "VaginaB1"

  analNodes = new String[1]
  analNodes[0] = "Anal"

  promptKeys = new String[6]
  promptKeys[0] = BREASTS_KEY
  promptKeys[1] = VAGINAL_KEY
  promptKeys[2] = ANAL_KEY
  promptKeys[3] = PENIS_KEY
  promptKeys[4] = BELLY_KEY
  promptKeys[5] = BUTT_KEY  
EndFunction


Event CommandDispatcher(String speakerName,String  command, String parameter)
  if !bHasAIFF
    return
  EndIf
  Main.Debug("VR - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")
  
EndEvent


Function SetContext(actor akTarget)
  
EndFunction


string Function GetKeywordsForActor(actor akTarget)
  return "";
EndFunction


string Function GetFactionsForActor(actor akTarget)
  return "";
EndFunction


Function TrackTouch(string nodeType, float collisionDuration, actor akActor)
  ; Does not track multiple actors at once. Good enough for now? Do we need this? Update interval is pretty quick.
  if hitThreshold
    return
  EndIf
  string actorName = Main.GetActorName(akActor)
  Float currentValue = JMap.GetFlt(touchedLocations, nodeType)
  Float newValue = currentValue + collisionDuration ; The longer the location is touched, the more it's weighted for the response
  Main.Debug("Tracking Touched Location (" + nodeType + ") for actor [" + actorName + "]: Before=" + currentValue + ", after=" + newValue)
  JMap.SetFlt(touchedLocations, nodeType, newValue)
  if akActor == playerRef && newValue > config.cbpcSelfTouchThreshold
    hitThreshold = True
    hitValue = newValue
    locationHit = nodeType
    Main.Debug("Hit Threshold (Player): "  + locationHit + "=" + hitValue)
  EndIf
  if akActor != playerRef && newValue > config.cbpcOtherTouchThreshold
    hitThreshold = True
    hitValue = newValue
    locationHit = nodeType
    Main.Debug("Hit Threshold (NPC): "  + locationHit + "=" + hitValue)
  EndIf
  if actorName == ""
    Main.Warn("TrackTouch received empty name - Defaulting to 'Someone'")
    JMap.SetStr(touchedLocations, ACTOR_KEY, "someone")
  Else
    JMap.SetStr(touchedLocations, ACTOR_KEY, actorName)
    JMap.SetForm(touchedLocations, ACTORREF_KEY, akActor)
  EndIf
EndFunction


Function OnCollision(string eventName, string nodeName, float collisionDuration, Form actorForm)
  if collisionMutex
    return
  EndIf
  collisionMutex = True
  if !useCBPC || useCBPC.GetValueInt() != 1
    UnregisterForUpdate()
    return
  EndIf
  if hitThreshold
    return
  EndIf
  Actor akActor = actorForm as Actor
  if (akActor.IsChild())
    Main.Warn(Main.GetActorName(akActor) + " is a child actor. Not processing collision.")
    collisionMutex = False
    return
  EndIf
  string actorName = Main.GetActorName(akActor)
  string playerName = Main.GetActorName(playerRef)
  
  string debugStr = "OnCollision(" + eventName + ", " + nodeName + ", " + collisionDuration + ", " + actorName + ")"
  main.Debug(debugStr)
  if actorName == "" ; This is not provided for non-VR users when they bump into an actor.
    Main.Warn("OnCollision() received empty actor name - Doing nothing.")
    return
  EndIf
  if akActor == playerRef && config.cbpcDisableSelfTouch
    Main.Debug("Self touch is disabled.")
    return
  EndIf  
  if devious.hasDD() && akActor == playerRef && playerRef.WornHasKeyword(devious.libs.zad_DeviousHeavyBondage)
    Main.Debug("Not processing self-collision data for player: Has DD heavy bondage on")
    return
  EndIf
  if akActor == playerRef && playerRef.IsWeaponDrawn()
    Main.Debug("Not processing self-collision data for player: Has weapon drawn")
    return
  EndIf
  ; Debug.Notification(debugStr)
  if BreastNodes.Find(nodeName) >= 0
    TrackTouch(BREASTS_KEY, collisionDuration, akActor)
  elseif ButtNodes.Find(nodeName) >= 0
    if akActor == playerRef && !config.cbpcDisableSelfAssTouch
      TrackTouch(BUTT_KEY, collisionDuration, akActor)
    EndIf
  elseif BellyNodes.Find(nodeName) >= 0
    TrackTouch(BELLY_KEY, collisionDuration, akActor)
  elseif PenisNodes.Find(nodeName) >= 0
    TrackTouch(PENIS_KEY, collisionDuration, akActor)
  elseif AnalNodes.Find(nodeName) >= 0
    TrackTouch(ANAL_KEY, collisionDuration, akActor)
  elseif VaginalNodes.Find(nodeName) >= 0
    TrackTouch(VAGINAL_KEY, collisionDuration, akActor)
  else
    TrackTouch(OTHER_KEY, collisionDuration, akActor)
  EndIf
  if eventName == "CBPCPlayerGenitalCollisionWithFemaleEvent" || eventName == "CBPCPlayerGenitalCollisionWithMaleEvent"
    JMap.setInt(touchedLocations, GENITAL_COLLISION_KEY, 1)
  EndIf
  collisionMutex = False
EndFunction


Function ClearTouchedLocations()
  JMap.SetStr(touchedLocations, ACTOR_KEY, "")
  JMap.SetStr(touchedLocations, ACTORREF_KEY, None)
  JMap.SetFlt(touchedLocations, BREASTS_KEY, 0.0)
  JMap.SetFlt(touchedLocations, VAGINAL_KEY, 0.0)
  JMap.SetFlt(touchedLocations, ANAL_KEY, 0.0)
  JMap.SetFlt(touchedLocations, BELLY_KEY, 0.0)
  JMap.SetFlt(touchedLocations, PENIS_KEY, 0.0)
  JMap.SetFlt(touchedLocations, OTHER_KEY, 0.0)
  JMap.setInt(touchedLocations, GENITAL_COLLISION_KEY, 0)
  hitThreshold = False
  locationHit = ""
  hitValue = 0.0
  collisionMutex = False
EndFunction


Event OnUpdate()
  if useCBPC.GetValueInt() != 1
    Main.Warn("CBPC: Aborting update, cbpc is disabled")
    return
  EndIf
  if !hitThreshold
    ClearTouchedLocations()
    RegisterForSingleUpdate(config.collisionCooldown)
    return
  EndIf
  string actorName = JMap.GetStr(touchedLocations, ACTOR_KEY)
  Actor akActor = JMap.GetForm(touchedLocations, ACTORREF_KEY) as Actor
  string playerName = Main.GetActorName(playerRef)
  if actorName == ""
    Main.Debug("No actor touched for collision")
    ClearTouchedLocations()
    RegisterForSingleUpdate(config.collisionCooldown)
    return
  EndIf

  if config.cbpcDisableSelfAssTouch && locationHit == BUTT_KEY && actorName == playerName
    Main.Debug("Self ass touch is disabled and target node is butt key. Doing nothing.")
    RegisterForSingleUpdate(config.collisionCooldown)
    return
  EndIf
  
  Main.Debug("Most touched location (" + actorName + "): " + hitThreshold + " = " + hitValue)
  string lineToSay = ""
  bool wasPenetration = (JMap.GetInt(touchedLocations, GENITAL_COLLISION_KEY) == 1)
  bool bHasDD = devious.hasDD() 
  if actorName == playerName
    if locationHit == VAGINAL_KEY && bHasDD && playerRef.WornHasKeyword(devious.libs.zad_DeviousBelt)
      lineToSay = playerName + " tried to touch their " + locationHit + " but was prevented from doing so by the chastity belt"
    elseif locationHit == BREASTS_KEY && bHasDD && playerRef.WornHasKeyword(devious.libs.zad_DeviousBra)
      lineToSay = playerName + " tried to touch their " + locationHit + " but was prevented from doing so by the chastity bra"
    else
      lineToSay = playerName + " touched their " + locationHit
    EndIf
  Else
    if wasPenetration && playerRef.GetActorBase().GetSex() == 0
      lineToSay = playerName + "'s " + PENIS_KEY + " touched " + actorName + "'s " + locationHit
    elseif wasPenetration && playerRef.GetActorBase().GetSex() >= 1
      lineToSay = playerName + "'s " + VAGINAL_KEY + " was touched by " + actorName + "'s " + locationHit
    else
      if locationHit == VAGINAL_KEY && bHasDD && akActor.WornHasKeyword(devious.libs.zad_DeviousBelt)
        lineToSay = playerName + " tried to touch " + actorName + "'s " + locationHit + " but was prevented from doing so by the chastity belt"
      elseif locationHit == BREASTS_KEY && bHasDD && akActor.WornHasKeyword(devious.libs.zad_DeviousBra)
        lineToSay = playerName + " tried to touch " + actorName + "'s " + locationHit + " but was prevented from doing so by the chastity bra"
      else
        lineToSay = playerName + " touched " + actorName + "'s " + locationHit
      EndIf
    endif
  EndIf
  float currentTime = Utility.GetCurrentRealTime()
  float cooldown = 0.0
  if !sex.CanAnimate(playerRef, playerRef) ; Enforce different cooldown during sex
    cooldown = config.collisionSexCooldown
  Else
    cooldown = config.collisionSpeechCooldown
  EndIf
  lineToSay = "#PHYSICS_INFO " + lineToSay
  if currentTime - lastCollisionSpeechTime < cooldown || !(PromptKeys.Find(locationHit) >= 0) || !bHasAIFF
    Main.RegisterEvent(lineToSay)
  Else
    ; Prompt AIFF to comment on it.
    main.RequestLLMResponse(lineToSay, "chatnf_vr_1", actorName)
    lastCollisionSpeechTime = currentTime
  EndIf
  ProcessArousal(akActor)
  ClearTouchedLocations()
  RegisterForSingleUpdate(config.collisionCooldown)
EndEvent

Function ProcessArousal(actor akActor)
  if !akActor
    return
  EndIf
  Main.Debug("CBPC: Updating arousal for actor " + Main.GetActorName(akActor))
  if locationHit == VAGINAL_KEY
    arousal.UpdateArousal(akActor, 2)
  elseif locationHit == ANAL_KEY
    arousal.UpdateArousal(akActor, 2)
  elseif locationHit == PENIS_KEY
    arousal.UpdateArousal(akActor, 2)
  elseif locationHit == BREASTS_KEY
    arousal.UpdateArousal(akActor, 1)
  elseif locationHit == BUTT_KEY
    arousal.UpdateArousal(akActor, 1)
  EndIf
  if devious.HasDD()
    float currentTime = Utility.GetCurrentRealTime()
    if currentTime - lastMoanTime > 8
      Main.RegisterEvent(Main.GetActorName(akActor) + " moaned due to being touched")
      lastMoanTime = currentTime
      devious.libs.Moan(akActor)
    EndIf
  EndIf
EndFunction

function ResetSpeechCooldowns()
  lastCollisionSpeechTime = 0
EndFunction