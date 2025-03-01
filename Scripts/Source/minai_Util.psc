scriptname minai_Util extends Quest

Actor Property PlayerRef auto
minai_Config Property config Auto

Function Maintenance()
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
EndFunction


string Function GetActorName(actor akActor)
  if akActor == playerRef
    return akActor.GetActorBase().GetName()
  else
    return akActor.GetDisplayName()
  EndIf
EndFunction

String Function JoinStringArray(string[] strArr, string separator = ",")
  String result = ""
  int index = 0
  int size = strArr.Length

  ; Loop through the array using a while loop
  while (index < size)
      string str = strArr[index]
      if (str)
          ; Append the actor's name to the result string
          result += str

          ; If it's not the last actor, append a comma
          if (index < size - 1)
              result += separator
          endif
      endif
      
      index += 1
  endwhile
  
  return result
EndFunction

String Function JoinActorArray(Actor[] actorArray, string separator = ",")
  String result = ""
  int index = 0
  int size = actorArray.Length

  ; Loop through the array using a while loop
  while (index < size)
      Actor currentActor = actorArray[index]
      if (currentActor)
          ; Append the actor's name to the result string
          result += GetActorName(currentActor)

          ; If it's not the last actor, append a comma
          if (index < size - 1)
              result += separator
          endif
      endif
      
      index += 1
  endwhile
  
  return result
EndFunction

actor function getRandomActor(actor[] actors)
  int index = PO3_SKSEFunctions.GenerateRandomInt(0, actors.length - 1)

  return actors[index]
endfunction

Function Log(String str, string lvl)
  Debug.Trace("[minai (" + lvl + ")]: " + str)
  if config.enableConsoleLogging
    MiscUtil.PrintConsole("[minai (" + lvl + ")]: " + str)
  EndIf
EndFunction

Function Fatal(String str)
  ; Always log fatals
  Log(str, "FATAL")
  Debug.MessageBox(str)
EndFunction


Function Error(String str)
  if config.logLevel >= 1
    Log(str, "ERROR")
  EndIf
EndFunction


Function Warn(String str)
  if config.logLevel >= 2
    Log(str, "WARN")
  EndIf
EndFunction


Function Info(String str)
  if config.logLevel >= 3
    Log(str, "INFO")
  EndIf
EndFunction

Function Debug(String str)
  if config.logLevel >= 4
    Log(str, "DEBUG")
  EndIf
EndFunction

Function DebugVerbose(String str)
  if config.logLevel >= 5
    Log(str, "VERBOSE")
  EndIf
EndFunction

Function Trace(String str)
  if config.logLevel >= 6
    Log(str, "TRACE")
  EndIf
EndFunction