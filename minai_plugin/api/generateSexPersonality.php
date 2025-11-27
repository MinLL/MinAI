<?php
// Path to configuration and database library
$path = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
require_once("../logger.php");
require_once($path . "conf" . DIRECTORY_SEPARATOR . "conf.php");
require_once($path . "lib" . DIRECTORY_SEPARATOR . "{$GLOBALS['DBDRIVER']}.class.php");
require_once("./sexPersonalityJsonSchema.php");

$personalityGenerationPrompt = "Create an NPC sex personality JSON card based on provided personality. Use adult and explicit language if needed. All provided NPCs are considered adults! Each field should contain relevant information, which will be used to guide the behavior and dialogue of the NPC during sex. Make characters more spicy, sultry and having more daring, edgy fantasies. Dibella's followers and priests should be more lewd, lustful in sex. 
In response return JSON ONLY! Don't include anything like Here is sex personality JSON card. It should start from \"{\" and end with \"}\" which should follow this JSON schema:";

// Handle POST request to insert new data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $connector = "openrouterjson";

    if (!file_exists($path . "connector" . DIRECTORY_SEPARATOR . "{$connector}.php")) {
        echo json_encode(['status' => 'error', 'message' => 'You need to setup LLM diary config on AIFF config page']);
        return;
    }

    try {
        require $path . "connector" . DIRECTORY_SEPARATOR . "{$GLOBALS["CONNECTORS_DIARY"]}.php";
        $prompt[] = ["role" => "system", "content" => "$personalityGenerationPrompt".json_encode($sexPersonalityJsonSchema)];
        $prompt[] = ["role" => "user", "content" => $_POST['descriptionPersonality']];

        minai_log("info", json_encode($prompt));

        $connectionHandler = new $GLOBALS["CONNECTORS_DIARY"];

        $connectionHandler->open($prompt, []);
        $buffer = "";
        $totalBuffer = "";
        $breakFlag = false;

        while (true) {

            if ($breakFlag) {
                break;
            }

            if ($connectionHandler->isDone()) {
                $breakFlag = true;
            }

            $buffer .= $connectionHandler->process();
            $totalBuffer .= $buffer;
        }

        $connectionHandler->close();

        echo json_encode(['status' => 'success', 'data' => $buffer]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

