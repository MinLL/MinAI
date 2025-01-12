scriptname minai_FertilityModv3 extends Quest

; these spells are attached to women depending on their circumstances
Spell FertilityModV3_PMS
Spell FertilityModV3_Fertile
Spell FertilityModV3_FirstTrimester
Spell FertilityModV3_SecondTrimester
Spell FertilityModV3_ThirdTrimester
Spell FertilityModV3_Menstruation
; from update, labor
Spell FertilityModV3_Labor

bool bFertilityModV3_PMS = False
bool bFertilityModV3_Fertile = False
bool bFertilityModV3_FirstTrimester = False
bool bFertilityModV3_SecondTrimester = False
bool bFertilityModV3_ThirdTrimester = False
bool bFertilityModV3_Menstruation = False
bool bFertilityModV3_Labor = False


bool bHasFertilityModV3 = False
bool bHasFertilityMode3FixesAndUpdates = False

minai_MainQuestController main
minai_AIFF aiff



Function Maintenance(minai_MainQuestController _main)
    main = _main
    aiff = (Self as Quest) as minai_AIFF
    if Game.GetModByName("Fertility Mode 3 Fixes and Updates.esp") != 255
        FertilityModV3_PMS = Game.GetFormFromFile(0x00081B, "Fertility Mode 3 Fixes and Updates.esp") as Spell
        FertilityModV3_Fertile = Game.GetFormFromFile(0x00081B, "Fertility Mode 3 Fixes and Updates.esp") as Spell
        FertilityModV3_FirstTrimester = Game.GetFormFromFile(0x00081B, "Fertility Mode 3 Fixes and Updates.esp") as Spell
        FertilityModV3_SecondTrimester = Game.GetFormFromFile(0x00081B, "Fertility Mode 3 Fixes and Updates.esp") as Spell
        FertilityModV3_ThirdTrimester = Game.GetFormFromFile(0x00081B, "Fertility Mode 3 Fixes and Updates.esp") as Spell
        FertilityModV3_Labor = Game.GetFormFromFile(0x000CEC, "Fertility Mode 3 Fixes and Updates.esp") as Spell
        bHasFertilityMode3FixesAndUpdates = True
        bHasFertilityModV3 = True
    ElseIf Game.GetModByName("Fertility Mode.esm") != 255
        FertilityModV3_PMS = Game.GetFormFromFile(0x017718, "Fertility Mode.esm") as Spell
        FertilityModV3_Fertile = Game.GetFormFromFile(0x0181E2, "Fertility Mode.esm") as Spell
        FertilityModV3_FirstTrimester = Game.GetFormFromFile(0x01B816, "Fertility Mode.esm") as Spell
        FertilityModV3_SecondTrimester = Game.GetFormFromFile(0x01B818, "Fertility Mode.esm") as Spell
        FertilityModV3_ThirdTrimester = Game.GetFormFromFile(0x01B81A, "Fertility Mode.esm") as Spell
        bHasFertilityModV3 = True
    EndIf
    aiff.SetModAvailable("FertilityModV3", bHasFertilityModV3)
EndFunction


Function SetContext(actor akActor)
    SetValuesOfBools(akActor)
    string msg = GetStringForActor(akActor)
    string whoKnows = GetStringForEverybodyVSActor(akActor)
    aiff.SetActorVariable(akActor, "fertilityModV3Status", msg) 
    aiff.SetActorVariable(akActor, "fertilityModV3ContextAwareness", whoKnows) 
EndFunction
  
string Function GetStringForActor(actor akActor)
    string actorName = Main.GetActorName(akActor)
    string msg = ""
    if bFertilityModV3_PMS
        ; public vs private keywords
        msg = actorName + " is on her period."
    ElseIf bFertilityModV3_Fertile
        msg = actorName + " suspects she is ovulating."
    ElseIf bFertilityModV3_Labor
        msg = actorName + " is in labor, giving birth."
    Elseif bFertilityModV3_FirstTrimester
        ; since we don't have much granularity
        ; the NPC's LLM aspect will know she is pregnant in 2nd trimester
        if aiff ; the narrator could know
            msg = actorName + " may not know she's pregnant in her first trimester. Don't give spoilers, she might not know, but little hints would be fun."
        EndIf
        msg = actorName + "" 
    ElseIf bFertilityModV3_SecondTrimester
        msg = actorName + " is pregnant and in her second trimester."
    ElseIf bFertilityModV3_ThirdTrimester
        msg = actorName + " is pregnant and in her third trimester. She's showing."
    EndIf

    return msg
EndFunction



string Function GetStringForEverybodyVSActor(actor currentActor)
    String msg = ""
    String actorName = main.GetActorName(currentActor)
    if bFertilityModV3_PMS
        ; public vs private keywords
        msg = actorName
    ElseIf bFertilityModV3_Fertile
        msg = actorName
    ElseIf bFertilityModV3_Labor
        msg = "everybody"
    Elseif bFertilityModV3_FirstTrimester
        ; since we don't have much granularity
        ; the NPC's LLM aspect will know she is pregnant in 2nd trimester
        msg = "The Narrator" ;
    ElseIf bFertilityModV3_SecondTrimester
        msg = actorName
    ElseIf bFertilityModV3_ThirdTrimester
        msg = "everybody"
    EndIf

    return msg
EndFunction

Function SetValuesOfBools(actor currentActor)
    bFertilityModV3_PMS = currentActor.HasSpell(FertilityModV3_PMS)  
    bFertilityModV3_Fertile = currentActor.HasSpell(FertilityModV3_Fertile) 
    bFertilityModV3_FirstTrimester = currentActor.HasSpell(FertilityModV3_FirstTrimester) 
    bFertilityModV3_SecondTrimester = currentActor.HasSpell(FertilityModV3_SecondTrimester) 
    bFertilityModV3_ThirdTrimester = currentActor.HasSpell(FertilityModV3_ThirdTrimester) 
    if bHasFertilityMode3FixesAndUpdates
        bFertilityModV3_Labor = currentActor.HasSpell(FertilityModV3_Labor)
    EndIf        
EndFunction
      



