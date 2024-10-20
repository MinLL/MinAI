scriptname minai_CombatDetectionEffect extends ActiveMagicEffect


minai_AIFF aiff
minai_MainQuestController main
actor playerRef
  
Event OnEffectStart(Actor akTarget, Actor akCaster)
  main = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_MainQuestController
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  playerRef = Game.GetPlayer()
  if (!akTarget || !main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] Skipping OnEffectStart, not ready")
    return
  EndIf
  string targetName = Main.GetActorName(akTarget)
  main.Debug("Combat Detection: (" + targetName +") entered combat")
  if playerRef != akTarget
    aiff.SetActorVariable(akTarget, "hostileToPlayer", akTarget.IsHostileToActor(playerRef))
  else
    main.PlayerInCombat = true
  EndIf
  aiff.SetActorVariable(akTarget, "inCombat", true)
EndEvent


Event OnEffectFinish(Actor akTarget, Actor akCaster)
  string targetName = Main.GetActorName(akTarget)
  main.Debug("Combat Detection: (" + targetName +") left combat")
  aiff.SetActorVariable(akTarget, "inCombat", false)
  if playerRef != akTarget
    aiff.SetActorVariable(akTarget, "hostileToPlayer", false)
  else
    main.PlayerInCombat = false
  EndIf
EndEvent

