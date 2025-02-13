scriptname minai_FertilityMode extends Quest

; Properties and variables
minai_MainQuestController main
minai_AIFF aiff
actor playerRef
bool bHasFertilityMode = false

; Storage script from Fertility Mode
_JSW_BB_Storage Property FertilityStorage Auto
_JSW_BB_Utility Property FMUtility Auto

Function Maintenance(minai_MainQuestController _main)
    playerRef = Game.GetPlayer()
    main = _main
    aiff = (Self as Quest) as minai_AIFF

    main.Info("Initializing Fertility Mode Module")
    
    ; Check if Fertility Mode is installed (required)
    if Game.GetModByName("Fertility Mode.esm") != 255
        bHasFertilityMode = true
        Main.Info("Found Fertility Mode")
        
        ; Get the storage quest from the base mod
        Quest storageQuest = Game.GetFormFromFile(0x0D62, "Fertility Mode.esm") as Quest
        if storageQuest
            FertilityStorage = storageQuest as _JSW_BB_Storage
            FMUtility = storageQuest as _JSW_BB_Utility
            
            if !FertilityStorage || !FMUtility
                Main.Error("Could not get required Fertility Mode script references")
                bHasFertilityMode = false
            EndIf
        Else
            Main.Error("Could not load Fertility Mode storage quest")
            bHasFertilityMode = false
        EndIf
    EndIf

    aiff.SetModAvailable("FertilityMode", bHasFertilityMode)
EndFunction

Function SetContext(actor akTarget)
    if !aiff || !bHasFertilityMode || !FertilityStorage || !FMUtility
        return
    EndIf

    ; Only check female actors
    if akTarget.GetActorBase().GetSex() != 1
        return
    EndIf

    ; Get actor's index in fertility mode tracking
    int actorIndex = FertilityStorage.GetActorIndex(akTarget)
    if actorIndex == -1
        ; Actor not being tracked by Fertility Mode
        aiff.SetActorVariable(akTarget, "fertility_state", "normal")
        return
    EndIf

    ; Get the actor's state from FMMiscUtil
    ; States are:
    ; 0 : ovulation phase, before egg
    ; 1 : ovulation phase, with egg
    ; 2 : luteal - ovulation phase, after egg has died
    ; 3 : menstruation
    ; 4 : first trimester
    ; 5 : second trimester
    ; 6 : third trimester
    ; 7 : ovulation is blocked this cycle
    ; 8 : recovery from birth
    ; 20: full-term pregnancy
    int stateID = FMUtility.FMMiscUtil.GetActorStateID(actorIndex)

    if stateID == 6 || stateID == 20
        aiff.SetActorVariable(akTarget, "fertility_state", "third_trimester")
    elseif stateID == 5
        aiff.SetActorVariable(akTarget, "fertility_state", "second_trimester")
    elseif stateID == 4
        aiff.SetActorVariable(akTarget, "fertility_state", "first_trimester")
    elseif stateID == 1
        aiff.SetActorVariable(akTarget, "fertility_state", "ovulating")
    elseif stateID == 2
        aiff.SetActorVariable(akTarget, "fertility_state", "pms")
    elseif stateID == 3
        aiff.SetActorVariable(akTarget, "fertility_state", "menstruating")
    else
        aiff.SetActorVariable(akTarget, "fertility_state", "normal")
    endif
EndFunction 