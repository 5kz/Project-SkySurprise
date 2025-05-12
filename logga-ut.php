<?php

require_once 'func.php';   //se till att func filen finns
session_start(); //starta sessionen

session_unset(); 
session_destroy(); // "logga ut" genom att förstöra sessionen

header("Location: main.php"); //skicka användaren till startsidan
exit();
?>