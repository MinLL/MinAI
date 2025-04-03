scriptname minai_CombatDetectionEffect extends ActiveMagicEffect

minai_AIFF aiff
minai_MainQuestController main
minai_CombatManager combat
actor playerRef

; Will this be enough to keep this performant?
bool OnHitMutex = false
float LastHitTime = 0.0
float HitThrottleTime = 1.0  ; Throttle to 1.0 seconds


String Function ActorArrayToString(Actor[] actors) global
  String result = ""
  int maxActors = 10
  int i = 0
  while i < actors.Length && i < maxActors
    if actors[i]
      if result != ""
        result += "~"
      endif
      result += actors[i].GetDisplayName()
    endif
    i += 1
  endwhile
  return result
EndFunction
  
Event OnEffectStart(Actor akTarget, Actor akCaster)
  main = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_MainQuestController
  combat = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_CombatManager
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  playerRef = Game.GetPlayer()
  lastHitTime = 0.0

  if (!akTarget || !main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] Skipping OnEffectStart, not ready")
    return
  EndIf
  string targetName = Main.GetActorName(akTarget)
  main.Debug("Combat Detection: (" + targetName +") entered combat")
  
  ; Store combat allies and targets as strings
  Actor[] allies = PO3_SKSEFunctions.GetCombatAllies(akTarget)
  Actor[] targets = PO3_SKSEFunctions.GetCombatTargets(akTarget)
  aiff.SetActorVariable(akTarget, "combatAllies", ActorArrayToString(allies))
  aiff.SetActorVariable(akTarget, "combatTargets", ActorArrayToString(targets))
  
  if playerRef != akTarget
    aiff.SetActorVariable(akTarget, "hostileToPlayer", akTarget.IsHostileToActor(playerRef))
  else
    main.PlayerInCombat = true
  EndIf
  combat.OnCombatStart(akTarget)
  aiff.SetActorVariable(akTarget, "inCombat", true)
EndEvent

Event OnEffectFinish(Actor akTarget, Actor akCaster)
  string targetName = Main.GetActorName(akTarget)
  main.Debug("Combat Detection: (" + targetName +") left combat")
  
  ; Clear combat allies and targets
  aiff.SetActorVariable(akTarget, "combatAllies", "")
  aiff.SetActorVariable(akTarget, "combatTargets", "")
  
  aiff.SetActorVariable(akTarget, "inCombat", false)
  if playerRef != akTarget
    aiff.SetActorVariable(akTarget, "hostileToPlayer", false)
  else
    main.PlayerInCombat = false
  EndIf
  combat.OnCombatEnd(akTarget)
EndEvent


Event OnHit(ObjectReference akAggressor, Form akSource, Projectile akProjectile, bool abPowerAttack, bool abSneakAttack, bool abBashAttack, bool abHitBlocked)
  if OnHitMutex
    return
  EndIf
  
  float currentTime = Utility.GetCurrentGameTime()
  if (currentTime - LastHitTime) < HitThrottleTime
    return
  EndIf
  
  OnHitMutex = true
  LastHitTime = currentTime
  actor akTarget = GetTargetActor()
  if akTarget
    combat.SetVitals(akTarget)
  endif
  OnHitMutex = false
EndEvent


