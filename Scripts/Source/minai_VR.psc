scriptname minai_VR extends Quest


minai_MainQuestController main
minai_AIFF aiff
actor playerRef
bool bHasAIFF

string[] BreastNodes
string[] BellyNodes
string[] ButtNodes
string[] PenisNodes

float lastCollisionTime
float lastCollisionSpeechTime

GlobalVariable collisionCooldown
GlobalVariable collisionSpeechCooldown

function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  aiff = (Self as Quest) as minai_AIFF
  Main.Info("Initializing VR Module.")
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  collisionCooldown = Game.GetFormFromFile(0x090C, "MinAI.esp") as GlobalVariable
  collisionSpeechCooldown = Game.GetFormFromFile(0x090D, "MinAI.esp") as GlobalVariable
  RegisterForModEvent("CBPCPlayerCollisionWithFemaleEvent", "OnCollision")
  RegisterForModEvent("CBPCPlayerCollisionWithMaleEvent", "OnCollision")  
  RegisterForModEvent("CBPCPlayerGenitalCollisionWithFemaleEvent", "OnCollision")
  RegisterForModEvent("CBPCPlayerGenitalCollisionWithMaleEvent", "OnCollision")
  InitNodeDefinitions()
  lastCollisionTime = 0.0
  lastCollisionSpeechTime = 0.0
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
  
  BellyNodes = new String[2]
  BellyNodes[0] = "NPC Pelvis [Pelv]"
  BellyNodes[1] = "HDT Belly"

  penisNodes = new String[6]
  penisNodes[0] = "NPC Genitals01"
  penisNodes[1] = "NPC Genitals02"
  penisNodes[2] = "NPC Genitals03"
  penisNodes[3] = "NPC Genitals04"
  penisNodes[4] = "NPC Genitals05"
  penisNodes[5] = "NPC Genitals06"
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


Function ReportTouch(string playerName, string actorName, string nodeType, float collisionDuration, bool promptResponse)
  ; TODO: Differentiate between touch, grope, impact
  float currentTime = Utility.GetCurrentRealTime()
  string lineToSay = playerName + " touched " + actorName + "'s " + nodeType
  ; Outer cooldown for speech
  if currentTime - lastCollisionSpeechTime < collisionCooldown.GetValue() || !promptResponse
    ; Inner cooldown for reporting events
    if currentTime - lastCollisionTime < collisionCooldown.GetValue()
      Main.Debug("OnCollision - THROTTLED")
      return
    EndIf
    lastCollisionTime = currentTime  
    ; Register event if we're on cooldown instead of prompting the ai to respond
    Main.RegisterEvent(lineToSay)
  Else
    Main.Debug("Prompting AI to respond to " + lineToSay)
    AIAgentFunctions.requestMessageForActor(lineToSay, "chatnf_vr_1", actorName)
    lastCollisionSpeechTime = currentTime
  EndIf
EndFunction


Function OnCollision(string eventName, string nodeName, float collisionDuration, Form actorForm)
  Actor akActor = actorForm as Actor
  string actorName = akActor.GetDisplayName()
  string playerName = playerRef.GetDisplayName()
  
  string debugStr = "OnCollision(" + eventName + ", " + nodeName + ", " + collisionDuration + ", " + actorName + ")"
  main.Debug(debugStr)
  
  ; Debug.Notification(debugStr)
  if BreastNodes.Find(nodeName) >= 0
    ReportTouch(playerName, actorName, "Breasts", collisionDuration, true)
  elseif ButtNodes.Find(nodeName) >= 0
    ReportTouch(playerName, actorName, "Butt", collisionDuration, true)
  elseif BellyNodes.Find(nodeName) >= 0
    ReportTouch(playerName, actorName, "Belly", collisionDuration, true)
  elseif PenisNodes.Find(nodeName) >= 0
    ReportTouch(playerName, actorName, "Penis", collisionDuration, true)
  else
    ReportTouch(playerName, actorName, "Body Non-Sexually", collisionDuration, false)
  EndIf
EndFunction