scriptname minai_DirtAndBlood extends Quest

bool bHasDirtAndBlood = False

minai_MainQuestController main
minai_AIFF aiff

actor playerRef

Spell Dirty_SoapEffectSpell
Spell Dirty_Spell_Dirt1
Spell Dirty_Spell_Dirt2
Spell Dirty_Spell_Dirt3
Spell Dirty_Spell_Dirt4
Spell Dirty_Spell_Blood1
Spell Dirty_Spell_Blood2
Spell Dirty_Spell_Blood3
Spell Dirty_Spell_Blood4
Spell Dirty_Spell_Clean
; Spell Dirty_NPCIsWashingNow
; Spell Dirty_Spell_DirtForNPCs
; Spell Dirty_Spell_Swimming
; Spell Dirty_CleanYoSelf
; Spell Dirty_Spell_NegativeSideEffects
; Spell Dirty_Spell_PositiveSideEffects
; Spell Dirty_Spell_IsRaining
; Spell Dirty_CleanYoSelfNPC
; Spell Dirty_BloodySpellForNPCs

bool bDirty_SoapEffectSpell
bool bDirty_Spell_Dirt1
bool bDirty_Spell_Dirt2
bool bDirty_Spell_Dirt3
bool bDirty_Spell_Dirt4
bool bDirty_Spell_Blood1
bool bDirty_Spell_Blood2
bool bDirty_Spell_Blood3
bool bDirty_Spell_Blood4
bool bDirty_Spell_Clean
; bool bDirty_NPCIsWashingNow
; bool bDirty_Spell_DirtForNPCs
; bool bDirty_Spell_Swimming
; bool bDirty_CleanYoSelf
; bool bDirty_Spell_NegativeSideEffects
; bool bDirty_Spell_PositiveSideEffects
; bool bDirty_Spell_IsRaining
; bool bDirty_CleanYoSelfNPC
; bool bDirty_BloodySpellForNPCs

; set minai_loglevel to 5



function Maintenance(minai_MainQuestController _main)
  Main.Info("BBBB Initializing Dirt and Blood minAI Module.")
  playerRef = Game.GetPlayer()
  main = _main
  aiff = (Self as Quest) as minai_AIFF
  If Game.GetModByName("Dirt and Blood - Dynamic Visuals.esp") != 255
    Main.Info("Found Dirt and Blood - Dynamic Visuals")
    bHasDirtAndBlood = True
    Dirty_SoapEffectSpell = Game.GetFormFromFile(0x000800, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    Dirty_Spell_Dirt1 = Game.GetFormFromFile(0x000806, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    Dirty_Spell_Dirt2 = Game.GetFormFromFile(0x000807, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    Dirty_Spell_Dirt3 = Game.GetFormFromFile(0x000808, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    Dirty_Spell_Dirt4 = Game.GetFormFromFile(0x000838, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    Dirty_Spell_Blood1 = Game.GetFormFromFile(0x000809, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    Dirty_Spell_Blood2 = Game.GetFormFromFile(0x00080A, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    Dirty_Spell_Blood3 = Game.GetFormFromFile(0x00080B, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    Dirty_Spell_Blood4 = Game.GetFormFromFile(0x000839, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    Dirty_Spell_Clean = Game.GetFormFromFile(0x00080C, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_NPCIsWashingNow = Game.GetFormFromFile(0x00081C, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_DirtForNPCs = Game.GetFormFromFile(0x000822, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_Swimming = Game.GetFormFromFile(0x000825, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_CleanYoSelf = Game.GetFormFromFile(0x00082F, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_NegativeSideEffects = Game.GetFormFromFile(0x000844, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_PositiveSideEffects = Game.GetFormFromFile(0x000848, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_Spell_IsRaining = Game.GetFormFromFile(0x00085D, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_CleanYoSelfNPC = Game.GetFormFromFile(0x000860, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    ; Dirty_BloodySpellForNPCs = Game.GetFormFromFile(0x000DE6, "Dirt and Blood - Dynamic Visuals.esp") as Spell
    aiff.SetModAvailable("DirtAndBlood", bHasDirtAndBlood)
  EndIf
EndFunction

Function UpdateEventsForMantella(Actor actorToSpeakTo, Actor actorSpeaking, actor[] actorsFromFormList)
  WriteDirtinessString(actorToSpeakTo, actorSpeaking, actorsFromFormList)
EndFunction


Function WriteDirtinessString(actor akTarget, actor actorSpeaking,  actor[] actorsFromFormList)
  Main.Info("WriteDirtinessString of Dirt and Blood")
  if !aiff
    Main.Info("No AIFF in minai_Dirt And Blood")
    return
  EndIf
  if akTarget == playerRef
    Main.Info("Dirt and Blood Set Context is working on the player.")
  EndIf

	int numActors = actorsFromFormList.Length
	int i = 0
	While (i < numActors)
		Actor currentActor = actorsFromFormList[i]
		if (currentActor != None)
			String actorName = main.GetActorName(currentActor)
      bDirty_SoapEffectSpell = currentActor.HasSpell(Dirty_SoapEffectSpell)
      bDirty_Spell_Dirt1 = currentActor.HasSpell(Dirty_Spell_Dirt1)
      bDirty_Spell_Dirt2 = currentActor.HasSpell(Dirty_Spell_Dirt2)
      bDirty_Spell_Dirt3 = currentActor.HasSpell(Dirty_Spell_Dirt3)
      bDirty_Spell_Dirt4 = currentActor.HasSpell(Dirty_Spell_Dirt4)
      bDirty_Spell_Blood1 = currentActor.HasSpell(Dirty_Spell_Blood1)
      bDirty_Spell_Blood2 = currentActor.HasSpell(Dirty_Spell_Blood2)
      bDirty_Spell_Blood3 = currentActor.HasSpell(Dirty_Spell_Blood3)
      bDirty_Spell_Blood4 = currentActor.HasSpell(Dirty_Spell_Blood4)
      bDirty_Spell_Clean = currentActor.HasSpell(Dirty_Spell_Clean)
      If bDirty_Spell_Dirt1
        main.RegisterEvent(actorName + " is barely dirty and fits right in with the people of Skyrim. ", "info_context")
      EndIf
      If bDirty_Spell_Dirt2
        main.RegisterEvent(actorName + " is starting to look dirty and could use a bath. ", "info_context")
      EndIf
      If bDirty_Spell_Dirt3
        main.RegisterEvent(actorName + " is very dirty and in need of a bath. ", "info_context")
      EndIf
      If bDirty_Spell_Dirt4
        main.RegisterEvent(actorName + " is filthy, disgustingly dirty, so gross it is uncomfortable to be close to " + actorName + ". " + actorName + " smells. " + actorName + " is so dirty " + actorName + " may get sick. " + actorName + " has terrible hygiene. ", "info_context")
      EndIf
      If bDirty_Spell_Blood1
        main.RegisterEvent(actorName + " is barely shows blood blood from battle. ", "info_context")
      EndIf
      If bDirty_Spell_Blood2
        main.RegisterEvent( actorName + " has blood from battle smeared on themselves. ", "info_context")
      EndIf
      If bDirty_Spell_Blood3
        main.RegisterEvent(actorName + " is covered in blood from head to toe. ", "info_context")
      EndIf
      If bDirty_Spell_Blood4
        main.RegisterEvent(actorName + " is soaked in blood, and the ground below " + actorName + "seeps with blood dripping off them. ", "info_context")
      EndIf
      If bDirty_SoapEffectSpell
        main.RegisterEvent(actorName + " is bathing. " + actorName + " is soaping up and washing themselves. ", "info_context")
      EndIf
      If bDirty_Spell_Clean
        main.RegisterEvent(actorName + " is clean and well groomed. ", "info_context")
      EndIf
    Endif
		i += 1
	EndWhile
EndFunction


Function SetContextForAIFF(actor akTarget)
  Main.Info("SetContext of Dirt and Blood")
  if !aiff
    Main.Info("No AIFF in minai_Dirt And Blood")
    return
  EndIf
  if akTarget == playerRef
    Main.Info("Dirt and Blood Set Context is working on the player.")
    string tag_list = ""
    bDirty_SoapEffectSpell = akTarget.HasSpell(Dirty_SoapEffectSpell)
    bDirty_Spell_Dirt1 = akTarget.HasSpell(Dirty_Spell_Dirt1)
    bDirty_Spell_Dirt2 = akTarget.HasSpell(Dirty_Spell_Dirt2)
    bDirty_Spell_Dirt3 = akTarget.HasSpell(Dirty_Spell_Dirt3)
    bDirty_Spell_Dirt4 = akTarget.HasSpell(Dirty_Spell_Dirt4)
    bDirty_Spell_Blood1 = akTarget.HasSpell(Dirty_Spell_Blood1)
    bDirty_Spell_Blood2 = akTarget.HasSpell(Dirty_Spell_Blood2)
    bDirty_Spell_Blood3 = akTarget.HasSpell(Dirty_Spell_Blood3)
    bDirty_Spell_Blood4 = akTarget.HasSpell(Dirty_Spell_Blood4)
    bDirty_Spell_Clean = akTarget.HasSpell(Dirty_Spell_Clean)
    If bDirty_Spell_Dirt1
      tag_list += "Dirt1,"
    EndIf
    If bDirty_Spell_Dirt2
      tag_list += "Dirt2,"
    EndIf
    If bDirty_Spell_Dirt3
      tag_list += "Dirt3,"
    EndIf
    If bDirty_Spell_Dirt4
      tag_list += "Dirt4,"
    EndIf
    If bDirty_Spell_Blood1
      tag_list += "Blood1,"
    EndIf
    If bDirty_Spell_Blood2
      tag_list += "Blood2,"
    EndIf
    If bDirty_Spell_Blood3
      tag_list += "Blood3,"
    EndIf
    If bDirty_Spell_Blood4
      tag_list += "Blood4,"
    EndIf
    If bDirty_SoapEffectSpell
      tag_list += "SoapEffectSpell,"
    EndIf
    If bDirty_Spell_Clean
      tag_list += "Clean,"
    EndIf
    aiff.SetActorVariable(akTarget, "dirtAndBloodTags", tag_list)
  EndIf

EndFunction
