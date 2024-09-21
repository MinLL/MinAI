 scriptname minai_VR extends Quest


minai_MainQuestController main
minai_AIFF aiff
minai_Sex  sex
minai_DeviousStuff devious

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

GlobalVariable collisionCooldown
GlobalVariable collisionSpeechCooldown
GlobalVariable collisionSexCooldown
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
string GENITAL_COLLISION_KEY = "GenitalCollision"

; How long a part has to be touched cumulatively within a collisionCooldown window to count
float TOUCH_THRESHOLD = 1.0
float PLAYER_TOUCH_THRESHOLD = 4.0

function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  sex = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_Sex
  devious = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_DeviousStuff
  Main.Info("Initializing VR Module.")
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  collisionCooldown = Game.GetFormFromFile(0x090C, "MinAI.esp") as GlobalVariable
  collisionSpeechCooldown = Game.GetFormFromFile(0x090D, "MinAI.esp") as GlobalVariable
  collisionSexCooldown = Game.GetFormFromFile(0x090F, "MinAI.esp") as GlobalVariable
  useCBPC = Game.GetFormFromFile(0x0910, "MinAI.esp") as GlobalVariable

  if useCBPC.GetValueInt() == 1
    Main.Info("Enabling CBPC")
    RegisterForModEvent("CBPCPlayerCollisionWithFemaleEvent", "OnCollision")
    RegisterForModEvent("CBPCPlayerCollisionWithMaleEvent", "OnCollision")  
    RegisterForModEvent("CBPCPlayerGenitalCollisionWithFemaleEvent", "OnCollision")
    RegisterForModEvent("CBPCPlayerGenitalCollisionWithMaleEvent", "OnCollision")
    InitNodeDefinitions()
    lastCollisionSpeechTime = 0.0
    if (touchedLocations == 0)
      Main.Debug("Initializing touched locations map")
      touchedLocations = JMap.Object()
      JValue.Retain(touchedLocations)
    EndIf
    ClearTouchedLocations()
    
    RegisterForSingleUpdate(collisionCooldown.GetValue())
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


Function TrackTouch(string nodeType, float collisionDuration, string actorName)
  ; Does not track multiple actors at once. Good enough for now? Do we need this? Update interval is pretty quick.
  Float currentValue = JMap.GetFlt(touchedLocations, nodeType)
  Float newValue = currentValue + collisionDuration ; The longer the location is touched, the more it's weighted for the response
  Main.Debug("Tracking Touched Location (" + nodeType + ") for actor [" + actorName + "]: Before=" + currentValue + ", after=" + newValue)
  JMap.SetFlt(touchedLocations, nodeType, newValue)
  if actorName == ""
    Main.Warn("TrackTouch received empty name - Defaulting to 'Someone'")
    JMap.SetStr(touchedLocations, ACTOR_KEY, "someone")
  Else
    JMap.SetStr(touchedLocations, ACTOR_KEY, actorName)
  EndIf
EndFunction


Function OnCollision(string eventName, string nodeName, float collisionDuration, Form actorForm)
  if useCBPC.GetValueInt() != 1
    Main.Warn("CBPC: Aborting OnCollision(), cbpc is disabled")
    return
  EndIf
  Actor akActor = actorForm as Actor
  string actorName = akActor.GetActorBase().GetName()
  string playerName = playerRef.GetActorBase().GetName()
  
  string debugStr = "OnCollision(" + eventName + ", " + nodeName + ", " + collisionDuration + ", " + actorName + ")"
  main.Debug(debugStr)
  if actorName == "" ; This is not provided for non-VR users when they bump into an actor.
    Main.Warn("OnCollision() received empty actor name - Doing nothing.")
    return
  EndIf
  if akActor == playerRef && playerRef.WornHasKeyword(devious.libs.zad_DeviousHeavyBondage)
    Main.Debug("Not processing self-collision data for player: Has DD heavy bondage on")
    return
  EndIf
  ; Debug.Notification(debugStr)
  if BreastNodes.Find(nodeName) >= 0
    TrackTouch(BREASTS_KEY, collisionDuration, actorName)
  elseif ButtNodes.Find(nodeName) >= 0
    TrackTouch(BUTT_KEY, collisionDuration, actorName)
  elseif BellyNodes.Find(nodeName) >= 0
    TrackTouch(BELLY_KEY, collisionDuration, actorName)
  elseif PenisNodes.Find(nodeName) >= 0
    TrackTouch(PENIS_KEY, collisionDuration, actorName)
  elseif AnalNodes.Find(nodeName) >= 0
    TrackTouch(ANAL_KEY, collisionDuration, actorName)
  elseif VaginalNodes.Find(nodeName) >= 0
    TrackTouch(VAGINAL_KEY, collisionDuration, actorName)
  else
    TrackTouch(OTHER_KEY, collisionDuration, actorName)
  EndIf
  if eventName == "CBPCPlayerGenitalCollisionWithFemaleEvent" || eventName == "CBPCPlayerGenitalCollisionWithMaleEvent"
    JMap.setInt(touchedLocations, GENITAL_COLLISION_KEY, 1)
  EndIf
EndFunction


Function ClearTouchedLocations()
  JMap.SetStr(touchedLocations, ACTOR_KEY, "")
  JMap.SetFlt(touchedLocations, BREASTS_KEY, 0.0)
  JMap.SetFlt(touchedLocations, VAGINAL_KEY, 0.0)
  JMap.SetFlt(touchedLocations, ANAL_KEY, 0.0)
  JMap.SetFlt(touchedLocations, BELLY_KEY, 0.0)
  JMap.SetFlt(touchedLocations, PENIS_KEY, 0.0)
  JMap.SetFlt(touchedLocations, OTHER_KEY, 0.0)
  JMap.setInt(touchedLocations, GENITAL_COLLISION_KEY, 0)
EndFunction


Event OnUpdate()
  if useCBPC.GetValueInt() != 1
    Main.Warn("CBPC: Aborting update, cbpc is disabled")
    return
  EndIf
  string actorName = JMap.GetStr(touchedLocations, ACTOR_KEY)
  string playerName = playerRef.GetActorBase().GetName()
  if actorName == ""
    Main.Debug("No actor touched for collision")
    ClearTouchedLocations()
    RegisterForSingleUpdate(collisionCooldown.GetValue())
    return
  EndIf
  int[] locations = new int[7]
  string[] locationStr = new String[7]
  locations[0] = JMap.GetInt(touchedLocations, BREASTS_KEY)
  locations[1] = JMap.GetInt(touchedLocations, BUTT_KEY)
  locations[2] = JMap.GetInt(touchedLocations, BELLY_KEY)
  locations[3] = JMap.GetInt(touchedLocations, PENIS_KEY)
  locations[4] = JMap.GetInt(touchedLocations, ANAL_KEY)
  locations[5] = JMap.GetInt(touchedLocations, VAGINAL_KEY)
  locations[6] = JMap.GetInt(touchedLocations, OTHER_KEY)
  ClearTouchedLocations()
  locationStr[0] = BREASTS_KEY
  locationStr[1] = BUTT_KEY
  locationStr[2] = BELLY_KEY
  locationStr[3] = PENIS_KEY
  locationStr[4] = ANAL_KEY
  locationStr[5] = VAGINAL_KEY
  locationStr[6] = OTHER_KEY
  
  ; Find most touched locations
  int index = 1
  int currentValue
  string currentValueStr
  While index < locations.Length
    currentValue = locations[index]
    currentValueStr = locationStr[index]
    int position = index
    While (position > 0 && locations[position - 1] < currentValue)
      locations[position] = locations[position - 1]
      locationStr[position] = locationStr[position - 1]
      position = position - 1
    EndWhile
    locations[position] = currentValue
    locationStr[position] = currentValueStr
    index += 1
  EndWhile

  string targetNode = locationStr[0]
  int targetValue = locations[0]
  ; Prefer non-generic locations
  if locationStr[0] == OTHER_KEY && locations[1] != 0.0
    targetNode = locationStr[1]
    targetValue = locations[1]
  EndIf

  if ((targetValue > TOUCH_THRESHOLD && actorName != playerName) || (targetValue > PLAYER_TOUCH_THRESHOLD && actorName == playerName ) && actorName != "")
    Main.Debug("Most touched location (" + actorName + "): " + locationStr[0] + " = " + locations[0])
    string lineToSay = ""
    bool wasPenetration = (JMap.GetInt(touchedLocations, GENITAL_COLLISION_KEY) == 1)
    if actorName == playerName
      lineToSay = playerName + " touched their " + targetNode
    Else
      if wasPenetration && playerRef.GetActorBase().GetSex() == 0
        lineToSay = playerName + "'s " + PENIS_KEY + " touched " + actorName + "'s " + targetNode
      elseif wasPenetration && playerRef.GetActorBase().GetSex() >= 1
        lineToSay = playerName + "'s " + VAGINAL_KEY + " was touched by " + actorName + "'s " + targetNode
      else
        lineToSay = playerName + " touched " + actorName + "'s " + targetNode
      endif
    EndIf
    float currentTime = Utility.GetCurrentRealTime()
    float cooldown = 0.0
    if !sex.CanAnimate(playerRef, playerRef) ; Enforce different cooldown during sex
      cooldown = collisionSexCooldown.GetValue()
    Else
      cooldown = collisionSpeechCooldown.GetValue()
    EndIf
    if currentTime - lastCollisionSpeechTime < cooldown || !(PromptKeys.Find(targetNode) >= 0) || !bHasAIFF
      Main.RegisterEvent(lineToSay)
    Else
      ; Prompt AIFF to comment on it.
      Main.Debug("Requesting reaction from AI for: " + lineToSay)
      AIAgentFunctions.requestMessageForActor(lineToSay, "chatnf_vr_1", actorName)
      lastCollisionSpeechTime = currentTime
    EndIf
  EndIf
  RegisterForSingleUpdate(collisionCooldown.GetValue())
EndEvent


function ResetSpeechCooldowns()
  lastCollisionSpeechTime = 0
EndFunction