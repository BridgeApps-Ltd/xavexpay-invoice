<?php

/*
Gets list of all log files in a given server
and places them in session to be used later
*/

session_start();

/*
Logic 
- Loop through list of all- predefined directory paths - in the VM (EC2)
- get list of all files including full path > add it to an Map
- Add this Map to Session
*/

// Give a name of LogDirectories on the filesystem. Note: DO not end the folder with /
$logDirectories["cargofl-core"] = "C:\Programming\wamp64-321\logs";
$logDirectories["nginx"] = "C:\Programming\tomcat\apache-tomcat-6.0.53\logs";

// Check if the values are already set in Session, if so, so not load again.
// unless asked using the "Get Logs" button
if(!isset($_SESSION['FILE_MAP'])) {
    $loadLogs = 'Y';
}
else if(isset($_SESSION['FILE_MAP']) && isset($_GET['start']) && ($_GET['start'] == 'Y')){
    $loadLogs = 'Y';
} else {
    $loadLogs = 'N';
}


if ($loadLogs == 'Y'){

    foreach ($logDirectories as $dirkey => $dirvalue) {
        
        //echo "... Reading Directory = ".$dirvalue."\n";
        if (is_dir($dirvalue)){
            // is a valid Directory on this file system
            $filesInLogDir = scandir($dirvalue);

            foreach ($filesInLogDir as $key => $value) {
                if (!in_array($value,array(".",".."))){
                    $fullFilePath = $dirvalue . DIRECTORY_SEPARATOR . $value;
                    if (!is_dir($fullFilePath)){
                        // is not a directory, continue with creating the full path
                        $logFileMap[$value] = $fullFilePath ; // create a hashmap as filename => full path of file                       
                        $logFileForUIMap[$value] = $dirkey.DIRECTORY_SEPARATOR.$value; // create label Map for UI as filename => cargofl-core/access.log etc.
                    } else {
                        // is a directory, do nothing. Dirs are not expected here
                    } // end if dir check
                } // end if ignore . & ..
            } // end foreach 2
        } else {
            // the dir doesnt exist in the filesystem, so just ignore 
        }
    } // end for 1

    // Add this file map to the session
    $_SESSION['FILE_MAP'] = $logFileMap;
    $_SESSION['FILE_UI_MAP'] = $logFileForUIMap;

   
}

//print_r ($_SESSION['FILE_MAP']);
?>
