scriptname minai_CombatManager extends Quest

minai_MainQuestController main
minai_AIFF aiff
minai_Sex  sex
minai_DeviousStuff devious
minai_Config config
minai_Followers followers
DefeatConfig Defeat
  
actor playerRef
bool bHasAIFF
bool bHasNFF
bool bHasDefeat
  
nwsFollowerControllerScript nff

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
  followers = Game.GetFormFromFile(0x0913, "MinAI.esp") as minai_Followers
  Main.Info("Initializing Combat Management Module.")
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  bHasDefeat = (Game.GetModByName("SexlabDefeat.esp") != 255)
  if bHasDefeat
    Defeat = DefeatUtil.GetDefeat()
  EndIf
  RegisterForModEvent("DefeatPostAssault", "OnDefeat")
  RegisterForModEvent("da_StartRecoverSequence", "OnDefeatRecoverSequence")
  RegisterForModEvent("AnimationEnd_Defeat", "OnDefeatAnimationEnd")
  aiff.SetModAvailable("SexlabDefeat", bHasDefeat)
EndFunction

event OnDefeatRecoverSequence(Form sender, Form theForm, int theInt, string theString)
  main.Info("OnDefeatRecoverSequence(" + sender + ", " + theInt + ", " + theString + ")")
EndEvent


event OnDefeat(String str1, String str2, Float theFloat, form def)
  main.Info("OnDefeat(" + str1 + ", " + str2 +"," + theFloat + ", " + def + ")")
EndEvent

event OnDefeatAnimationEnd(Form sender, Form theForm, int theInt, string theString)
  main.Info("OnDefeatAnimationEnd(" + sender + ", " + theInt + ", " + theString + ")")
EndEvent


Event CommandDispatcher(String speakerName,String  command, String parameter)
  if !bHasAIFF
    return
  EndIf

EndEvent


Function SetContext(actor akTarget)
  Main.Debug("SetContext CombatManager(" + main.GetActorName(akTarget) + ")")
  aiff.SetActorVariable(akTarget, "inCombat", akTarget.GetCombatState() >= 1)
EndFunction


string Function GetKeywordsForActor(actor akTarget)
  return "";
EndFunction


string Function GetFactionsForActor(actor akTarget)
  return "";
EndFunction


Function OnCombatStart(actor akTarget)
  Main.Info("Combat: OnCombatStart()")
EndFunction

Function OnCombatEnd(actor akTarget)
  Main.Info("Combat: OnCombatEnd()")
  if !bHasDefeat || !bHasAIFF
    return
  EndIf
  bool defeated = Defeat.IsDefeatActive(akTarget)
  if (defeated && akTarget == playerRef)
    AIAgentFunctions.requestMessage("The party was defeated in combat", "minai_combatenddefeat")
   elseif (!defeated && akTarget == playerRef)
    AIAgentFunctions.requestMessage("The party was victorious in combat", "minai_combatendvictory")
  elseif (defeated && followers.IsFollower(akTarget))
    AIAgentFunctions.requestMessage(Main.GetActorName(akTarget) + " was defeated in combat", "minai_combatenddefeat")
  EndIf
EndFunction

Function OnBleedoutStart(actor akTarget)
  Main.Info("Combat: OnBleedoutStart()")
  if akTarget != playerRef
    string targetName = Main.GetActorName(akTarget)
    Main.RequestLLMResponseFromActor(targetName + " has been knocked down and is badly injured!", "minai_bleedoutself", targetName, "npc")
  EndIf
EndFunction

Function OnBleedoutEnd(actor akTarget)
  Main.Info("Combat: OnBleedoutEnd()")
EndFunction