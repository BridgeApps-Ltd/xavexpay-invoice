<?php error_reporting (E_ALL ^ E_NOTICE); ?> 
<?php
session_start();
/*
 * This file is a wrapper around tail.sh,
 * a script you can use to read a file if the server process
 * does not have read permissions on the file.
 *
 * If permissions are not an issue:
 *   1. Edit tail.sh to point to your log file.
 *   2. Remove 'sudo' from the command below.
 *
 * If the web server cannot read the log file:
 *   1. Edit tail.sh to point to your log file
 *   2. sudo chown root:root tail.sh
 *   3. sudo chmod 700 tail.sh
 *   4. Add to sudoers file:
 *      ALL    ALL = (root) NOPASSWD: /path/to/tail.sh
 *
 * Otherwise, I suppose you could write a pure PHP version
 * if you don't want to use the shell script.
 */

header('Content-Type: text/plain');

if(isset($_GET['start'])){
    $n = (int) $_GET['start'];
}

$fname ="";
$fnamewithpath = "";
if(isset($_GET['logfile'])){
    $fname = $_GET['logfile'];
    
    $logFileMap = $_SESSION['FILE_MAP'] ;
    echo "\n";
    echo "... FileMap = ".$logFileMap;
    $fnamewithpath = $logFileMap[$fname];  
    echo "\n";
    echo "... File Name Path = ".$fnamewithpath;
}

$lcmd = "sudo tail  ".$fnamewithpath." ".$n ;
echo "\n";
echo ".. Command to Execute = ".$lcmd;

passthru($lcmd);



?>
