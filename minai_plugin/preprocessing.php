<?php

require_once("util.php");
require_once("contextbuilders.php");
require_once("roleplaybuilder.php");
// TODO: Add an actual install routine to the HerikaServer proper to not do this every request.
// InitiateDBTables();

interceptRoleplayInput();

