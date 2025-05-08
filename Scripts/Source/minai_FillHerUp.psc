scriptname minai_FillHerUp extends Quest

bool bHasFillHerUp = false

Faction property SR_InflateFaction auto  ; Inflated actor faction
Faction property SR_InflateOralFaction auto  ; Inflated Oral actor faction 
Faction property sr_Impregnated auto  ; Impregnated
Faction property sr_ImpregnatedAnal auto  ; ImpregnatedAnal

minai_MainQuestController main
minai_AIFF aiff
Actor playerRef

; StorageUtil keys from sr_inflateQuest
String Property CUM_VAGINAL = "sr.inflater.cum.vaginal" autoreadonly hidden
String Property CUM_ANAL = "sr.inflater.cum.anal" autoreadonly hidden
String Property CUM_ORAL = "sr.inflater.cum.oral" autoreadonly hidden

; Animation event names
String[] Property SPERM_OUT_START auto
String[] Property SPERM_OUT_LOOP auto
String[] Property SPERM_OUT_END auto
String[] Property SPERM_ANAL_START auto
String[] Property SPERM_ANAL_LOOP auto
String[] Property SPERM_ANAL_END auto
String[] Property SPERM_ORAL_START auto
String[] Property SPERM_ORAL_LOOP auto
String[] Property SPERM_ORAL_END auto
String[] Property SPERM_EXPEL auto

; Pool masks
int property VAGINAL		= 0x01 autoreadonly hidden
int property ANAL		= 0x02 autoreadonly hidden
int property ORAL		= 0x04 autoreadonly hidden

Function Maintenance(minai_MainQuestController _main)
    main = _main
    aiff = (Self as Quest) as minai_AIFF
    playerRef = Game.GetPlayer()

    main.Info("Initializing Fill Her Up Module")
    
    ; Initialize animation event arrays
    SPERM_OUT_START = new String[4]
    SPERM_OUT_START[0] = "BaboSpermOut01Start"
    SPERM_OUT_START[1] = "BaboSpermOut02Start"
    SPERM_OUT_START[2] = "BaboSpermOut03Start"
    SPERM_OUT_START[3] = "BaboSpermOut04Start"

    SPERM_OUT_LOOP = new String[4]
    SPERM_OUT_LOOP[0] = "BaboSpermOut01Loop"
    SPERM_OUT_LOOP[1] = "BaboSpermOut02Loop"
    SPERM_OUT_LOOP[2] = "BaboSpermOut03Loop"
    SPERM_OUT_LOOP[3] = "BaboSpermOut04Loop"

    SPERM_OUT_END = new String[4]
    SPERM_OUT_END[0] = "BaboSpermOut01End"
    SPERM_OUT_END[1] = "BaboSpermOut02End"
    SPERM_OUT_END[2] = "BaboSpermOut03End"
    SPERM_OUT_END[3] = "BaboSpermOut04End"

    SPERM_ANAL_START = new String[4]
    SPERM_ANAL_START[0] = "BaboSpermAnalOut01Start"
    SPERM_ANAL_START[1] = "BaboSpermAnalOut02Start"
    SPERM_ANAL_START[2] = "BaboSpermAnalOut03Start"
    SPERM_ANAL_START[3] = "BaboSpermAnalOut04Start"

    SPERM_ANAL_LOOP = new String[4]
    SPERM_ANAL_LOOP[0] = "BaboSpermAnalOut01Loop"
    SPERM_ANAL_LOOP[1] = "BaboSpermAnalOut02Loop"
    SPERM_ANAL_LOOP[2] = "BaboSpermAnalOut03Loop"
    SPERM_ANAL_LOOP[3] = "BaboSpermAnalOut04Loop"

    SPERM_ANAL_END = new String[4]
    SPERM_ANAL_END[0] = "BaboSpermAnalOut01End"
    SPERM_ANAL_END[1] = "BaboSpermAnalOut02End"
    SPERM_ANAL_END[2] = "BaboSpermAnalOut03End"
    SPERM_ANAL_END[3] = "BaboSpermAnalOut04End"

    SPERM_ORAL_START = new String[1]
    SPERM_ORAL_START[0] = "BaboSpermOral01Start"

    SPERM_ORAL_LOOP = new String[1]
    SPERM_ORAL_LOOP[0] = "BaboSpermOral01Loop"

    SPERM_ORAL_END = new String[1]
    SPERM_ORAL_END[0] = "BaboSpermOral01End"

    SPERM_EXPEL = new String[5]
    SPERM_EXPEL[0] = "BaboSpermExpel"
    SPERM_EXPEL[1] = "BaboSpermExpelPanting"
    SPERM_EXPEL[2] = "BaboSpermExpelRefuse"
    SPERM_EXPEL[3] = "BaboSpermAnusExpelFail"
    SPERM_EXPEL[4] = "BaboStomachRubbing"

    ; Check if mod is installed and load references
    if Game.GetModByName("sr_FillHerUp.esp") != 255
        bHasFillHerUp = true
        Main.Info("Found Fill Her Up")
        
        ; Load faction references
        Main.Debug("Loading Fill Her Up faction references...")
        SR_InflateFaction = Game.GetFormFromFile(0x0A991, "sr_FillHerUp.esp") as Faction
        Main.Debug("SR_InflateFaction loaded: " + (SR_InflateFaction != None))
        
        SR_InflateOralFaction = Game.GetFormFromFile(0x14204B, "sr_FillHerUp.esp") as Faction
        Main.Debug("SR_InflateOralFaction loaded: " + (SR_InflateOralFaction != None))
        
        sr_Impregnated = Game.GetFormFromFile(0x19306F, "sr_FillHerUp.esp") as Faction
        Main.Debug("sr_Impregnated loaded: " + (sr_Impregnated != None))
        
        sr_ImpregnatedAnal = Game.GetFormFromFile(0x193070, "sr_FillHerUp.esp") as Faction
        Main.Debug("sr_ImpregnatedAnal loaded: " + (sr_ImpregnatedAnal != None))
        
        if !SR_InflateFaction || !SR_InflateOralFaction || !sr_Impregnated || !sr_ImpregnatedAnal
            Main.Error("Could not load all Fill Her Up references")
            bHasFillHerUp = false
        EndIf

        if bHasFillHerUp
            Main.Debug("Registering for Fill Her Up events...")
            ; Register for all animation events
            RegisterForAnimationEvents(playerRef)
            
            ; Register for Fill Her Up mod events
            RegisterForModEvent("SR_AbsorbEvent", "SRAbsorbEvent")
            RegisterForModEvent("SR_InflateEvent", "SRInflateEvent") 
            RegisterForModEvent("SR_InflateInjectorEvent", "SRInflateEventWithInjector")
            Main.Debug("Fill Her Up events registered")
        EndIf
    EndIf

    aiff.SetModAvailable("FillHerUp", bHasFillHerUp)
EndFunction


Event OnAnimationEvent(ObjectReference akSource, string asEventName)
    Main.Info("FillHerUp: OnAnimationEvent(" + akSource.GetBaseObject().GetName() + "): " + asEventName)
    if !bHasFillHerUp
        return
    EndIf
    
    Actor target = akSource as Actor
    if !target
        return
    EndIf
    
    string eventType = ""
    
    ; Determine event type based on animation name
    if SPERM_OUT_START.Find(asEventName) >= 0
        eventType = "spermout_start"
    elseif SPERM_OUT_LOOP.Find(asEventName) >= 0
        eventType = "spermout_loop"
    elseif SPERM_OUT_END.Find(asEventName) >= 0
        eventType = "spermout_end"
    elseif SPERM_ANAL_START.Find(asEventName) >= 0
        eventType = "spermanal_start"
    elseif SPERM_ANAL_LOOP.Find(asEventName) >= 0
        eventType = "spermanal_loop"
    elseif SPERM_ANAL_END.Find(asEventName) >= 0
        eventType = "spermanal_end"
    elseif SPERM_ORAL_START.Find(asEventName) >= 0
        eventType = "spermoral_start"
    elseif SPERM_ORAL_LOOP.Find(asEventName) >= 0
        eventType = "spermoral_loop"
    elseif SPERM_ORAL_END.Find(asEventName) >= 0
        eventType = "spermoral_end"
    elseif asEventName == "BaboSpermExpel"
        eventType = "sperm_expel"
    elseif asEventName == "BaboSpermExpelPanting"
        eventType = "sperm_expel_panting"
    elseif asEventName == "BaboSpermExpelRefuse"
        eventType = "sperm_expel_refuse"
    elseif asEventName == "BaboSpermAnusExpelFail"
        eventType = "sperm_expel_fail"
    elseif asEventName == "BaboStomachRubbing"
        eventType = "stomach_rubbing"
    endif
    
    if eventType != ""
        string eventLine = main.GetActorName(target) + " is performing animation " + asEventName
        main.RequestLLMResponseFromActor(eventLine, "minai_fillherup_" + eventType, "everyone", "player")
    EndIf
EndEvent

Function SetContext(actor akTarget)
    Main.Debug("SetContext FillHerUp(" + main.GetActorName(akTarget) + ")")
    if !bHasFillHerUp || !aiff
        return
    EndIf

    ; Get and set faction ranks
    int inflateRank = akTarget.GetFactionRank(SR_InflateFaction)
    int inflateOralRank = akTarget.GetFactionRank(SR_InflateOralFaction)
    int impregnatedRank = akTarget.GetFactionRank(sr_Impregnated)
    int impregnatedAnalRank = akTarget.GetFactionRank(sr_ImpregnatedAnal)

    ; Get cum amounts from StorageUtil
    float cumVaginal = StorageUtil.GetFloatValue(akTarget, CUM_VAGINAL)
    float cumAnal = StorageUtil.GetFloatValue(akTarget, CUM_ANAL)
    float cumOral = StorageUtil.GetFloatValue(akTarget, CUM_ORAL)

    ; Store values in AIFF
    aiff.SetActorVariable(akTarget, "inflateRank", inflateRank)
    aiff.SetActorVariable(akTarget, "inflateOralRank", inflateOralRank)
    aiff.SetActorVariable(akTarget, "impregnatedRank", impregnatedRank)
    aiff.SetActorVariable(akTarget, "impregnatedAnalRank", impregnatedAnalRank)
    aiff.SetActorVariable(akTarget, "cumVaginal", cumVaginal)
    aiff.SetActorVariable(akTarget, "cumAnal", cumAnal)
    aiff.SetActorVariable(akTarget, "cumOral", cumOral)
EndFunction

Function RegisterForAnimationEvents(Actor akActor)
    Main.Debug("FillHerUp: RegisterForAnimationEvents(" + main.GetActorName(akActor) + ")")
    if !akActor
        return
    endif
    
    ; Register for all animation types
    int i = 0
    
    ; Vaginal animations
    while i < SPERM_OUT_START.Length
        RegisterForAnimationEvent(akActor, SPERM_OUT_START[i])
        RegisterForAnimationEvent(akActor, SPERM_OUT_LOOP[i])
        RegisterForAnimationEvent(akActor, SPERM_OUT_END[i])
        i += 1
    endwhile

    ; Anal animations 
    i = 0
    while i < SPERM_ANAL_START.Length
        RegisterForAnimationEvent(akActor, SPERM_ANAL_START[i])
        RegisterForAnimationEvent(akActor, SPERM_ANAL_LOOP[i])
        RegisterForAnimationEvent(akActor, SPERM_ANAL_END[i])
        i += 1
    endwhile

    ; Oral animations
    i = 0
    while i < SPERM_ORAL_START.Length
        RegisterForAnimationEvent(akActor, SPERM_ORAL_START[i])
        RegisterForAnimationEvent(akActor, SPERM_ORAL_LOOP[i])
        RegisterForAnimationEvent(akActor, SPERM_ORAL_END[i])
        i += 1
    endwhile

    ; Special animations
    i = 0
    while i < SPERM_EXPEL.Length
        RegisterForAnimationEvent(akActor, SPERM_EXPEL[i])
        i += 1
    endwhile
EndFunction

Function UnregisterForAnimationEvents(Actor akActor) 
    Main.Debug("FillHerUp: UnregisterForAnimationEvents(" + main.GetActorName(akActor) + ")")
    if !akActor
        return
    endif

    int i = 0
    
    ; Unregister all animation types
    while i < SPERM_OUT_START.Length
        UnregisterForAnimationEvent(akActor, SPERM_OUT_START[i])
        UnregisterForAnimationEvent(akActor, SPERM_OUT_LOOP[i])
        UnregisterForAnimationEvent(akActor, SPERM_OUT_END[i])
        i += 1
    endwhile

    i = 0
    while i < SPERM_ANAL_START.Length
        UnregisterForAnimationEvent(akActor, SPERM_ANAL_START[i])
        UnregisterForAnimationEvent(akActor, SPERM_ANAL_LOOP[i])
        UnregisterForAnimationEvent(akActor, SPERM_ANAL_END[i])
        i += 1
    endwhile

    i = 0
    while i < SPERM_ORAL_START.Length
        UnregisterForAnimationEvent(akActor, SPERM_ORAL_START[i])
        UnregisterForAnimationEvent(akActor, SPERM_ORAL_LOOP[i])
        UnregisterForAnimationEvent(akActor, SPERM_ORAL_END[i])
        i += 1
    endwhile

    i = 0
    while i < SPERM_EXPEL.Length
        UnregisterForAnimationEvent(akActor, SPERM_EXPEL[i])
        i += 1
    endwhile
EndFunction

; Handler for absorption events
Event SRAbsorbEvent(Form akSpeakerform, int poolmask, float amount, int time, string callback)
    Main.Debug("FillHerUp: SRAbsorbEvent(" + akSpeakerform + ", " + poolmask + ", " + amount + ", " + time + ", " + callback + ")")
    if !bHasFillHerUp
        return
    EndIf

    Actor target = akSpeakerform as Actor
    if !target
        return
    EndIf

    string eventType = ""
    string bodyPart = ""
    if Math.LogicalAnd(poolmask, VAGINAL)
        eventType = "absorb_vaginal"
        bodyPart = "womb"
    elseif Math.LogicalAnd(poolmask, ANAL)
        eventType = "absorb_anal"
        bodyPart = "bowels"
    elseif Math.LogicalAnd(poolmask, ORAL)
        eventType = "absorb_oral"
        bodyPart = "stomach"
    endif

    if eventType != ""
        string eventLine = main.GetActorName(target) + "'s body absorbed " + amount + " units of cum from their " + bodyPart + " over " + time + " seconds"
        main.RegisterEvent(eventLine, "minai_fillherup_" + eventType)
    EndIf
EndEvent

; Handler for inflation events 
Event SRInflateEvent(Form akSpeakerform, Bool Inflation, int poolmask, float amount, int time, string callback)
    Main.Debug("FillHerUp: SRInflateEvent(" + akSpeakerform + ", " + Inflation + ", " + poolmask + ", " + amount + ", " + time + ", " + callback + ")")
    if !bHasFillHerUp
        return
    EndIf

    Actor target = akSpeakerform as Actor
    if !target
        return
    EndIf

    string eventType = ""
    string bodyPart = ""
    string inflateAction = ""
    if Inflation
        inflateAction = "inflated with"
    else
        inflateAction = "deflated"
    endif
    
    if Math.LogicalAnd(poolmask, VAGINAL)
        if Inflation
            eventType = "inflate_vaginal"
        else
            eventType = "deflate_vaginal"
        endif
        bodyPart = "womb"
    elseif Math.LogicalAnd(poolmask, ANAL)
        if Inflation
            eventType = "inflate_anal"
        else
            eventType = "deflate_anal"
        endif
        bodyPart = "bowels"
    elseif Math.LogicalAnd(poolmask, ORAL)
        if Inflation
            eventType = "inflate_oral"
        else
            eventType = "deflate_oral"
        endif
        bodyPart = "stomach"
    endif

    if eventType != ""
        string eventLine = main.GetActorName(target) + "'s " + bodyPart + " was " + inflateAction + " " + amount + " units of cum over " + time + " seconds"
        if Inflation
            main.RequestLLMResponseFromActor(eventLine, "minai_fillherup_" + eventType, "everyone", "player")
        else
            main.RegisterEvent(eventLine, "minai_fillherup_" + eventType)
        EndIf
    EndIf
EndEvent

; Handler for inflation events with injector
Event SRInflateEventWithInjector(Form akSpeakerform, Form akInjectorform, Bool Inflation, int poolmask, float amount, int time, string callback)
    Main.Debug("FillHerUp: SRInflateEventWithInjector(" + akSpeakerform + ", " + akInjectorform + ", " + Inflation + ", " + poolmask + ", " + amount + ", " + time + ", " + callback + ")")
    if !bHasFillHerUp
        return
    EndIf

    Actor target = akSpeakerform as Actor
    Actor injector = akInjectorform as Actor
    if !target || !injector
        return
    EndIf

    string eventType = ""
    string bodyPart = ""
    string inflateAction = ""
    if Inflation
        inflateAction = "inflated with"
    else
        inflateAction = "deflated"
    endif
    
    if Math.LogicalAnd(poolmask, VAGINAL)
        if Inflation
            eventType = "inflate_vaginal"
        else
            eventType = "deflate_vaginal"
        endif
        bodyPart = "womb"
    elseif Math.LogicalAnd(poolmask, ANAL)
        if Inflation
            eventType = "inflate_anal"
        else
            eventType = "deflate_anal"
        endif
        bodyPart = "bowels"
    elseif Math.LogicalAnd(poolmask, ORAL)
        if Inflation
            eventType = "inflate_oral"
        else
            eventType = "deflate_oral"
        endif
        bodyPart = "stomach"
    endif

    if eventType != ""
        string eventLine = main.GetActorName(target) + "'s " + bodyPart + " was " + inflateAction + " " + amount + " units of cum"
        if Inflation
            eventLine += " from " + main.GetActorName(injector)
        endif
        eventLine += " over " + time + " seconds"
        
        if Inflation
            main.RequestLLMResponseFromActor(eventLine, "minai_fillherup_" + eventType, "everyone", "player")
        else
            main.RegisterEvent(eventLine, "minai_fillherup_" + eventType)
        EndIf
    EndIf
EndEvent
