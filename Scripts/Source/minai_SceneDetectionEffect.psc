scriptname minai_SceneDetectionEffect extends ActiveMagicEffect


minai_AIFF aiff
minai_MainQuestController main

Event OnEffectStart(Actor akTarget, Actor akCaster)
  main = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_MainQuestController
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  if (!akTarget || !main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] Skipping OnEffectStart, not ready")
    return
  EndIf
  string targetName = Main.GetActorName(akTarget)
  main.Debug("Scene Detection: (" + targetName +") entered scene")
  aiff.SetActorVariable(akTarget, "scene", akTarget.GetCurrentScene())
EndEvent


Event OnEffectFinish(Actor akTarget, Actor akCaster)
  string targetName = Main.GetActorName(akTarget)
  main.Debug("Scene Detection: (" + targetName +") left scene")
  aiff.SetActorVariable(akTarget, "scene", akTarget.GetCurrentScene())
EndEvent

