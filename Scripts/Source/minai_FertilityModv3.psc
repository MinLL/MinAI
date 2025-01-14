scriptname minai_FertilityModv3 extends Quest

; these spells are attached to women depending on their circumstances
MagicEffect FertilityModV3_PMS_MagicEffect
MagicEffect FertilityModV3_Fertile_MagicEffect
MagicEffect FertilityModV3_FirstTrimester_MagicEffect
MagicEffect FertilityModV3_SecondTrimester_MagicEffect
MagicEffect FertilityModV3_ThirdTrimester_MagicEffect
MagicEffect FertilityModV3_Menstruation_MagicEffect
; from update, labor
MagicEffect FertilityModV3_Labor_MagicEffect
; from update, morning sickness + without knowledge of pregnancy
MagicEffect FertilityModV3_Trimester1Morning_MagicEffect

bool bFertilityModV3_PMS = False
bool bFertilityModV3_Fertile = False
bool bFertilityModV3_FirstTrimester = False
bool bFertilityModV3_SecondTrimester = False
bool bFertilityModV3_ThirdTrimester = False
bool bFertilityModV3_Menstruation = False
bool bFertilityModV3_Labor = False
bool bFertilityModV3_Trimester1Morning = False


bool bHasFertilityModV3 = False
bool bHasFertilityMode3FixesAndUpdates = False

minai_MainQuestController main
minai_AIFF aiff

; right now this only works on PC, I'll have to understand better FertilityModeV3 to extend the LLM awareness to NPCs, who aren't tracked with spells. 
; it will probably have to be a mod that stands outside of MinAI but is a Patch for it, to support NPC pregnancy tracking because it will likely need to import 
; fertility mode v3's Storage data object

Function Maintenance(minai_MainQuestController _main)
    main = _main
    main.Info("Fertility ModeV3 Plugin Loading.")
    aiff = (Self as Quest) as minai_AIFF
    if Game.GetModByName("Fertility Mode 3 Fixes and Updates.esp") != 255
        FertilityModV3_PMS_MagicEffect = Game.GetFormFromFile(0x017717, "Fertility Mode 3 Fixes and Updates.esp") as MagicEffect
        FertilityModV3_Fertile_MagicEffect = Game.GetFormFromFile(0x0181E1, "Fertility Mode 3 Fixes and Updates.esp") as MagicEffect
        FertilityModV3_FirstTrimester_MagicEffect = Game.GetFormFromFile(0x000824, "Fertility Mode 3 Fixes and Updates.esp") as MagicEffect
        FertilityModV3_SecondTrimester_MagicEffect = Game.GetFormFromFile(0x000826, "Fertility Mode 3 Fixes and Updates.esp") as MagicEffect
        FertilityModV3_ThirdTrimester_MagicEffect = Game.GetFormFromFile(0x000827, "Fertility Mode 3 Fixes and Updates.esp") as MagicEffect
        FertilityModV3_Labor_MagicEffect = Game.GetFormFromFile(0x0155BD, "Fertility Mode 3 Fixes and Updates.esp") as MagicEffect
        FertilityModV3_Trimester1Morning_MagicEffect = Game.GetFormFromFile(0x000825, "Fertility Mode 3 Fixes and Updates.esp") as MagicEffect
        bHasFertilityMode3FixesAndUpdates = True
        bHasFertilityModV3 = True
    ElseIf Game.GetModByName("Fertility Mode.esm") != 255
        FertilityModV3_PMS_MagicEffect = Game.GetFormFromFile(0x017717, "Fertility Mode.esm") as MagicEffect
        FertilityModV3_Fertile_MagicEffect = Game.GetFormFromFile(0x0181E1, "Fertility Mode.esm") as MagicEffect
        FertilityModV3_FirstTrimester_MagicEffect = Game.GetFormFromFile(0x01B813, "Fertility Mode.esm") as MagicEffect
        FertilityModV3_SecondTrimester_MagicEffect = Game.GetFormFromFile(0x01B814, "Fertility Mode.esm") as MagicEffect
        FertilityModV3_ThirdTrimester_MagicEffect = Game.GetFormFromFile(0x01B815, "Fertility Mode.esm") as MagicEffect
        FertilityModV3_Labor_MagicEffect = Game.GetFormFromFile(0x0155BD, "Fertility Mode.esm") as MagicEffect
        bHasFertilityModV3 = True
    EndIf
    aiff.SetModAvailable("FertilityModV3", bHasFertilityModV3)
EndFunction

Function SetContext(actor currentActor)
    String actorName = main.GetActorName(currentActor)
    String firstperson_msg = ""
    String secondperson_msg = ""
    String thirdperson_msg = ""
    
    bFertilityModV3_PMS = currentActor.HasSpell(FertilityModV3_PMS_MagicEffect)  
    bFertilityModV3_Fertile = currentActor.HasSpell(FertilityModV3_Fertile_MagicEffect) 
    bFertilityModV3_FirstTrimester = currentActor.HasSpell(FertilityModV3_FirstTrimester_MagicEffect) 
    bFertilityModV3_SecondTrimester = currentActor.HasSpell(FertilityModV3_SecondTrimester_MagicEffect) 
    bFertilityModV3_ThirdTrimester = currentActor.HasSpell(FertilityModV3_ThirdTrimester_MagicEffect) 
    if bHasFertilityMode3FixesAndUpdates
        bFertilityModV3_Labor = currentActor.HasSpell(FertilityModV3_Labor_MagicEffect)
        bFertilityModV3_Trimester1Morning = currentActor.HasSpell(FertilityModV3_Trimester1Morning_MagicEffect) 
    EndIf        
    
    if bFertilityModV3_PMS
        firstperson_msg = actorName + " is on her period. "
        secondperson_msg = actorName + " is distracted and irritable."
        thirdperson_msg = actorName + " is on her period."
    ElseIf bFertilityModV3_Fertile
        firstperson_msg = actorName + " is more aroused than usual. Men look a little better than usual. " + actorName + " suspects she is ovulating. "
        secondperson_msg = actorName + " has an attractive glow."
        thirdperson_msg = actorName + " is ovulating. She might not know so no spoilers! Coy flirty hints and suggestions for love are welcome."
    ElseIf bFertilityModV3_Labor
        firstperson_msg = actorName + " is in labor, giving birth."
        secondperson_msg = actorName + " is in labor, giving birth."
        thirdperson_msg = actorName + " is in labor, giving birth."
    Elseif bFertilityModV3_FirstTrimester || bFertilityModV3_Trimester1Morning
        ; since we don't have much granularity
        ; the NPC's LLM aspect will know she is pregnant in 2nd trimester
        firstperson_msg = actorName + " has been feeling a sick in the morning, throwing up shortly after waking. "
        secondperson_msg = actorName + " was frazzled this morning."
        thirdperson_msg = actorName + " is pregnant in her first trimester. They might not know so no spoilers! Coy hints are welcome. "
    ElseIf bFertilityModV3_SecondTrimester
        firstperson_msg = actorName + " knows they are pregnant and in their second trimester. They finds chairs uncomfortable. "
        secondperson_msg = actorName + " has a wonderful glow about themselves, their breasts are more magnificent than they were before."
        thirdperson_msg = actorName + " knows they are pregnant and in their second trimester. They finds chairs uncomfortable. "
    ElseIf bFertilityModV3_ThirdTrimester
        firstperson_msg = actorName + " is pregnant and in their third trimester. They are showing. They wants strange food. "
        secondperson_msg = actorName + " is pregnant and in their third trimester. They are showing. They wants strange food. "
         thirdperson_msg = actorName + " is pregnant and in their third trimester. They are showing. They wants strange food. "
    EndIf
    If(firstperson_msg!="a" && secondperson_msg != "b" && thirdperson_msg != "c")
        aiff.SetActorVariable(currentActor, "fertilityModV3PrivateStatus", firstperson_msg)
        aiff.SetActorVariable(currentActor, "fertilityModV3PublicStatus", secondperson_msg)
        aiff.SetActorVariable(currentActor, "fertilityModV3NarratorStatus", thirdperson_msg) 
    EndIf
EndFunction
  
