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

Function RemoveAI(actor akTarget)
  string targetName = Main.GetActorName(akTarget)
  Actor agent = AIAgentFunctions.getAgentByName(targetName)
  if agent
    Main.Info("SAPIENCE: Removing " + targetName + " from AI")
    AIAgentFunctions.setDrivenByAIA(akTarget, false)
    akTarget.RemoveSpell(ContextSpell)
  EndIf
EndFunction

Function EnableAI(actor akTarget)
  string targetName = Main.GetActorName(akTarget)
  Actor agent = AIAgentFunctions.getAgentByName(targetName)
  if !agent
    Main.Info("SAPIENCE: Adding " + targetName + " to AI")
    AIAgentFunctions.setDrivenByAIA(akTarget, false)
    aiff.TrackContext(akTarget)
  EndIf
EndFunction

Function OnEffectStop(Actor akTarget, Actor akCaster)
  if (!akTarget || !main || !aiff || !aiff.IsInitialized())
    Debug.Trace("[minai] SAPIENCE: Skipping OnEffectStop, not ready")
    return
  EndIf
  string targetName = Main.GetActorName(akTarget)
  main.Debug("SAPIENCE: OnEffectStop(" + targetName +")")
  RemoveAI(akTarget)
EndFunction


Event OnUpdate()
  actor akTarget = GetTargetActor()
  string targetName = Main.GetActorName(akTarget)
  Main.Debug("SAPIENCE OnUpdate (" + targetName +")")
  if(!aiff || !akTarget.Is3DLoaded())
    Main.Debug("SAPIENCE OnUpdate( " + targetName + ") Stopping OnUpdate for actor - actor is not loaded.")
    RemoveAI(akTarget)
    UnregisterForUpdate()
    return
  endif
  EnableAI(akTarget)
  RegisterForSingleUpdate(60) ; Check every 60 seconds if the NPC is still loaded, and clean them up if they're not.
EndEvent
