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

	
Function OnEffectStart(Actor akTarget, Actor akCaster)
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
  int updateTime = 1 + Utility.RandomInt(0, 2)
  RegisterForSingleUpdate(updateTime)
EndFunction

; OnEffectStop is not reliably called. Handle cleanup elsewhere.

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
    UnregisterForUpdate()
    return
  endif
  ; Immediately store the targets voice to avoid the delay on context due to jitter to make sure they can respond immediately
  aiff.StoreActorVoice(akTarget)
  ; Enable AI
  aiff.EnableActorAI(akTarget)
EndEvent
