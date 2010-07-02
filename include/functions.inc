<?php
    session_start();

function dbConnect($server='localhost', $username='root', $password='7ynMELdb')
{
    @ $db = mysql_connect("$server", "$username", "$password") or die(mysql_error());
    if (!$db) {
        echo "Error: Could not connect to database. Try again later.";
        exit();
    }
     
    return $db;
}

?>
