<?php

function importDataToDB($tableName, $folderName, $createQuery)
{
    $folder = __DIR__ . DIRECTORY_SEPARATOR . $folderName;
    $importedVersionsFile = "$folder/imported.txt";
    $db = $GLOBALS['db'];
    $db->execQuery($createQuery);

    if (!is_file($importedVersionsFile)) {
        file_put_contents($importedVersionsFile, "");
    }
    $importedVersions = file($importedVersionsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $importedVersions = is_array($importedVersions) ? $importedVersions : [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    // Loop through each item in the iterator
    foreach ($iterator as $fileInfo) {
        // Check if the current item is a file (not a directory)
        if ($fileInfo->isFile()) {
            $fileName = $fileInfo->getFileName();
            $extension = $fileInfo->getExtension();
            $filePath = $fileInfo->getRealPath();
            if ($extension !== "csv" || in_array($fileName, $importedVersions)) {
                continue;
            }

            if (($handle = fopen($filePath, "r")) !== FALSE) {
                $headers = fgetcsv($handle, 1000, ",");
                
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $insertData = [];

                    foreach ($headers as $index => $header) {
                        $value = $data[$index];
                        if (!$value || $value === "\N") {
                            $value = null;
                        }
                        $insertData[$header] = $value;
                    }

                    $db->insert($tableName, $insertData);
                }

                file_put_contents($importedVersionsFile, $fileName . PHP_EOL, FILE_APPEND);
            }
        }
    }

}

function importScenesDescriptions() {
    $tableName = "minai_scenes_descriptions";
    importDataToDB($tableName, "sceneDescriptionsDBImport", "CREATE TABLE IF NOT EXISTS $tableName (
        ostim_id character varying(256),
        sexlab_id character varying(256),
        description text
      )");
}

function importXPersonalities() {
    $tableName = "minai_x_personalities";
    importDataToDB($tableName, "xPersonalitiesDBImport", "CREATE TABLE IF NOT EXISTS $tableName (
        id character varying(256) PRIMARY KEY,
        x_personality JSONB
      )");
}

?>
