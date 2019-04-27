<?php
session_start();

require_once('adodb5/adodb.inc.php');
require_once('db_psw.php');
header("Cache-Control: no-cache, must-revalidate");
$host = (htmlspecialchars($_SERVER["HTTP_HOST"]));
$uri = rtrim(dirname(htmlspecialchars($_SERVER["PHP_SELF"])), "/\\");

if (isset($_SESSION["login"]) && $_SESSION["login"] == login && $_SESSION["admin"] == admin) {





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


if (!$dbSyb->IsConnected()) {


    print ("Anmeldung: " . $dbSyb->ErrorMsg());

    $data = array();

    return ($data);
}

$dbSyb->debug = false;
$out = array();

if (isset($_REQUEST["id"])) {
    $id = $_REQUEST["id"];
    if ($id != "null" && $id != "") {
        if ((preg_match("/^[0-9]{1,11}?$/", trim($id))) == 0) {

            $out{'response'}{'status'} = -4;
            $out{'response'}{'errors'} = array('id' => "Bitte die ID prüfen!");

            print json_encode($out);
            return;
        }
    } else {
        $out{'response'}{'status'} = -1;
        $out{'response'}{'errors'} = array('id' => "ID fehlt!");

        print json_encode($out);

        return;
    }
} else {
    $out{'response'}{'status'} = -1;
    $out{'response'}{'errors'} = array('id' => "ID fehlt!");

    print json_encode($out);

    return;
}

if (isset($_REQUEST["ref"])) {
    $ref = $_REQUEST["ref"];
    
} else {
   $ref = "st";
}

// String mit DB-Abfrage f�r den Rest


$querySQL = "select dateiname, id, media_id from media where id = ". $id. " and ref = ".$dbSyb->Quote($ref)." and art = 'ga';";



$rs = $dbSyb->Execute($querySQL);


$data = array();

if (!$rs) {
    $out{'response'}{'status'} = -4;
    $out{'response'}{'errors'} = array('lfd_nr' => ($dbSyb->ErrorMsg()));

    print json_encode($out);
    return;
}
else {
    $i = 0;

    while (!$rs->EOF) {
        $data{$i}{"id"} = $rs->fields{'id'};
        $data{$i}{"media_id"} = $rs->fields{'media_id'};
        $data{$i}{"dateiname"} = ($rs->fields{'dateiname'});

        $i++;

        // den n�chsten Datensatz lesen
        $rs->MoveNext();
    }
       
    $rs->Close();
    
    $out{'response'}{'status'} = 0;
    $out{'response'}{'errors'} = array();
    $out{'response'}{'data'} = $data;

    print json_encode($out);  

}

} else {
    header("Location: http://$host/kartal/noadmin.php");
}
?>