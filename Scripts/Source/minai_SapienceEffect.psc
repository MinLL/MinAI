scriptname minai_SapienceEffect extends ActiveMagicEffect


minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious
minai_AIFF aiff
minai_MainQuestController main
Spell SapienceSpell
Spell ContextSpell
GlobalVariable minai_SapienceEnabled

	
Event OnEffectStart(Actor akTarget, Actor akCaster)
  main = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_MainQuestController
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  SapienceSpell = Game.GetFormFromFile(0x0917, "MinAI.esp") as Spell
  ContextSpell = Game.GetFormFromFile(0x090A, "MinAI.esp") as Spell
  minai_SapienceEnabled = Game.GetFormFromFile(0x091A, "MinAI.esp") as GlobalVariable
  if (!akTarget || !main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] SAPIENCE: Skipping OnEffectStart, not ready")
    return
  EndIf
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
  string targetName = Main.GetActorName(akTarget)
  Main.Debug("SAPIENCE Processing ( " + targetName + ") for removal")
  if (!main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] SAPIENCE: Skipping OnEffectFinish, not ready")
    return
  EndIf
  aiff.RemoveActorAI(targetName)
EndEvent


Event OnUpdate()
  actor akTarget = GetTargetActor()
  if !akTarget
    Main.Warn("SAPIENCE: Could not find target actor. Aborting.")
    return
  EndIf
  string targetName = Main.GetActorName(akTarget)
  Main.Debug("SAPIENCE Processing (" + targetName +")")
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
