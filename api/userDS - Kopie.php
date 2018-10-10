<?php

session_start();
require_once('adodb5/adodb.inc.php');
require_once('db_psw.php');
date_default_timezone_set('europe/berlin');
$host = (htmlspecialchars($_SERVER["HTTP_HOST"]));
$uri = rtrim(dirname(htmlspecialchars($_SERVER["PHP_SELF"])), "/\\");

$filename = "";
$status = "";
$info = "";
$ip = getenv("REMOTE_ADDR"); // get the ip number of the user  

$browser = $_SERVER['HTTP_USER_AGENT'];

function os() {
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $os = "unknown";
    if (strstr($agent, "Windows 98")) {
        $os = "Windows 98";
    } elseif (strstr($agent, "NT 4.0")) {
        $os = "Windows NT ";
    } elseif (strstr($agent, "NT 5.1")) {
        $os = "Windows XP";
    } elseif (strstr($agent, "NT 5.2")) {
        $os = "Windows XP Professional x64 Edition";
    } elseif (strstr($agent, "NT 6.0")) {
        $os = "Vista";
    } elseif (strstr($agent, "NT 6.1")) {
        $os = "Windows 7";
    } elseif (strstr($agent, "NT 6.2")) {
        $os = "Windows 8";
    } elseif (strstr($agent, "NT 6.3")) {
        $os = "Windows 8.1";
    } elseif (strstr($agent, "Mac")) {
        $os = "Mac OS";
    } elseif (strstr($agent, "Linux")) {
        $os = "Linux";
    } elseif (strstr($agent, "Unix")) {
        $os = "Unix";
    }
    return $os;
}

$ADODB_CACHE_DIR = 'C:/php/cache';


$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC; // Liefert ein assoziatives Array, das der geholten Zeile entspricht 

$ADODB_COUNTRECS = true;

$dbSyb = ADONewConnection("mysqli");

// DB-Abfragen NICHT cachen
$dbSyb->memCache = false;
$dbSyb->memCacheHost = array('localhost'); /// $db->memCacheHost = $ip1; will work too
$dbSyb->memCacheCompress = false; /// Use 'true' arbeitet unter Windows nicht
//$dsn = "'localhost','root',psw,'vitaldb'";
$dbSyb->Connect('localhost', user, psw, db); //=>>> Verbindungsaufbau mit der DB

if (!$dbSyb->IsConnected()) {


    print ("Anmeldung: " . $dbSyb->ErrorMsg());

    $data = array();

    return ($data);
}

$dbSyb->debug = false;



if (isset($_POST["name"])) {
    $benutzer = $_POST["name"];
}

if (isset($_POST["passwort"])) {
    $passwort = sha1($_POST["passwort"]);
}

/*  if (isset($_POST["passwort"])) {
  $passwort = $_POST["passwort"];
  } */

$querySQL = "
       call loginProc (" . $dbSyb->Quote($benutzer)        //=>>> SQL-Abfrage wird erstellt
        . "," . $dbSyb->Quote($passwort) . ")";

//  $fp = fopen("loginProc", "w");  // =>> Hier wird eine txt erzeugt, mit der man eine Variable bzw. eine SQL-Abfrage
//fputs($fp, $querySQL);             // auslesen kann. Die Variable / Abfrage muss in dieser Zeile, nach $fp, eingesetzt
//fclose($fp); 

/* @var $rs string */
$rs = $dbSyb->Execute($querySQL); //=>>> Abfrage wird an den Server �bermittelt / ausgef�hrt?
// Ausgabe initialisieren


if (!$rs) {
    // keine Query hat nicht funtioniert

    print("Query 1: " . $dbSyb->ErrorMsg());
    
    return;
}
else {
    if ($rs->fields{'Ergebnis'} == 1 && $rs->fields{'status'} == 'B') { // Passwort OK und User ist freigeschaltet - Anmeldung erfolgreich
        $_SESSION["benutzer"] = $benutzer;
        $_SESSION["login"] = 1;
        $_SESSION["admin"] = $rs->fields{'admin'};
        $extra = "../start.php";
        $status = "[INFO]";
        $info = "Anmeldung erfolgreich";
        $log = $status . "  -- " . "IP: " . $ip . " -- " . date('d-m-Y H:i:s') . " -- " . "Angemeldeter User: " . $_SESSION['benutzer'] . " -- " . $info . " -- Browser: " . $browser . " -- OS: " . os() . "\n\n";
 
	
	} elseif ($rs->fields{'Ergebnis'} == 1 && $rs->fields{'status'} == 'O') { // Passwort ist OK aber der User ist nicht freigeschaltet - Anmeldung nicht möglich
        $data{$i}{"ergebnis"} = "User " . $benutzer . " ist noch nicht freigeschaltet!";
        $_SESSION["benutzer"] = $benutzer;
        $_SESSION["login"] = "falsch";
        $_SESSION["admin"] = $rs->fields{'admin'};
        $_SESSION["loginReport"] = "User " . $benutzer . " ist noch nicht freigeschaltet!";
        $extra = "../login.php";
        $status = "[ERROR]";
        $info = "Benutzer '" . $benutzer . "' ist noch nicht freigeschaltet!";
        $log = $status . " -- " . "IP: " . $ip . " -- " . date('d-m-Y H:i:s') . " -- " . $info . " -- Browser: " . $browser . " -- OS: " . os() . "\n\n";
		
	
    } elseif ($rs->fields{'Ergebnis'} == -99) { // User ist wegen 3 Login-Fehlversuchen 30 Minuten gesperrt
        $data{$i}{"ergebnis"} = "Das Konto " . $benutzer . " ist aufgrund zu häufiger Login-Fehlversuche </br>bis zu 30 Minuten gesperrt.";
        $_SESSION["benutzer"] = $benutzer;
        $_SESSION["login"] = "falsch";
        $_SESSION["loginReport"] = "Das Konto " . $benutzer . " ist aufgrund zu häufiger Login-Fehlversuche </br>bis zu 30 Minuten gesperrt.";
        $_SESSION["admin"] = $rs->fields{'admin'};
        $extra = "../login.php";
        $status = "[ERROR]";
        $info = "Benutzer '" . $benutzer . "' ist gesperrt.";
        $log = $status . " -- " . "IP: " . $ip . " -- " . date('d-m-Y H:i:s') . " -- " . $info . " -- Browser: " . $browser . " -- OS: " . os() . "\n\n";
	
		
    } elseif ($rs->fields{'Ergebnis'} == -98) { // User hat seinen Passwort 3 mal falsch eingegeben und wird für 30 Min. gesperrt
        $data{$i}{"ergebnis"} = "Sie haben mehr als 3 Mal ihr Passwort falsch eingegeben. </br> Ihr Konto wird für 30 Minuten gesperrt.";
        $_SESSION["benutzer"] = $benutzer;
        $_SESSION["login"] = "falsch";
        $_SESSION["loginReport"] = "Sie haben mehr als 3 Mal ihr Passwort falsch eingegeben. </br> Ihr Konto wird für 30 Minuten gesperrt.";
        $_SESSION["admin"] = $rs->fields{'admin'};
        $extra = "../login.php";
        $status = "[ERROR]";
        $info = "Benutzer '" . $benutzer . "' wird für 30 Min. gesperrt.";
        $log = $status . " -- " . "IP: " . $ip . " -- " . date('d-m-Y H:i:s') . " -- " . $info . " -- Browser: " . $browser . " -- OS: " . os() . "\n\n";
	
		
    } else { // Anmeldung fehlgeschlagen - evtl. Passwort falsch oder Username falsch
        $data{$i}{"ergebnis"} = "Anmeldung ist fehlgeschlagen";
        $_SESSION["benutzer"] = $benutzer;
        $_SESSION["login"] = "falsch";
        $_SESSION["loginReport"] = "Ah Ah Ah...du hast das Zauberwort vergessen....Ah ah ah...";
        $_SESSION["admin"] = $rs->fields{'admin'};
        $extra = "../login.php";
        $status = "[ERROR]";
        $info = "Anmeldung ist fehlgeschlagen";
        $log = $status . " -- " . "IP: " . $ip . " -- " . date('d-m-Y H:i:s') . " -- " . $info . " -- Browser: " . $browser . " -- OS: " . os() . "\n\n";
    
	
	}

    if (!file_exists(getcwd() . "\\logs")) {// Prüft ob das Verzeichnis existiert. Wenn das Verzeichnis nicht existiert, wird eins erstellt
        /*if (mkdir(getcwd() . "\\logs", 0700)) {
            $filename = getcwd() . "\\logs\\" . date('Y-m-d') . ".log";
            file_put_contents($filename, $log, FILE_APPEND);
        }*/
		mkdir(getcwd() . "\\logs", 0777, true);
		chmod($getcwd() . "\\logs", 0777);
            $filename = getcwd() . "\\logs\\" . date('Y-m-d') . ".log";
            file_put_contents($filename, $log, FILE_APPEND);
    } else {
        $filename = getcwd() . "\\logs\\" . date('Y-m-d') . ".log";
        file_put_contents($filename, $log, FILE_APPEND);
    }

    header("Location: http://$host$uri/$extra");
}
?>