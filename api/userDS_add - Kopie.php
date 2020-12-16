<?php
session_start();
$host = (htmlspecialchars($_SERVER["HTTP_HOST"]));
$uri = rtrim(dirname(htmlspecialchars($_SERVER["PHP_SELF"])), "/\\");



require_once('adodb5/adodb.inc.php');
require_once('db_psw.php');

$ADODB_CACHE_DIR = 'C:/php/cache';


$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC; // Liefert ein assoziatives Array, das der geholten Zeile entspricht 

$ADODB_COUNTRECS = true;

$dbSyb = ADONewConnection("mysqli");

// DB-Abfragen NICHT cachen
$dbSyb->memCache = false;
$dbSyb->memCacheHost = array('localhost'); /// $db->memCacheHost = $ip1; will work too
$dbSyb->memCacheCompress = false; /// Use 'true' arbeitet unter Windows nicht
//$dsn = "'localhost','root',psw,'vitaldb'";
$dbSyb->Connect(link, user, psw, db); //=>>> Verbindungsaufbau mit der DB

$data = array();
if (!$dbSyb->IsConnected()) {

	  ?>
    <script type="text/javascript">
        parent.document.getElementById("message").innerHTML = "<?php echo ("Anmeldung: " . $dbSyb->ErrorMsg()); ?>";
    </script>
    <?php
	return;
}

$dbSyb->debug = false;


if (isset($_REQUEST["benutzer"])) {
    $benutzer = ($_REQUEST["benutzer"]);
    if(trim($benutzer) != ""){
    if ((preg_match("/^[0-9a-zA-Z-+*_.]{3,15}$/", trim($benutzer))) == 0) {
        ?>
    <script type="text/javascript">
        parent.document.getElementById("message").innerHTML = "<?php echo ('Der Benutzername darf nur aus den Zeichen 0-9 a-z A-Z -+*_. bestehen</br> und muss mind. aus 3 und max. aus 15 Zeichen bestehen.'); ?>";
    </script>
    <?php
    return;}
    }else {
    ?>
    <script type="text/javascript">
        parent.document.getElementById("message").innerHTML = "<?php echo 'Bitte den Benutzernamen eingeben!' ?>";
    </script>
    <?php
    return;
}
} else {
    ?>
    <script type="text/javascript">
        parent.document.getElementById("message").innerHTML = "<?php echo 'Bitte den Benutzernamen eingeben!' ?>";
    </script>
    <?php
    return;
}

if (isset($_REQUEST["passwort"])) {
    if(trim($_REQUEST["passwort"]) != ""){  
        $_passwort = $_REQUEST["passwort"];
    if ((preg_match("/^[0-9a-zA-Z-+*_.]{6,12}$/", trim($_passwort))) == 0) {
        ?>
    <script type="text/javascript">
        parent.document.getElementById("message").innerHTML = "<?php echo ('Das Passwort darf nur aus den Zeichen 0-9 a-z A-Z -+*_. bestehen </br> und muss mind. aus 6 und max. aus 12 Zeichen bestehen.'); ?>";
    </script>
    <?php
    return;}
    }else {
    ?>
    <script type="text/javascript">
        parent.document.getElementById("message").innerHTML = "<?php echo 'Bitte das Passwort eingeben!' ?>";
    </script>
    <?php
    return;
}
}else {
    ?>
    <script type="text/javascript">
        parent.document.getElementById("message").innerHTML = "<?php echo 'Bitte das Passwort eingeben!' ?>";
    </script>
    <?php
    return;
}
//------------------------- Passwort 2 ------------------------------------------------------------------------------------------------------------------------------------------
if (isset($_REQUEST["passwort2"])) {
    if(trim($_REQUEST["passwort2"]) != ""){  
        $_passwort2 = $_REQUEST["passwort2"];
    if ($_passwort2 != $_passwort) {
        ?>
    <script type="text/javascript">
        parent.document.getElementById("message").innerHTML = "<?php echo (('Die Passwörter stimmen nicht überein!')); ?>";
    </script>
    <?php
    return;}
    }else {
    ?>
    <script type="text/javascript">
        parent.document.getElementById("message").innerHTML = "<?php echo(('Bitte bestätigen Sie Ihr Passwort')); ?>";
    </script>
    <?php
    return;
}
}else {
    ?>
    <script type="text/javascript">
        parent.document.getElementById("message").innerHTML = "<?php echo ('Bitte das Passwort eingeben!'); ?>";
    </script>
    <?php
    return;
}

//------------------------- Email ------------------------------------------------------------------------------------------------------------------------------------------
if (isset($_REQUEST["email"])) {  // überprüft ob alle Parameter belegt sind
  $e_mail = ($_REQUEST["email"]);

  // Strukturprüfung
  if ((preg_match("/^(([a-zA-Z0-9_.\\-+])+@(([a-zA-Z0-9\\-])+\\.)+[a-zA-Z0-9]{2,4})|([ ])$/", trim(($e_mail)))) == 0) {
    ?>
    <script type="text/javascript">
        parent.document.getElementById("message").innerHTML = "<?php echo (('Bitte eine gültige Email-Adresse eingeben!')); ?>";
    </script>
    <?php
    return;                                  // Der vertikale Strich '|' bedeuted oder.
  }
} else {
 
    $e_mail = "";
}
$passwort = sha1($_passwort);


$querySQL = "call UserAddProc (" . $dbSyb->Quote($benutzer)        //=>>> SQL-Abfrage wird erstellt
        . "," . $dbSyb->Quote($passwort)
        . "," . $dbSyb->Quote($e_mail).")"
        ;

//$fp = fopen("userDS_add", "w");  // =>> Hier wird eine txt erzeugt, mit der man eine Variable bzw. eine SQL-Abfrage
//fputs($fp, $querySQL);             // auslesen kann. Die Variable / Abfrage muss in dieser Zeile, nach $fp, eingesetzt
//fclose($fp); 

$rs = $dbSyb->Execute($querySQL); //=>>> Abfrage wird an den Server �bermittelt / ausgef�hrt?
// Ausgabe initialisieren

$data = array();
$ergebnis = 0;
$userID = 0;

//$Name = "My Apps"; //senders name
//$email = "k-a-r-a-kartal@hotmail.de"; //senders e-mail adress
//$recipient = $e_mail; //recipient
//
//$subject = "Registrierung abschließen"; //subject
//$header = "From: ". $Name . " <" . $email . ">\r\n"; //optional headerfields
//$bestLink = "";

 

if (!$rs) {
    // keine Query hat nicht funtioniert

    print("Query 1: " . $dbSyb->ErrorMsg());

    return($data);
}
// das else MUSS nicht sein, da ein Fehler vorher Stoppt
else {

    $i = 0;

    while (!$rs->EOF) { // =>>> End OF File
        $ergebnis = $rs->fields['ergebnis'];
        $userID = $rs->fields['userID'];

        $i++;

        // den n�chsten Datensatz lesen
        $rs->MoveNext();
    }

    $rs->Close();
    if ($ergebnis == 1) {
        $bestLink = "http://" . $host . $uri . "/confirm.php?conLink=" . sha1($userID . "|" . $benutzer);
        $mail_body = "Hallo " . $benutzer . "!\r\n\r\nWillkommen bei KaraKartals Spiele Datenbank.\r\nUm die Registrierung abzuschließen, musst du nur noch den unteren Link anklicken.\r\n\r\n\r\n"
                . $bestLink; //mail body
        ?>
    <!-- Automatische Weiterleitung nach der Registrierung-->  
    <script type="text/javascript">
              
           var regSuc = function(){
                var domain = location.host; 
                parent.open('http://' + domain + '/kartal/login.php', '_self', false);
            };
            parent.document.getElementById("message").innerHTML = "<?php echo('Registrierung war erfolgreich. </br> Sie werden in wenigen Sekunden zur Login-Seite weitergeleitet.'); ?>";
            parent.document.getElementsByName("benutzer")[0].value = "";
            parent.document.getElementsByName("passwort")[0].value = "";
            parent.document.getElementsByName("passwort2")[0].value = "";
            parent.document.getElementsByName("email")[0].value = "";
            
            parent.setTimeout(regSuc,5000);
            
        </script>        
        <?php        
        
        $empfaenger = $e_mail; //Mailadresse Empfaenger
        $betreff = "";
        $mailtext = $mail_body;

        $absender = "KaraKartals Spiele Datenbank <aguila1419@googlemail.com>";

        $headers = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/plain; charset=UTF-8";
        $headers[] = "From: {$absender}";
// falls Bcc benötigt wird
        $headers[] = "Reply-To: {$absender}";
        $headers[] = "Subject: KaraKartals Spiele Datenbank Registrierung";
        $headers[] = "X-Mailer: PHP/" . phpversion();

        mail($empfaenger, $betreff, iconv("Windows-1252", "Windows-1252", $mailtext), implode("\r\n", $headers));
        return;
    } elseif ($ergebnis == -1) {
        ?>
        <script type="text/javascript">
            parent.document.getElementById("message").innerHTML = "<?php echo 'Dieser Benutzername existiert bereits!' ?>";
        </script>
        <?php
        return;
    } elseif ($ergebnis == -2) {
        ?>
        <script type="text/javascript">
            parent.document.getElementById("message").innerHTML = "<?php echo 'Es ist ein Fehler beim Anlegen des Benutzers aufgetreten' ?>";
        </script>
        <?php
        return;
    } elseif ($ergebnis == 0) {
        ?>
        <script type="text/javascript">
            parent.document.getElementById("message").innerHTML = "<?php echo 'Registrierung ist fehlgeschlagen' ?>";
        </script>
        <?php
        return;
    }
}
?>