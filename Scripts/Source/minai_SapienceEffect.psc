scriptname minai_SapienceEffect extends ActiveMagicEffect


minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious
minai_AIFF aiff
minai_Followers followers 
minai_MainQuestController main
Spell SapienceSpell
Spell ContextSpell
GlobalVariable minai_SapienceEnabled
actor playerRef
	
Event OnEffectStart(Actor akTarget, Actor akCaster)
  main = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_MainQuestController
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  followers = Game.GetFormFromFile(0x0913, "MinAI.esp") as minai_Followers
  SapienceSpell = Game.GetFormFromFile(0x0917, "MinAI.esp") as Spell
  ContextSpell = Game.GetFormFromFile(0x090A, "MinAI.esp") as Spell
  minai_SapienceEnabled = Game.GetFormFromFile(0x091A, "MinAI.esp") as GlobalVariable
  if (!akTarget || !main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] SAPIENCE: Skipping OnEffectStart, not ready")
    return
  EndIf
  playerRef = Game.GetPlayer()
  string targetName = Main.GetActorName(akTarget)
  main.Debug("SAPIENCE: OnEffectStart(" + targetName +")")
  ; Do one update for actors the first time we enter a zone. Introduce a little jitter to distribute load.
  float updateTime = Utility.RandomFloat(0.1, 1.5)
  RegisterForSingleUpdate(updateTime)
EndEvent

Event OnEffectFinish(Actor akTarget, Actor akCaster)
  if !akTarget
    Main.Warn("SAPIENCE: Could not find target actor. Aborting.")
    return
  EndIf
  if followers.IsFollower(akTarget)
    Main.Debug("SAPIENCE: Aborting. " + targetName + " is a follower.")
    return
  EndIf
  string targetName = Main.GetActorName(akTarget)
  Main.Debug("SAPIENCE Processing ( " + targetName + ") for removal")
  if (!main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] SAPIENCE: Skipping OnEffectFinish, not ready")
    return
  EndIf
  ; It's okay if the actor doesn't end up getting cleaned up here, they'll get cleaned up on location change.
  ; This prevents the actor from flapping in and out of ai control when the magic effect falls off for some reason
  if (ShouldRemoveActor(akTarget))
    aiff.RemoveActorAI(targetName)
  else
    Main.Debug("Not removing actor " + targetName)
  EndIf
EndEvent

bool Function ShouldRemoveActor(actor akTarget)
  if (!playerRef || !akTarget)
    return true
  EndIf
  bool distanceCheck = (akTarget.GetDistance(playerRef) <= 1024)
  bool isAlive = (akTarget.GetKiller() == None)
  ; bool isNotHostile = !(akTarget.IsHostileToActor(playerRef))
  bool isSapienceEnabled = (minai_SapienceEnabled.GetValueInt() == 1)
  bool losCheck = akTarget.HasLOS(playerRef)
  Main.Debug("ShouldRemoveActor(" + Main.GetActorName(akTarget) + "): distanceCheck=" + distanceCheck +", isAlive=" + isAlive +  ", sapienceEnabled=" + isSapienceEnabled)
  return (!distanceCheck || !isAlive || !isSapienceEnabled || !losCheck) ; || !isNotHostile
EndFunction


Event OnUpdate()
  actor akTarget = GetTargetActor()
  if !akTarget
    Main.Warn("SAPIENCE: Could not find target actor. Aborting.")
    return
  EndIf
  string targetName = Main.GetActorName(akTarget)
  Main.Debug("SAPIENCE Processing (" + targetName +")")
  if followers.IsFollower(akTarget)
    Main.Debug("SAPIENCE: Aborting. " + targetName + " is a follower.")
    return
  EndIf
  if(!aiff || !akTarget.Is3DLoaded())
    Main.Debug("SAPIENCE Processing( " + targetName + ") Stopping OnUpdate for actor - actor is not loaded.")
    aiff.RemoveActorAI(targetName)
    return
  endif
  ; Immediately store the targets voice to avoid the delay on context due to jitter to make sure they can respond immediately
  aiff.StoreActorVoice(akTarget)
  ; Enable AI
  if StringUtil.GetLength(Main.GetActorName(akTarget)) > 1 ; Filter out mannequins and invisible NPC's some mods add
    aiff.EnableActorAI(akTarget)
  EndIf
EndEvent
