scriptname minai_Followers extends Quest

minai_MainQuestController main
minai_AIFF aiff
minai_Sex  sex
minai_DeviousStuff devious
minai_Config config

actor playerRef
bool bHasAIFF
bool bHasNFF
  
Faction followerFaction
Faction nffFollowerFaction
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
  Main.Info("Initializing Followers Module.")
  RegisterForModEvent("CHIM_CommandReceived", "CommandDispatcher") ; Hook into AIFF - This is a separate quest, so we have to do this separately
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  bHasNFF = (Game.GetModByName("nwsFollowerFramework.esp") != 255)
  followerFaction = Game.GetFormFromFile(0x05C84E, "Skyrim.esm") as Faction
  if bHasNFF
    Main.Info("Found NFF")
    nff = Game.GetFormFromFile(0x00434F, "nwsFollowerFramework.esp") as nwsFollowerControllerScript
    nffFollowerFaction = Game.GetFormFromFile(0x094CC, "nwsFollowerFramework.esp") as Faction
    if !nff
      Main.Error("Could not load main NFF quest. Disabling NFF support")
      bHasNFF = False
    EndIf
  EndIf
  aiff.RegisterAction("ExtCmdStartLooting", "StartLooting", "Start Looting the Area", "Followers", 1, 0, 2, 5, 60, bHasNFF)
  aiff.RegisterAction("ExtCmdStopLooting", "StopLooting", "Stop Looting the Area", "Followers", 1, 0, 2, 5, 60, bHasNFF)
EndFunction


Function StartLooting()
  nff.CallLooting(true, false, false)
EndFunction


Function StopLooting()
  nff.CallLooting(false, True, false)
EndFunction


Event CommandDispatcher(String speakerName,String  command, String parameter)
  if !bHasAIFF
    return
  EndIf
  Main.Debug("Followers - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")
  if (command == "ExtCmdStartLooting")
    StartLooting()
    Main.RegisterEvent(speakerName + " started looting the area", "info_looting_start")
  elseif (command == "ExtCmdStopLooting")
    StartLooting()
    Main.RegisterEvent(speakerName + " stopped looting the area", "info_looting_stop")
  EndIf
EndEvent


Function SetContext(actor akTarget)
  Main.Debug("SetContext Followers(" + main.GetActorName(akTarget) + ")")
EndFunction


string Function GetKeywordsForActor(actor akTarget)
  return "";
EndFunction


string Function GetFactionsForActor(actor akTarget)
  return "";
EndFunction

Function UpdateFollowerDiaries()
  Main.Info("Updating all follower diaries")
  int i = 0
  bool didNarrator = false
  while i < nff.mcmScript.activeNames.Length
    string targetName = nff.mcmScript.activeNames[i]
    if targetName == "The Narrator"
      didNarrator = true
    EndIf
    Main.Debug("Updating diary for " + targetName)
    aiff.UpdateDiary(targetName)
    i += 1
  EndWhile
  if !didNarrator && config.UpdateNarratorDiary
    Main.Debug("Updating diary for The Narrator")
    aiff.UpdateDiary("PLAYER")
  EndIf
EndFunction



bool Function IsFollower(actor akTarget)
  return (nffFollowerFaction && akTarget.IsInFaction(nffFollowerFaction)) || akTarget.IsInFaction(followerFaction)
EndFunction
