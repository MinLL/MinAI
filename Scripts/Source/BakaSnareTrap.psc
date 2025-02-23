Scriptname BakaSnareTrap extends ObjectReference

Keyword Property ActorTypeNPC auto
bool property OnlyPC = false Auto
{Only PC will get stuck}
bool property StartOpen = True Auto
perk property LightFoot auto
bool property checkForLightFootPerk = true Auto
bool property QTE = false Auto
{default == true}
globalVariable property LightFootTriggerPercent auto
Quest Property TNTRController Auto
actor property playerRef Auto
actor property actorref auto hidden

idle property SnareRopeUndoSelfFail auto
idle property SnareRopeUndoSelfSuccess auto
idle property SnareRopeUndoSelfLoop auto
idle property SnareRopeUndoSelfLoop_Done auto
idle property SnareRopeUndoSelfStart auto
idle property SnareRopeStruggle auto
idle property SnareRopeActivateLoop auto
idle property SnareRopeActivateEnter auto
idle property StaggerStart auto
idle property NPCGetUp auto
Package Property TNTRDoNothing auto
bool LockPosition = false
BakaTrapQTEWidgetEx Property StruggleBar Auto
;float property SpeedMult auto
int itrap

Event onReset()
	;goToState("Closed")
	self.resettrap()
endEvent

event onLoad()
	if StartOpen
		playAnimation("Reset01")
		goToState("Open")
	endif
endEvent

auto state Closed
	Event OnBeginState()
	endEvent
	
	event OnTriggerEnter(objectReference TriggerRef)
	endEvent
	
	event OnActivate(objectReference TriggerRef)
		playAnimation("GoStandby")
		resettrap()
		goToState("Open")
	endEvent

endState

state Open
	Event OnBeginState()
 		;debug.Trace(self + " has entered state Open")
		;debug.notification(self + " has entered state Open")
	endEvent

	event OnTriggerEnter(objectReference TriggerRef)
	actorref = TriggerRef as actor
		if !TriggerRef.haskeyword(ActorTypeNPC)
			return
		endif
		if OnlyPC && actorref == playerref
			return
		endif
	
		if checkPerks(TriggerRef) && actorref
 			;debug.notification(self + " has been entered by " + actorref.getactorbase().getname())
			GoToState("Busy")


			(TNTRController as TNTRControllerScript).SetCoordinates(actorref, self, 3)
			playAnimation("TriggerA01")
			SendTNTREvent(actorref, "TriggerA01")
			actorref.playidle(SnareRopeActivateEnter)
			WaitForAnimationEvent("TransA01")
			GoToState("Hooked")
		endif
	endEvent
	
	event OnActivate(objectReference TriggerRef)
; 		debug.Trace(self + " has been activated by " + TriggerRef)
; 		debug.Trace(self + " is in state Open")
		GoToState("Busy")
		playAnimationAndWait("TriggerDisarm","TransTrapDisarm")
		SendTNTREvent(actorref, "TriggerDisarm")
		goToState("ClosedDisarmed")
	endEvent
endState

state ClosedDisarmed
	Event OnBeginState()		
	endEvent
	
	event OnTriggerEnter(objectReference TriggerRef)
	endEvent
	
	event OnActivate(objectReference TriggerRef)
		playAnimationAndWait("TriggerRearm","TransRearm")
		resettrap()
		goToState("Open")
	endEvent
endState

State Hooked
	Event OnBeginState()
		;actorref.playidle(SnareRopeActivateLoop);It's not sequence animation

		Utility.wait(3.0)
		actorref.playidle(SnareRopeStruggle)
		playAnimationAndWait("TriggerD01","TransStruggle")
		SendTNTREvent(actorref, "TriggerD01")
		
		;playAnimationAndWait("TransA02","TransA03");if actor is killed.
		actorref.playidle(SnareRopeUndoSelfStart)
		playAnimationAndWait("TriggerB01","TransB01")
		SendTNTREvent(actorref, "TriggerB01")
		actorref.playidle(SnareRopeUndoSelfLoop)
		int randomi
		if QTE && (actorref == playerref);This is where QTE is placed
			(GetNthLinkedRef(1) as BakaTrapTriggerBox).FireQTE(self)
		else
			randomi = Utility.randomint(1, 3)
			Utility.wait(5.0)
			if randomi == 1
				actorref.playidle(SnareRopeUndoSelfSuccess)
				playAnimationAndWait("TriggerC01","TransC01")
				SendTNTREvent(actorref, "TriggerC01")
				goToState("Escaped")
			else
				actorref.playidle(SnareRopeUndoSelfFail)
				playAnimationAndWait("TransB02","TransB03");Back in Loop
				SendTNTREvent(actorref, "TransB02")
				GoToState("UntieFail")
			endif
		endif
		
	endEvent
	
	event OnTriggerEnter(objectReference TriggerRef)
	endEvent
	
	event OnActivate(objectReference TriggerRef)
	endEvent
EndState

State Busy	;Dummy state to prevent interaction while animating
	Event OnBeginState()
; 		debug.Trace(self + " has entered state Open")
	endEvent
EndState

State UntieFail
	Event OnBeginState()
		Utility.wait(3.0)
		GoToState("Hooked")
	EndEvent
	event OnTriggerEnter(objectReference TriggerRef)
	endEvent
	
	event OnActivate(objectReference TriggerRef)
	endEvent

EndState

State Escaped
	Event OnBeginState()
		(TNTRController as TNTRControllerScript).ClearHookedActor(itrap)
		Utility.wait(2.0)
		(TNTRController as TNTRControllerScript).ResetCoordinates(actorref, false)
	endEvent
	
	event OnTriggerEnter(objectReference TriggerRef)
	endEvent
	
	event OnActivate(objectReference TriggerRef)
		resettrap()
		goToState("Open")
	endEvent
EndState

State Disarmed
	Event OnBeginState()
	endEvent
	
	event OnTriggerEnter(objectReference TriggerRef)
	endEvent
	
	event OnActivate(objectReference TriggerRef)
		playAnimation("TransRearm")
		playAnimation("Reset01")
		goToState("Open")
	endEvent
EndState

State Killed
	Event OnBeginState()
		Debug.notification("Killed State")
		playAnimationAndWait("TriggerKillEnd","TransTrapKill")
		SendTNTREvent(actorref, "TriggerKillEnd")
		goToState("Closed")
	endEvent
	
	event OnTriggerEnter(objectReference TriggerRef)
	endEvent
	
	event OnActivate(objectReference TriggerRef)
	endEvent
EndState
event OnTriggerEnter(objectReference TriggerRef)
endEvent
	
event OnActivate(objectReference TriggerRef)
endEvent

Function resettrap()
	;(TNTRController as TNTRControllerScript).ResetCoordinates(actorref)
	playAnimation("GoStandby")
	playAnimation("Reset01")
endfunction

Bool function checkPerks(objectReference triggerRef)
	if checkForLightFootPerk
; 		;debug.Trace(self + " is checking if " + triggerRef + " has LightFoot Perk")
		if  (triggerRef as actor).hasPerk(LightFoot)
; 			;debug.Trace(self + " has found that " + triggerRef + " has LightFoot Perk")
			if utility.randomFloat(0.0,100.00) <= LightFootTriggerPercent.getValue()
; 				;debug.Trace(self + " is returning false due to failed lightfoot roll")
				return False
			else
; 				;debug.Trace(self + " is returning true due to successful lightfoot roll")
				return True
			endif
		Else
; 			debug.Trace(self + " has found that " + triggerRef + " doesn't have the LightFoot Perk")
			Return True
		EndIf
	else
		return True
	endif
endFunction

Function QTEFail()
	actorref.playidle(SnareRopeUndoSelfFail)
	playAnimationAndWait("TransB02","TransB03")
	;actorref.playidle(SnareRopeUndoSelfLoop_Done)
	actorref.playidle(SnareRopeUndoSelfLoop)
	Utility.wait(3.0)
	actorref.playidle(SnareRopeStruggle)
	playAnimationAndWait("TriggerD01","TransStruggle")
	
	actorref.playidle(SnareRopeUndoSelfStart)
	playAnimationAndWait("TriggerB01","TransB01")
	actorref.playidle(SnareRopeUndoSelfLoop)
	
	(GetNthLinkedRef(1) as BakaTrapTriggerBox).FireQTE(self)
EndFunction

Function QTESuccess()
	actorref.playidle(SnareRopeUndoSelfSuccess)
	playAnimationAndWait("TriggerC01","TransC01")
	goToState("Escaped")
EndFunction

Function actorkilled()
	goToState("Killed")
endfunction

Function AssignTrapNum(int inum)
	itrap = inum
EndFunction

Function SendTNTREvent(Actor akTarget, string asEventName)
    Debug.Trace("Snare sending TNTR event: " + asEventName)
    Int handle = ModEvent.Create("minai_tntr")
    ModEvent.PushForm(handle, akTarget)
    ModEvent.PushString(handle, "Snare")
    ModEvent.PushString(handle, asEventName)
    ModEvent.Send(handle)
EndFunction
