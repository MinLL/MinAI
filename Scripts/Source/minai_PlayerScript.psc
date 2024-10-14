Scriptname minai_PlayerScript extends ReferenceAlias

minai_MainQuestController Property  MainQuestController  Auto
minai_AIFF aiff
minai_Followers followers
minai_Config config
bool bHasAIFF

Event OnPlayerLoadGame()
  RegisterForSleep()
  MainQuestController.Maintenance()
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  if (bHasAIFF)
    aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  endif
  followers = Game.GetFormFromFile(0x0913, "MinAI.esp") as minai_Followers
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
EndEvent

Event OnLocationChange(Location akOldLoc, Location akNewLoc)
  if (bHasAIFF)
    aiff.CleanupSapientActors()
  EndIf
endEvent


Event OnSleepStart(float afSleepStartTime, float afDesiredSleepEndTime)
  MainQuestController.Info("OnSleepStart()")
  if config.autoUpdateDiary
    followers.UpdateFollowerDiaries()
  EndIf
EndEvent
