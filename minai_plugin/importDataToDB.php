<?php

function importDataToDB($tableName, $folderName, $createQuery, $checkDuplicatesColumns = [])
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

    // Initialize an array to store files and their associated dates
    $filesWithDates = [];

    // Loop through all files
    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isFile()) {
            $fileName = $fileInfo->getFileName();
            error_log("Processing $fileName");
            $extension = $fileInfo->getExtension();
            $filePath = $fileInfo->getRealPath();
            if ($extension !== "csv" || in_array($fileName, $importedVersions)) {
                error_log("Not processing");
                continue;
            }
            
            // Use regex to extract the date part from the filename (assuming date format is MM_DD_YYYY)
            if (preg_match('/_(\d{2})_(\d{2})_(\d{4})\.csv$/', $fileName, $matches)) {
                // Build the date string in YYYY-MM-DD format
                $dateString = $matches[3] . '-' . $matches[1] . '-' . $matches[2];

                // Convert the date string into a DateTime object for sorting
                $date = DateTime::createFromFormat('Y-m-d', $dateString);

                // Store the file and the associated date
                $filesWithDates[] = ['fileName' => $fileName, 'filePath' => $filePath, 'date' => $date];
            }
        }
    }

    // Sort the files by the date, from earliest to latest
    usort($filesWithDates, function($a, $b) {
        return $a['date'] <=> $b['date'];
    });

    // Loop through each item in the iterator
    foreach ($filesWithDates as $fileWithDate) {
        $fileName = $fileWithDate['fileName'];
        $filePath = $fileWithDate['filePath'];
        // Check if the current item is a file (not a directory)
        error_log("Opening $filePath");                

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

                if (!empty($checkDuplicatesColumns)) {
                    $whereClause = [];
                    
                    foreach ($checkDuplicatesColumns as $column) {
                        $value = $db->escape($insertData[$column]);
                        if($value) {
                            $whereClause[] = "$column = '{$value}'";
                        } else {
                            $whereClause[] = "$column IS NULL";
                        }
                    }
                    $whereQuery = implode(' AND ', $whereClause);
                    $checkQuery = "SELECT COUNT(*) FROM $tableName WHERE $whereQuery";
                    // $params = array_intersect_key($insertData, array_flip($checkDuplicatesColumns));
                    $result = $db->query($checkQuery);
                    $row = $db->fetchArray($result);

                    if ($row["count"] > 0) {
                        
                        // Update the row if it exists
                        $updateQuery = "UPDATE $tableName SET ";
                        $setClause = [];
                        foreach ($insertData as $column => $value) {
                            $value = $db->escape($insertData[$column]);
                            $setClause[] = "$column = '{$value}'";
                        }
                        $updateQuery .= implode(', ', $setClause);
                        $updateQuery .= " WHERE $whereQuery";
                        $db->query($updateQuery, $insertData);
                    } else {
                        // Insert the row if it does not exist
                        $db->insert($tableName, $insertData);
                    }
                } else {
                    // If no columns to check for duplicates, simply insert
                    $db->insert($tableName, $insertData);
                }
            }

            file_put_contents($importedVersionsFile, $fileName . PHP_EOL, FILE_APPEND);
        }
    }

}

function importScenesDescriptions() {
    $tableName = "minai_scenes_descriptions";
    importDataToDB($tableName, "sceneDescriptionsDBImport", "CREATE TABLE IF NOT EXISTS $tableName (
        ostim_id character varying(256),
        sexlab_id character varying(256),
        description text
      )", ["ostim_id", "sexlab_id"]);
}

function importXPersonalities() {
    $tableName = "minai_x_personalities";
    importDataToDB($tableName, "xPersonalitiesDBImport", "CREATE TABLE IF NOT EXISTS $tableName (
        id character varying(256) PRIMARY KEY,
        x_personality JSONB
      )", ["id"]);
}

?>
