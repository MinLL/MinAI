<?php

require_once("action_builder.php");

// StartLooting Action
directRegisterAction(
    "ExtCmdStartLooting", 
    "StartLooting", 
    "Start looting the area",
    true
);

// StopLooting Action
directRegisterAction(
    "ExtCmdStopLooting", 
    "StopLooting", 
    "Stop looting the area",
    true
);

