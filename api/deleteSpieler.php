<?php



session_start();

require_once('adodb5/adodb.inc.php');

$ADODB_CACHE_DIR = 'C:/php/cache';
require_once('db_psw.php');


$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC; // Liefert ein assoziatives Array, das der geholten Zeile entspricht 

$ADODB_COUNTRECS = true;

$dbSyb = ADONewConnection("mysqli");

// DB-Abfragen NICHT cachen
$dbSyb->memCache = false;
$dbSyb->memCacheHost = array('localhost'); /// $db->memCacheHost = $ip1; will work too
$dbSyb->memCacheCompress = false; /// Use 'true' arbeitet unter Windows nicht
//$dsn = "'localhost','root',psw,'vitaldb'";
$dbSyb->Connect(link, user, psw, db); //=>>> Verbindungsaufbau mit der DB


$out = array();

if (!$dbSyb->IsConnected()) {

    $out{'response'}{'status'} = -1;
    $out{'response'}{'errors'} = array('vereinsname' => ($dbSyb->ErrorMsg()));

    print json_encode($out);

    return;
}

$dbSyb->debug = false;


if (isset($_REQUEST["spieler_id"])) {
    $spieler_id = $_REQUEST["spieler_id"];
    if ($spieler_id != "null" && $spieler_id != "") {
        if ((preg_match("/^[0-9]{1,11}?$/", trim($spieler_id))) == 0) {

            $out{'response'}{'status'} = -4;
            $out{'response'}{'errors'} = array('errors' => "Bitte die Spieler-ID prüfen!");

            print json_encode($out);
            return;
        }
    } else {
        $out{'response'}{'status'} = -1;
        $out{'response'}{'errors'} = array('errors' => "Spieler-ID fehlt!");

        print json_encode($out);

        return;
    }
} else {
    $out{'response'}{'status'} = -1;
    $out{'response'}{'errors'} = array('errors' => "Spieler-ID fehlt!");

    print json_encode($out);

    return;
}




$sqlQuery = "call deleteSpieler(". $spieler_id . ");";



// file_put_contents("deleteStadion.txt", $sqlQuery);

$rs = $dbSyb->Execute($sqlQuery);

$value = array();

if (!$rs) {
    $out{'response'}{'status'} = -4;
    $out{'response'}{'errors'} = array('errors' => ($dbSyb->ErrorMsg()));

    print json_encode($out);
    return;
}

If (isset($rs->fields{'ergebnis'})) {
    if ($rs->fields{'ergebnis'} == 1 ) {
        $i = 0;

while (!$rs->EOF) {

	$value{$i}{"ergebnis"} = $rs->fields{'ergebnis'};

    $i++;

    // den n�chsten Datensatz lesen
    $rs->MoveNext();
}

$rs->Close();

$out{'response'}{'status'} = 0;
$out{'response'}{'errors'} = array();
$out{'response'}{'data'} = $value;

print json_encode($out);
    }else if($rs->fields{'ergebnis'} == -1) {
        $out{'response'}{'status'} = -4;
        $out{'response'}{'errors'} = array('errors' => "Dem gewählten Spieler wurden bereits Bilder zugewiesen.</br> Bitte löschen Sie diese zuerst alle.</br>" . ($dbSyb->ErrorMsg()));
		$out{'response'}{'data'} = array('ergebnis' =>$rs->fields{'ergebnis'});
        print json_encode($out);
        return;
} else if($rs->fields{'ergebnis'} == -2 ) {
        $out{'response'}{'status'} = -4;
        $out{'response'}{'errors'} = array('errors' => "Der gewählte Spieler ist bereits einem oder mehreren Spiele zugewiesen worden.</br> Bitte löschen sie den Spieler erst aus dem oder den Spielen.</br>" . ($dbSyb->ErrorMsg()));
		$out{'response'}{'data'} = array('ergebnis' =>$rs->fields{'ergebnis'});
        print json_encode($out);
        return;
}else if($rs->fields{'ergebnis'} == -3 ) {
        $out{'response'}{'status'} = -4;
        $out{'response'}{'errors'} = array('errors' => "Der Spieler kann erst gelöscht werden, wenn alle Einträge aus der Tabelle ehemalige Vereine entfernt wurden." . ($dbSyb->ErrorMsg()));
		$out{'response'}{'data'} = array('ergebnis' =>$rs->fields{'ergebnis'});
        print json_encode($out);
        return;
}else if($rs->fields{'ergebnis'} == -4 ) {
        $out{'response'}{'status'} = -4;
        $out{'response'}{'errors'} = array('errors' => "Der gewählte Spieler ist bereits einem oder mehreren Vereinen zugewiesen worden.</br> Bitte löschen sie den Spieler erst aus dem oder den Vereinen.</br>" . ($dbSyb->ErrorMsg()));
		$out{'response'}{'data'} = array('ergebnis' =>$rs->fields{'ergebnis'});
        print json_encode($out);
        return;
}else{
        $out{'response'}{'status'} = -4;
        $out{'response'}{'errors'} = array('errors' => "Der gewählte Spieler konnte nicht gelöscht werden! </br>" . ($dbSyb->ErrorMsg()));
		$out{'response'}{'data'} = array('ergebnis' =>$rs->fields{'ergebnis'});
        print json_encode($out);
        return;
    }
}
else {
    $out{'response'}{'status'} = -4;
    $out{'response'}{'errors'} = array('errors' => "Keine Ergebnis-Rückmeldung erhalten. Löschvorgang evtl. nicht erfolgreich. </br>" . ($dbSyb->ErrorMsg()));
	$out{'response'}{'data'} = array('ergebnis' =>$rs->fields{'ergebnis'});
    print json_encode($out);
    return;
}




