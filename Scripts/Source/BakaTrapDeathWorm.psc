Scriptname BakaTrapDeathWorm extends BakaMovingTrap

import debug
import utility
	
;If Loop is true, it swings until activated again.
;If Loop is false, it swings once when activated.
bool restartLooping = false
bool finishedPlaying = false
float property initialDelay = 0.25 auto
bool property selfTrigger = true auto
{If this is true, then the trap will trigger when you enter the baked in trigger volume
	Default = TRUE}
string property startDamage = "startDamage" auto hidden
string property stopDamage = "stopDamage" auto hidden
;-----------------------------------

Function SendTNTREvent(Actor akTarget, string asEventName)
	Debug.Trace("DeathWorm sending TNTR event: " + asEventName)
	Int handle = ModEvent.Create("minai_tntr")
	ModEvent.PushForm(handle, akTarget)
	ModEvent.PushString(handle, "DeathWorm")
	ModEvent.PushString(handle, asEventName)
	ModEvent.Send(handle)
EndFunction

Function fireTrap()
	;PlayAnimationAndWait( "reset", "off" )
	;Basic wind up and fire once checking
	;TRACE("fireTrap called")
	ResolveLeveledDamage()
	
	isFiring = True
	WindupSound.play( self as ObjectReference)		;play windup sound

	wait( initialDelay )		;wait for windup
	;TRACE("Initial Delay complete")
	
	if (fireOnlyOnce == True)	;If this can be fired only once then disarm
		trapDisarmed = True
	endif
	
	;TRACE("Looping =")
	;TRACE(Loop)
	
	;Trap Guts
	while(finishedPlaying == False && isLoaded == TRUE)
		;TRACE("playing anim Single")

		PlayAnimation("Trigger01")
		SendTNTREvent(lastActivateRef as Actor, "Trigger01")
		WaitForAnimationEvent(startDamage)
		BakahitBase.goToState("CanHit")
		finishedPlaying = True
		WaitForAnimationEvent(stopDamage)
		BakahitBase.goToState("CannotHit")
		WaitForAnimationEvent("done")
		if (loop == TRUE)			;Reset Limiter
			resetLimiter()
		endif
		wait(0.0)
	endWhile
	
	if isLoaded 	
		isFiring = false
		;BakahitBase.goToState("CannotHit")
		;PlayAnimationAndWait( "reset", "off" )
		goToState("Reset")
	endif
	
endFunction

Function ResetLimiter()
	finishedPlaying = False
EndFunction


auto State Idle
	
	event onActivate (objectReference activateRef)
; 		;debug.TRACE("Idle trigger")
		lastActivateRef = activateRef
		if (trapDisarmed == False)
			if init == False	;determine if we should initialize
				initialize()
				init = True
			endif
			
			TrapTriggerBase TriggerRef					;TriggerRef will always be a TrapTriggerBase
			TriggerRef = activateRef as TrapTriggerBase		;Set TriggerRef to our activateRef
; 			;debug.Trace("Type = " + TriggerRef.Type)
			if activateRef == (self as objectReference)
				GoToState ( "DoOnce" )								;DoOnce Trigger Type
				ResetLimiter()
				FireTrap()
			elseif TriggerRef
				;TRACE("Type =")
				;TRACE(TriggerRef.Type)
				if (TriggerRef.Type == 0)						;if Type = 0 Single activate
					GoToState ( "DoOnce" )								;DoOnce Trigger Type
					ResetLimiter()
					FireTrap()
				elseif (TriggerRef.Type == 1) 				;Hold Trigger Type
					GoToState ( "Hold" )
					ResetLimiter()
					Loop = TRUE
					FireTrap()
				elseif (TriggerRef.Type >1 && TriggerRef.Type < 4) ;Most Trigger Types use On
					GoToState ( "On" )
					ResetLimiter()
					Loop = TRUE
					FireTrap()
				endif
			else
				GoToState ( "DoOnce" )								;DoOnce Trigger Type
				ResetLimiter()
				FireTrap()
			endif
		endif
	endevent
	
	Event onTriggerEnter(ObjectReference triggerRef)
		if selfTrigger && acceptableTrigger(triggerRef)
			self.Activate(Self)
		endif
	endEvent
endstate

State DoOnce															;Type Do Once
	
	Event OnActivate( objectReference activateRef )
; 		;debug.TRACE(self + "Do Once trigger")
		lastActivateRef = activateRef
		if (trapDisarmed == FALSE)
			TrapTriggerBase TriggerRef							;TriggerRef will always be a TrapTriggerBase
			TriggerRef = activateRef as TrapTriggerBase		;Set TriggerRef to our activateRef			
			
; 			;debug.Trace("Type = " + TriggerRef.Type)
			
			If TriggerRef
				if (TriggerRef.Type == 0)					;Type Do Once
					resetLimiter()
				endif
				
				if (TriggerRef.Type == 1)					;Type Hold
					GoToState("Hold")
					resetLimiter()
					Loop = TRUE
				endif
				
				if (TriggerRef.Type >1 && TriggerRef.Type < 4) ;Most Trigger Types use On
					GoToState ( "On" )
					ResetLimiter()
					Loop = TRUE
				endif
				
				if (TriggerRef.Type == 4)					;Type Hold
					Loop = FALSE
					GoToState("Reset")
				endif
			else
			
			endif
		endif
		
	EndEvent

endstate

State Reset

	Event OnBeginState()
		overrideLoop = True
		GoToState ( "Idle" )
		BakahitBase = (self as objectReference) as BakaTrapHitBase
		if BakahitBase
			BakahitBase.goToState("CannotHit")
		endif
	endEvent
	
	Event OnActivate( objectReference activateRef )
		lastActivateRef = activateRef
	EndEvent
	
endState

State On
	
	event onActivate (objectReference activateRef)
; 		;debug.TRACE(self + "On trigger")
		lastActivateRef = activateRef
		if (trapDisarmed == FALSE)
			TrapTriggerBase TriggerRef							;TriggerRef will always be a TrapTriggerBase
			TriggerRef = activateRef as TrapTriggerBase		;Set TriggerRef to our activateRef
			
; 			;debug.Trace("Type = " + TriggerRef.Type)
			
			if TriggerRef
				;/
				if (TriggerRef.Type == 1)					;Type Hold
					GoToState("Hold")
					resetLimiter()
					Loop = TRUE
				endif
				/;
				
				if (TriggerRef.Type == 4)					;if Type = 4 Turn Off
					GoToState ( "Reset" )
					Loop = FALSE
					overrideLoop = True
				endif
				
				if (TriggerRef.Type == 2)					;if Type = 2 Toggle
					GoToState ( "Reset" )
					Loop = FALSE
					overrideLoop = True
				endif
			endif
		endif
		
	endevent

endstate

State Hold			;Hold overrides all other states ***This is kind of a depricated state ***
	
	event onActivate (objectReference activateRef)
; 		;debug.TRACE(self + "Hold trigger")
		lastActivateRef = activateRef
		if (trapDisarmed == FALSE)
			TrapTriggerBase TriggerRef							;TriggerRef will always be a TrapTriggerBase
			TriggerRef = activateRef as TrapTriggerBase		;Set TriggerRef to our activateRef
			
; 			;debug.Trace("Type = " + TriggerRef.Type)
			
			if TriggerRef
				if (TriggerRef.Type == 1)						;if Type = 1 Turn Off
					GoToState ( "On" )
					Loop = FALSE
				endif
				
				if (TriggerRef.Type == 2)					;Type Hold
					Loop = FALSE
					GoToState("Reset")
				endif
				
				if (TriggerRef.Type == 3)						;if Type = 1 Turn Off
					GoToState ( "On" )
					Loop = FALSE
				endif
				
				if (TriggerRef.Type == 4)					;Type Hold
					Loop = FALSE
					GoToState("Reset")
				endif
			endif
		endif
	endEvent

endstate

State Disarmed

endState
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

faction property owningFaction Auto
actorBase property owningActorBase Auto
bool property playerOnlyTrigger = false auto
bool property checkForLightFootPerk = false Auto
{Setting this will give a chance that the trap trigger will not Fire
	this does not work on triggers with the HOLD trigger type
	Set on by default on all pressure plates
	you should only mess with this locally for triggerboxes}
bool property checkForLightHandsPerk = false Auto
{Setting this will give a chance that the trap trigger will not Fire when lockpicked
	this is used specifically for hinge triggers or other things like that
	set to on by default on the hinge trigger}
perk property lightFootPerk Auto
globalVariable property LightFootGlobalVar auto
perk property lightHandsPerk Auto
globalVariable property LightHandsGlobalVar auto

bool function acceptableTrigger(objectReference triggerRef)

; 	debug.Trace(self + " is checking if " + triggerRef + " is an acceptable trigger")
	if playerOnlyTrigger
		if triggerRef == game.getPlayer()
; 			debug.Trace(self + " has found that " + triggerRef + " is an acceptable trigger")
			if checkPerks(triggerRef)
				Return True
			Else
				Return False
			endif
		Else
; 			debug.Trace(self + " has found that " + triggerRef + " is not an acceptable trigger")
			return False
		endif
	Else
		if !(triggerRef as actor)	;if this is not a player only trigger and this is not an actor
			return True
		elseif owningFaction
			if (triggerRef as actor).IsInFaction(owningFaction)
; 				debug.Trace(self + " has found that " + triggerRef + " is not an acceptable trigger")
				return False
			else
; 				debug.Trace(self + " has found that " + triggerRef + " is an acceptable trigger")
				if checkPerks(triggerRef)
					Return True
				Else
					Return False
				endif
			endif
		else
			if owningActorBase
				if (triggerRef as actor).getActorBase() == owningActorBase
; 					debug.Trace(self + " has found that " + triggerRef + " is not an acceptable trigger")
					return False
				Else
; 					debug.Trace(self + " has found that " + triggerRef + " is an acceptable trigger")
					if checkPerks(triggerRef)
						Return True
					Else
						Return False
					endif
				endif
			else
; 				debug.Trace(self + " has found that " + triggerRef + " is an acceptable trigger")
				if checkPerks(triggerRef)
					Return True
				Else
					Return False
				endif
			endif
		endif
	endif
endFunction

Bool function checkPerks(objectReference triggerRef)
	if checkForLightFootPerk
; 		debug.Trace(self + " is checking if " + triggerRef + " has LightFoot Perk")
		if  (triggerRef as actor).hasPerk(lightFootPerk)
; 			debug.Trace(self + " has found that " + triggerRef + " has LightFoot Perk")
			if utility.randomFloat(0.0,100.00) <= LightFootGlobalVar.getValue()
; 				debug.Trace(self + " is returning false due to failed lightfoot roll")
				return False
			else
; 				debug.Trace(self + " is returning true due to successful lightfoot roll")
				return True
			endif
		Else
; 			debug.Trace(self + " has found that " + triggerRef + " doesn't have the LightFoot Perk")
			Return True
		EndIf
	elseif checkForLightHandsPerk
; 		debug.Trace(self + " is checking if " + triggerRef + " has LightHands Perk")
		if  (triggerRef as actor).hasPerk(lightHandsPerk)
; 			debug.Trace(self + " has found that " + triggerRef + " has LightFoot Perk")
			if utility.randomFloat(0.0,100.00) <= LightHandsGlobalVar.getValue()
; 				debug.Trace(self + " is returning false due to failed lightHands roll")
				return False
			else
; 				debug.Trace(self + " is returning true due to successful lightHands roll")
				return True
			endif
		Else
; 			debug.Trace(self + " has found that " + triggerRef + " doesn't have the LightFoot Perk")
			Return True
		EndIf
	Else
; 		debug.Trace(self + " has found that " + triggerRef + " doesn't have applicable perks")
		return True
	EndIf
endFunction