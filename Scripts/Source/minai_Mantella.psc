scriptname minai_Mantella extends Quest

MantellaConversation mantella
FormList MantellaConversationParticipantsFormList

GlobalVariable minai_GlobalInjectToggle

minai_MainQuestController main
; Modules
minai_Sex sex
minai_Survival survival
minai_Arousal arousal
minai_DeviousStuff devious
minai_DirtAndBlood dirtAndBlood

actor playerRef

Function Maintenance(minai_MainQuestController _main)
  main = _main
  Main.Info("- Initializing for Mantella.")
  main = (Self as Quest)as minai_MainQuestController
  
  RegisterForModEvent("Mantella_ActorSpeakEvent", "OnActorSpeak")
  RegisterForModEvent("Mantella_PlayerInputEvent", "OnPlayerInput")

  mantella = Game.GetFormFromFile(0x03D41A, "Mantella.esp") as MantellaConversation
  MantellaConversationParticipantsFormList = Game.GetFormFromFile(0x000E4537, "Mantella.esp") as FormList
  if !mantella || !MantellaConversationParticipantsFormList
    Debug.Messagebox("AI Fatal Error: Could not get handle to Mantella.")
    Main.Error("Could not get handle to Mantella")
  EndIf

  minai_GlobalInjectToggle = Game.GetFormFromFile(0x0905, "MinAI.esp") as GlobalVariable
  if !minai_GlobalInjectToggle
    Main.Error("Could not find inject toggle")
  EndIf

  sex = (Self as Quest)as minai_Sex
  survival = (Self as Quest)as minai_Survival
  arousal = (Self as Quest)as minai_Arousal
  devious = (Self as Quest)as minai_DeviousStuff
  dirtAndBlood = (Self as Quest)as minai_DirtAndBlood

  
  playerRef = Game.GetPlayer()
EndFunction








Event OnActorSpeak(Form actorToSpeakTo, Form actorSpeaking,string sayLine)
  ActionResponse(actorToSpeakTo,actorSpeaking,sayLine)
EndEvent

Event OnPlayerInput(string playerInput)
  Main.Info("OnPlayerInput(): " + playerInput)
  actor player = Game.GetPlayer()
  ;; Fix injected prompts being missing from first sentence of dialogue
  actor[] actorsFromFormList = GetActorsFromFormList()
  if MantellaConversationParticipantsFormList.GetSize() == 2
    actor otherActor = None
    int i = 0
    while i < actorsFromFormList.Length && otherActor == None
      if actorsFromFormList[i] != player
        otherActor = actorsFromFormList[i]
      EndIf
      i += 1
    EndWhile
    Main.Info("2 players in conversation, setting initial context if not set")
    UpdateEvents(player, otherActor, actorsFromFormList)
  EndIf
  
  
EndEvent


Function RegisterAction(String eventLine)
  if minai_GlobalInjectToggle.GetValue() != 1.0
    Main.Warn("RegisterAction() - Not doing anything, this is disabled.")
    return
  EndIf
  Main.Info("RegisterAction(): " + eventLine)
  mantella.AddInGameEvent(eventLine)
EndFunction

Function RegisterEvent(String eventLine)
  Main.Info("RegisterEvent(): " + eventLine)
  mantella.AddInGameEvent(eventLine)
EndFunction




Function SendActorSpeakEvent(Actor actorToSpeakTo, Actor actorSpeaking)
    int handle = ModEvent.Create("MinAI_ActorSpeakEvent")
    if (handle)
	ModEvent.PushForm(handle,actorToSpeakTo as Form )
        ModEvent.PushForm(handle, actorSpeaking as Form)
        ModEvent.Send(handle)
    EndIf
EndFunction


Function UpdateEvents(Actor actorToSpeakTo, Actor actorSpeaking, actor[] actorsFromFormList)
  bool isEmpty = mantella.IsActionsEmpty()
  SendActorSpeakEvent(actorToSpeakTo, actorSpeaking)
  if !isEmpty
    Main.Info("Actions are not empty")
    return
  EndIf
  Actor player = game.GetPlayer()
  bool bPlayerInScene = False
  
  if actorSpeaking == playerRef || actorToSpeakTo == playerRef
    Main.Info("Player in Scene")
    bPlayerInScene = True
  EndIf

  string targetName = main.getActorName(actorToSpeakTo)
  string speakerName = main.getActorName(actorSpeaking)
  string playerName = main.getActorName(player)
  
  devious.UpdateEvents(actorToSpeakTo, actorSpeaking, actorsFromFormList, bPlayerInScene,  targetName,  speakerName,  playerName)
  ; sex.UpdateEvents(actorToSpeakTo, actorSpeaking, actorsFromFormList, bPlayerInScene,  targetName,  speakerName,  playerName)
  arousal.UpdateEvents(actorToSpeakTo, actorSpeaking, actorsFromFormList, bPlayerInScene,  targetName,  speakerName,  playerName)
  survival.UpdateEvents(actorToSpeakTo, actorSpeaking, actorsFromFormList, bPlayerInScene,  targetName,  speakerName,  playerName)
  dirtAndBlood.UpdateEventsForMantella(actorToSpeakTo, actorSpeaking, actorsFromFormList)
  RegisterAction(BuildReminderStr(actorToSpeakTo))
EndFunction

bool Function FactionInScene(Faction theFaction, actor[] actorsInScene)
  int i = 0
  while i < actorsInScene.Length
    if actorsInScene[i] != None && actorsInScene[i].IsInFaction(theFaction)
      return True
    EndIf
    i += 1
  EndWhile
  return False
EndFunction


String Function BuildReminderStr(Actor akActor)
  string reminderStart = "Respond only with spoken dialog and defined -keywords- for your actions. Avoid narration and internal dialog. There are action -keywords- for "
  string reminderStr = ""
  if survival.useVanilla()
    reminderStr += "trading with, "
  EndIf

  if devious.UseSTA()
    ReminderStr += "spanking, "
  endif
  if devious.useSLHH()
    ReminderStr += "molesting, "
  endif
  if devious.useSLApp()
    ReminderStr += "kissing, hugging, "
  endif
  if survival.useSunhelm()
    ReminderStr += "feeding, serving a meal to, renting a room to, "
  endif
  if devious.UseDF()
    ReminderStr += "giving drugs or skooma to, "
  EndIf
  if devious.UseDD()
    ReminderStr +="vibrating, giving an orgasm to, teasing, "
  EndIf
  if sex.UseSex()
    if reminderStr != ""
      reminderStr += "or "
    EndIf
    reminderStr += "having sex with "
  EndIf
  reminderStr = reminderStart + reminderStr + main.GetActorName(akActor) + "."
  return reminderStr ;+ "\n!" + reminderStr + "!"
EndFunction








Function ActionResponse(Form actorToSpeakTo,Form actorSpeaking, string sayLine)
  ; akTarget is the person being talked to.
  actor akTarget = actorToSpeakTo as Actor
  ; actorToSpeakTo is the person initiating the conversation. Usually the player, unless radiant
  actor akSpeaker = actorSpeaking as Actor
  if akTarget.IsChild() || akSpeaker.IsChild()
    Main.Warn("Not processing response - one of the actors is a child.")
    return
  EndIf
  bool bPlayerInScene = False
  if actorSpeaking == playerRef || actorToSpeakTo == playerRef
    Main.Info("Player in Scene")
    bPlayerInScene = True
  EndIf
  Actor[] actorsFromFormList = GetActorsFromFormList()
  Main.Info("ActionResponse(" + Main.GetActorName(akSpeaker) + ", " + Main.GetActorName(akTarget) + ", playerInScene="+bPlayerInScene+"): " + sayLine) 
  arousal.ActionResponse(akTarget, akSpeaker, sayLine, actorsFromFormList)
  sex.ActionResponse(akTarget, akSpeaker, sayLine, actorsFromFormList, bPlayerInScene)
  survival.ActionResponse(akTarget, akSpeaker, sayLine, actorsFromFormList)
  devious.ActionResponse(akTarget, akSpeaker, sayLine, actorsFromFormList)
  UpdateEvents(actorToSpeakTo as Actor, actorSpeaking as Actor, actorsFromFormList)
EndFunction 







Actor[] Function GetActorsFromFormList()
    Actor[] actorsFromFormList = new Actor[10]
	int numActors = 0
    int i = 0
    while (i < MantellaConversationParticipantsFormList.GetSize() && numActors < 10)
        Form currentForm = MantellaConversationParticipantsFormList.GetAt(i)
        Actor currentActor = currentForm as Actor
        if (currentActor)
            actorsFromFormList[numActors] = currentActor
            numActors += 1
		else
            Main.Warn("Error: MantellaConversationParticipantsFormList[" + i + "] is not an Actor")
		EndIf
        i += 1
    endWhile
    return actorsFromFormList
EndFunction








