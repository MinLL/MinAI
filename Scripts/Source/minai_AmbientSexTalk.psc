Scriptname minai_AmbientSexTalk extends Quest 

minai_Config config
minai_Sex sex
minai_MainQuestController main

SexLabFramework slf = None
; store registered threads by ostim/sexlab framework:  {ostim: [0,1,2,3], sexlab: [0,1,2,3]}
int jThreadsByFrameworkMap
int playerThread = -1

function Maintenance(minai_Sex _sex, SexLabFramework _slf)
    config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
    sex = _sex
    main = (_sex as Quest) as minai_MainQuestController
    slf = _slf
endfunction

Function OnSexStart(int ThreadID, string framework)
    if(!config.enableAmbientComments)
        return
    endif
    Main.Info("Ambient:OnSexStart")
    RegisterForSingleUpdate(config.commentsRate)

    if(jThreadsByFrameworkMap == 0)
        jThreadsByFrameworkMap = JValue.retain(JMap.object())
    endif

    addThread(ThreadID, framework)
    
    if(framework == sex.sexlabType)
        playerThread = slf.FindPlayerController()
    elseif(framework == sex.ostimType && ThreadID == 0)
        playerThread = 0
    endif
EndFunction

Function OnSexEnd(int ThreadID, string framework)
    if(!config.enableAmbientComments)
        return
    endif
    Main.Info("Ambient:OnSexEnd")
    removeThread(ThreadID, framework)
    if(framework == sex.sexlabType && ThreadID == slf.FindPlayerController())
        playerThread = -1
    elseif(framework == sex.ostimType && ThreadID == 0)
        playerThread = -1
    endif
EndFunction

Event OnUpdate()
    if(!config.enableAmbientComments)
        return
    endif
    Main.Info("Ambient:OnUpdate")
    
    int jOstimThreadsArray = getFrameworkThreads(sex.ostimType)
    int jSexlabThreadsArray = getFrameworkThreads(sex.sexlabType)
    int jThreadsArray
    string framework

    if(JArray.count(jOstimThreadsArray) > 0)
        jThreadsArray = jOstimThreadsArray
        framework = sex.ostimType
    elseif(slf)
        jThreadsArray = jOstimThreadsArray
        framework = sex.sexlabType
    endif

    int threadsCount = JArray.count(jThreadsArray)
    bool hasThreads = threadsCount > 0

    if(hasThreads)
        int ThreadID
        if(playerThread != -1 && config.prioritizePlayerThread)
            ; use player's thread if prioritize config enabled
            ThreadID = playerThread
        else
            ; pick random thread from running threads
            ThreadID = JArray.getInt(jThreadsArray, PO3_SKSEFunctions.GenerateRandomInt(0, threadsCount - 1))    
        endif

        actor[] actors
        if(framework == sex.ostimType)
            actors = OThread.GetActors(ThreadID)
        elseif(framework == sex.sexlabType)
            actors = slf.GetController(ThreadID).Positions
        endif
        
        actor actorToSpeak = sex.getRandomActor(actors)
        Main.Info("Request ambient sextalk for: "+actorToSpeak.GetDisplayName())
        sex.sexTalkAmbient(actorToSpeak, playerThread != -1, framework)
        
        ; register for next OnUpdate cycle only if game has current active threads
        RegisterForSingleUpdate(config.commentsRate)
    endif
EndEvent

int function getFrameworkThreads(string framework)
    return JMap.getObj(jThreadsByFrameworkMap, framework)
endfunction

function addThread(int ThreadID, string framework)
    int jThreadsArr = getFrameworkThreads(framework)
        if(jThreadsArr == 0)
            jThreadsArr = JArray.object()
            JMap.setObj(jThreadsByFrameworkMap, framework, jThreadsArr)
        endif
        JArray.addInt(jThreadsArr, ThreadID)
endfunction

function removeThread(int ThreadID, string framework)
    int jThreadsArr = getFrameworkThreads(framework)
        if(jThreadsArr != 0)
            JArray.eraseInteger(jThreadsArr, ThreadID)
        endif
endfunction