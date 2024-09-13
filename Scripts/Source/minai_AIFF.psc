scriptname minai_AIFF extends Quest

; Modules
minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious



Actor player

minai_MainQuestController main
Function Maintenance(minai_MainQuestController _main)
  main = _main
  player = Game.GetPlayer()
  Debug.Trace("[minai] - Initializing for AIFF.")

  sex = (Self as Quest)as minai_Sex
  survival = (Self as Quest)as minai_Survival
  arousal = (Self as Quest)as minai_Arousal
  devious = (Self as Quest)as minai_DeviousStuff
  SetContext()
EndFunction



Function SetContext()
  Debug.Trace("[minai] AIFF - SetContext()")
  devious.SetContext(player)
EndFunction