<?php

//error_reporting(E_ALL);
session_start();

require_once('adodb5/adodb.inc.php');
require_once('fpdf/fpdf.php');
include 'db_psw.php';
header('Cache-Control: no-store, no-cache, must-revalidate');
$host = (htmlspecialchars($_SERVER["HTTP_HOST"]));
$uri = rtrim(dirname(htmlspecialchars($_SERVER["PHP_SELF"])), "/\\");


/* * *****************************************************************************
  System: infotool - SVK-Versaende
  Funktion: Versandfehler anzeigen
  Autor: jra
  Datum: 04.12.2012

  Zusatzhinweise:

  �nderungen:

 * ***************************************************************************** */



$ADODB_CACHE_DIR = 'C:/php/cache';


$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC; // Liefert ein assoziatives Array, das der geholten Zeile entspricht 

$ADODB_COUNTRECS = true;

$dbSyb = ADONewConnection("mysql");

// DB-Abfragen NICHT cachen
$dbSyb->memCache = false;
$dbSyb->memCacheHost = array('localhost'); /// $db->memCacheHost = $ip1; will work too
$dbSyb->memCacheCompress = false; /// Use 'true' arbeitet unter Windows nicht
//$dsn = "'localhost','root',psw,'vitaldb'";
$dbSyb->Connect('localhost', 'root', psw, db); //=>>> Verbindungsaufbau mit der DB


if (!$dbSyb->IsConnected()) {


    print ("Anmeldung: " . $dbSyb->ErrorMsg());

    return ($data);
}

$dbSyb->debug = false;
$out = array();


// $Where = " Where land != 'nb' ";

if (isset($_REQUEST["spiel_id"])) {
    $spiel_id = $_REQUEST["spiel_id"];
    if ($spiel_id != "null" && $spiel_id != "") {
        if ((preg_match("/^[0-9]{1,11}?$/", trim($spiel_id))) == 0) {

            $out{'response'}{'status'} = -4;
            $out{'response'}{'errors'} = array('spiel_id' => "Bitte die Spiel-ID prüfen! " . $spiel_id);

            print json_encode($out);
            return;
        }
    } else {
        $out{'response'}{'status'} = -1;
        $out{'response'}{'errors'} = array('spiel_id' => "Spiel-ID fehlt!");

        print json_encode($out);

        return;
    }
} else {
    $out{'response'}{'status'} = -1;
    $out{'response'}{'errors'} = array('spiel_id' => "Spiel-ID fehlt!");

    print json_encode($out);

    return;
}


$querySQL = "SELECT Distinct "
        . "	sp.spiel_id "
        . ", sp.zusch_anzahl "
        . ", sp.gaestefans "
        . ", sp.bes_vork "
        . ", sp.sp_bericht "
        . ", sp.schiri_id"
        . ", sp.ausverkauft"
        . ", s.name "
        . ", DATE_FORMAT(sp.sp_datum,GET_FORMAT(DATE,'EUR')) as sp_datum "
        . ", case weekday(sp.sp_datum) When 0 Then 'Montag' When 1 Then 'Dienstag' When 2 Then 'Mittwoch' When 3 Then 'Donnerstag' When 4 Then 'Freitag' When 5 Then 'Samstag' When 6 Then 'Sonntag' End as wochentag "
        . ", sp.ort as ort_id "
        . ", o.ort "
        . ", l.de as land "
        . ", sp.land as code "
        . ", sp.liga_id "
        . ", w.liga_bez as wettbewerb "
        . ", w.zusatz "
        . ", sp.zeit "
        . ", ergebnis "
        . ", sp.verein_id_h "
        . ", sp.verein_id_a "
        . ", va.gaengiger_name AS verein_a "
        . ", vh.gaengiger_name AS verein_h "
        . ", sp.stadion_id "
        . ", sl.stadionname "
        . ", sl.kapazitaet "
        . ", sl.anschrift "
        . ", sp.trainer_id_h "
        . ", sp.trainer_id_a "
        . ", s.name as schiri "
        . ", (select vs.gaengiger_name from vereine vs left join schiris ss on vs.verein_id = ss.verein_id where ss.schiri_id = sp.schiri_id) as schiri_verein "
        . ", ta.name as trainer_a "
        . ", th.name as trainer_h "
        . ", sp.stadion_id_alt "
        . ", sp.erg_halb "
        . ", sp.erg_elfer "
        . ", sp.sprit "
        . ", sp.sprit_anteilig "
        . ", sp.bahn "
        . ", sp.souvenir "
        . ", sp.taxi "
        . ", sp.handy "
        . ", sp.flieger "
        . ", sp.schiff "
        . ", sp.uebernachtung "
        . ", sp.verpflegung "
        . ", sp.sonstige "
        . ", sp.eintrittskarte "
        . ", sp.sonstige+sp.bahn+sp.eintrittskarte+sp.flieger+sp.uebernachtung+sp.verpflegung+sp.sprit as ges_kosten "
        . ", sp.erg_zusatz "
        . ", sn.name_vor_ae as stadionname_alt "
        . ", sp.wettbewerb_zusatz "
        . ", sp.nr as anzahl "
        . "  From sp_besuche sp left join laender l on sp.land = l.code "
        . " left join schiris s on sp.schiri_id = s.schiri_id "
        . " left join ligen w on w.liga_id = sp.liga_id "
        . " left join orte o on sp.ort = o.ort_id "
        . " Left join vereine vh on vh.verein_id = sp.verein_id_h "
        . " Left join vereine va on va.verein_id = sp.verein_id_a"
        . " Left join trainer th on th.trainer_id = sp.trainer_id_h "
        . " Left join trainer ta on ta.trainer_id = sp.trainer_id_a"
        . " Left Join stadionliste sl on sl.stadion_id = sp.stadion_id "
        . " Left join stadionnamen sn on sp.stadion_id_alt = sn.lfd_nr"
        . " LEFT JOIN sp_spieler_spiel_tabelle ss on ss.spiel_id = sp.spiel_id "
        . " LEFT JOIN spieler spi on spi.spieler_id = ss.spieler_id "
        . " LEFT JOIN sp_begleiter_spiel_tabelle bs on bs.spiel_id = sp.spiel_id "
        . " LEFT Join begleiter b on b.begleiter_id = bs.begleiter_id "
        . " WHERE  sp.spiel_id = " . $spiel_id
;


//$fp = fopen("spielePDF_Daten.txt", "w");
//fputs($fp, $querySQL);
//fclose($fp);


$rs = $dbSyb->Execute($querySQL);


if (!$rs) {

    $out{'response'}{'status'} = -4;
    $out{'response'}{'errors'} = array('errors' => "Es ist ein Fehler aufgetreten.");

    print json_encode($out);
    return;
} else {
    $i = 0;
    // $ii = 1;
    while (!$rs->EOF) {
        $data{$i}{"spiel_id"} = trim($rs->fields{'spiel_id'});
        $data{$i}{"stadion_id_alt"} = trim($rs->fields{'stadion_id_alt'});
        $data{$i}{"stadionname_alt"} = mb_convert_encoding($rs->fields{'stadionname_alt'}, 'UTF-8', 'CP1252');
        $data{$i}{"stadion_id"} = trim($rs->fields{'stadion_id'});
        $data{$i}{"zeit"} = trim($rs->fields{'zeit'});
        $data{$i}{"trainer_id_a"} = trim($rs->fields{'trainer_id_a'});
        $data{$i}{"trainer_id_h"} = trim($rs->fields{'trainer_id_h'});
        $data{$i}{"schiri_verein"} = mb_convert_encoding($rs->fields{'schiri_verein'}, 'UTF-8', 'CP1252');
        $data{$i}{"wochentag"} = mb_convert_encoding($rs->fields{'wochentag'}, 'UTF-8', 'CP1252');
        $data{$i}{"wettbewerb_zusatz"} = mb_convert_encoding($rs->fields{'wettbewerb_zusatz'}, 'UTF-8', 'CP1252');
        $data{$i}{"anschrift"} = mb_convert_encoding($rs->fields{'anschrift'}, 'UTF-8', 'CP1252');
        $data{$i}{"trainer_a"} = mb_convert_encoding($rs->fields{'trainer_a'}, 'UTF-8', 'CP1252');
        $data{$i}{"trainer_h"} = mb_convert_encoding($rs->fields{'trainer_h'}, 'UTF-8', 'CP1252');
        $data{$i}{"zusch_anzahl"} = number_format(trim($rs->fields{'zusch_anzahl'}), 0, ',', '.');
        $data{$i}{"gaestefans"} = number_format(trim($rs->fields{'gaestefans'}), 0, ',', '.');
        // $data{$i}{"erg_a"} = trim($rs->fields{'erg_a'});
        // $data{$i}{"erg_h"} = trim($rs->fields{'erg_h'});
        $data{$i}{"verein_id_a"} = trim($rs->fields{'verein_id_a'});
        $data{$i}{"verein_id_h"} = trim($rs->fields{'verein_id_h'});
        $data{$i}{"paarung"} = mb_convert_encoding($rs->fields{'verein_h'}, 'UTF-8', 'CP1252') . ' - ' . mb_convert_encoding($rs->fields{'verein_a'}, 'UTF-8', 'CP1252');
        $data{$i}{"sp_datum"} = trim($rs->fields{'sp_datum'});
        $data{$i}{"schiri_id"} = trim($rs->fields{'schiri_id'});
        $data{$i}{"schiri"} = mb_convert_encoding($rs->fields{'schiri'}, 'UTF-8', 'CP1252');
        $data{$i}{"name"} = mb_convert_encoding($rs->fields{'name'}, 'UTF-8', 'CP1252');
        $data{$i}{"liga_id"} = trim($rs->fields{'liga_id'});
        $data{$i}{"ort_id"} = trim($rs->fields{'ort_id'});
        $data{$i}{"ort"} = mb_convert_encoding($rs->fields{'ort'}, 'UTF-8', 'CP1252');
        $data{$i}{"sp_bericht"} = mb_convert_encoding($rs->fields{'sp_bericht'}, 'UTF-8', 'CP1252');
        $data{$i}{"bes_vork"} = mb_convert_encoding($rs->fields{'bes_vork'}, 'UTF-8', 'CP1252');
        $data{$i}{"land"} = mb_convert_encoding($rs->fields{'land'}, 'UTF-8', 'CP1252');
        $data{$i}{"code"} = mb_convert_encoding($rs->fields{'code'}, 'UTF-8', 'CP1252');
        $data{$i}{"verein_a"} = mb_convert_encoding($rs->fields{'verein_a'}, 'UTF-8', 'CP1252');
        $data{$i}{"verein_h"} = mb_convert_encoding($rs->fields{'verein_h'}, 'UTF-8', 'CP1252');
        $data{$i}{"erg"} = trim($rs->fields{'ergebnis'});
        $data{$i}{"erg_elfer"} = trim($rs->fields{'erg_elfer'});
        $data{$i}{"erg_halb"} = trim($rs->fields{'erg_halb'});
        $data{$i}{"erg_zusatz"} = trim($rs->fields{'erg_zusatz'});
        $data{$i}{"sprit"} = number_format($rs->fields{'sprit'}, 2, ',', '.');
        $data{$i}{"bahn"} = number_format($rs->fields{'bahn'}, 2, ',', '.');
        $data{$i}{"flieger"} = number_format($rs->fields{'flieger'}, 2, ',', '.');
        $data{$i}{"verpflegung"} = number_format($rs->fields{'verpflegung'}, 2, ',', '.');
        $data{$i}{"uebernachtung"} = number_format($rs->fields{'uebernachtung'}, 2, ',', '.');
        $data{$i}{"sonstige"} = number_format($rs->fields{'sonstige'}, 2, ',', '.');
        $data{$i}{"ges_kosten"} = number_format($rs->fields{'ges_kosten'}, 2, ',', '.');
        $data{$i}{"eintrittskarte"} = number_format($rs->fields{'eintrittskarte'}, 2, ',', '.');
        $data{$i}{"sprit_anteilig"} = number_format($rs->fields{'sprit_anteilig'}, 2, ',', '.');
        $data{$i}{"souvenir"} = number_format($rs->fields{'souvenir'}, 2, ',', '.');
        $data{$i}{"taxi"} = number_format($rs->fields{'taxi'}, 2, ',', '.');
        $data{$i}{"handy"} = number_format($rs->fields{'handy'}, 2, ',', '.');
        $data{$i}{"schiff"} = number_format($rs->fields{'schiff'}, 2, ',', '.');
        $data{$i}{"ausverkauft"} = trim($rs->fields{'ausverkauft'});


        if (strlen(trim($rs->fields{'zusatz'})) > 0) {
            $data{$i}{"wettbewerb"} = mb_convert_encoding($rs->fields{'wettbewerb'}, 'UTF-8', 'CP1252') . " (" . mb_convert_encoding($rs->fields{'zusatz'}, 'UTF-8', 'CP1252') . ")";
        } else {
            $data{$i}{"wettbewerb"} = mb_convert_encoding($rs->fields{'wettbewerb'}, 'UTF-8', 'CP1252');
        }


        if (strlen(trim($rs->fields{'kapazitaet'})) == 0 || trim($rs->fields{'kapazitaet'}) == '' || trim($rs->fields{'kapazitaet'}) == 'NULL' || trim($rs->fields{'kapazitaet'}) == null) {

            if (strlen(trim($rs->fields{'stadionname_alt'})) == 0 || trim($rs->fields{'stadionname_alt'}) == '' || trim($rs->fields{'stadionname_alt'}) == 'NULL' || trim($rs->fields{'stadionname_alt'}) == null) {
                $data{$i}{"stadionname"} = mb_convert_encoding($rs->fields{'stadionname'}, 'UTF-8', 'CP1252');
            } else {
                $data{$i}{"stadionname"} = mb_convert_encoding($rs->fields{'stadionname'}, 'UTF-8', 'CP1252') . ' (' . mb_convert_encoding($rs->fields{'stadionname_alt'}, 'UTF-8', 'CP1252') . ")";
            }
        } else {
            if (strlen(trim($rs->fields{'stadionname_alt'})) == 0 || trim($rs->fields{'stadionname_alt'}) == '' || trim($rs->fields{'stadionname_alt'}) == 'NULL' || trim($rs->fields{'stadionname_alt'}) == null) {
                $data{$i}{"stadionname"} = mb_convert_encoding($rs->fields{'stadionname'}, 'UTF-8', 'CP1252') . ' (' . number_format(trim($rs->fields{'kapazitaet'}), 0, ',', '.') . ')';
            } else {
                $data{$i}{"stadionname"} = mb_convert_encoding($rs->fields{'stadionname'}, 'UTF-8', 'CP1252') . ' (' . number_format(trim($rs->fields{'kapazitaet'}), 0, ',', '.') . ') (' . mb_convert_encoding($rs->fields{'stadionname_alt'}, 'UTF-8', 'CP1252') . ")";
            }
        }

        if (strlen(trim($rs->fields{'erg_zusatz'})) > 0) {
            if (trim($rs->fields{'erg_zusatz'}) == "n. V.") {
                $data{$i}{"ergebnis"} = trim($rs->fields{'ergebnis'}) . ' (' . trim($rs->fields{'erg_halb'}) . ") " . trim($rs->fields{'erg_zusatz'});
            }
            if (trim($rs->fields{'erg_zusatz'}) == "i. E.") {
                $data{$i}{"ergebnis"} = trim($rs->fields{'ergebnis'}) . ' (' . trim($rs->fields{'erg_halb'}) . ") " . trim($rs->fields{'erg_elfer'}) . " " . trim($rs->fields{'erg_zusatz'});
            }
            if (trim($rs->fields{'erg_zusatz'}) == "nvUiE") {
                $data{$i}{"ergebnis"} = trim($rs->fields{'ergebnis'}) . ' (' . trim($rs->fields{'erg_halb'}) . ") n.V. " . trim($rs->fields{'erg_elfer'}) . " i. E.";
            }
        } else {
            $data{$i}{"ergebnis"} = trim($rs->fields{'ergebnis'}) . ' (' . trim($rs->fields{'erg_halb'}) . ")";
        }
        $data{$i}{"nummer"} = $rs->fields{'anzahl'};


        $i++;
        // $ii++;

        $rs->MoveNext();
    }

    $rs->Close();
}

/* * **********************************************************************************************************************************************************
 * ********************************************************** ENDE SPIELDATEN *********************************************************************************
 * ********************************************************************************************************************************************************** */



/* * **********************************************************************************************************************************************************
 * ********************************************************** ANFANG WAPPEN *********************************************************************************
 * ********************************************************************************************************************************************************** */


$querySQL_wappen = "select ifnull(dateiname,'no_image.jpg') as dateiname from media where id = " . $data{0}{"verein_id_h"} . " and ref = 'vn' and art = 'fr'"
        . " union "
        . "select ifnull(dateiname,'no_image.jpg') as dateiname from media where id = " . $data{0}{"verein_id_a"} . " and ref = 'vn' and art = 'fr';";

// $fp = fopen("spielePDF_Wappen.txt", "w"); 
// fputs($fp, $querySQL_wappen);             
// fclose($fp);

$rs_wp = $dbSyb->Execute($querySQL_wappen);

if (!$rs_wp) {
    $out{'response'}{'status'} = -4;
    $out{'response'}{'errors'} = array('verein_id' => utf8_encode($dbSyb->ErrorMsg()));

    print json_encode($out);
    return;
} else {
    $i = 0;

    while (!$rs_wp->EOF) {
        $data_wp{$i}{"dateiname"} = utf8_encode($rs_wp->fields{'dateiname'});

        $i++;

        // den n�chsten Datensatz lesen
        $rs_wp->MoveNext();
    }

    $rs_wp->Close();
}


/*
 * ****************************PDF**********************************************
 * =============================================================================
 */

$pdf = new FPDF();
$pdf->AliasNbPages();
$pdf->AddPage();

/*
 * **************************** WAPPEN **********************************************
 */
$wappenDir = str_replace("\ds", "", getcwd()) . "\\..\\api\\images\\media\\";
$pdf->Image($wappenDir . $data_wp{0}{"dateiname"}, 5, 5); // Wappen Heim-Mannschaft
$pdf->Image($wappenDir . $data_wp{1}{"dateiname"}, 175, 5); // Wappen Auswärts-Mannschaft
$ih = 25;
$iw = 35;
/*
 * **************************** ERGEBNIS **********************************************
 */
$ergebnis = utf8_decode($data{0}{"verein_h"}) . "  " . $data{0}{"ergebnis"} . "  " . utf8_decode($data{0}{"verein_a"});
//Anpassung der Schriftgrößen
if (strlen($ergebnis) >= 40) {
    $pdf->SetFont('Arial', 'B', 15);
}
if (strlen($ergebnis) >= 55) {
    $pdf->SetFont('Arial', 'B', 13);
}
if (strlen($ergebnis) >= 64) {
    $pdf->SetFont('Arial', 'B', 11);
}
if (strlen($ergebnis) <= 39) {
    $pdf->SetFont('Arial', 'B', 20);
}
$w = $pdf->GetStringWidth($ergebnis);
$pdf->SetXY((210 - $w) / 2, $ih);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell($w, 0, $ergebnis, 0, 0, 'C', false);

/*
 * **************************** SPIELDATEN **********************************************
 */
$datum = $data{0}{"sp_datum"};
$uhrzeit = $data{0}{"zeit"} . " Uhr";
$wochentag = $data{0}{"wochentag"};
$wettbewerb = utf8_decode($data{0}{"wettbewerb"} . " (" . $data{0}{"wettbewerb_zusatz"} . ")");
$zuschauer = $data{0}{"zusch_anzahl"} . " (" . $data{0}{"gaestefans"} . ")";
$stadion = "Spielstätte: " . $data{0}{"stadionname"};
$schiri = "Schiedsrichter: " . $data{0}{"schiri"};

$spielZeit = $wochentag . ", " . $datum . " " . $uhrzeit;

$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0, 50, 50);
$w = $pdf->GetStringWidth($spielZeit);
$pdf->SetXY((210 - $w) / 2, $ih - 10);
$pdf->Cell($w, 0, $spielZeit, 0, 0, 'C', false);


// Wettbewerb
$w = $pdf->GetStringWidth($wettbewerb);
$pdf->SetXY((210 - $w) / 2, $ih - 16);
$pdf->Cell($w, 0, $wettbewerb, 0, 0, 'C', false);
// Zuschauer
$pdf->SetFont('Arial', '', 13);
$w1 = 21;
$pdf->Text($w1, $ih + 20, "Zuschauer: " . $data{0}{"zusch_anzahl"});
//Gäste
$pdf->Text($w1, $ih + 25, utf8_decode("Gäste: " . $data{0}{"gaestefans"}));
//Stadion
$pdf->Text($w1, $ih + 33, utf8_decode($stadion));
//Schiri
$pdf->Text($w1, $ih + 43, utf8_decode($schiri));

//Logos
$pdf->Image("c:/xampp/htdocs/mundial/images/famfam/supporter.png", 5, $ih + 16);
$pdf->Image("c:/xampp/htdocs/mundial/images/famfam/stadium.png", 7, $ih + 29);
$pdf->Image("c:/xampp/htdocs/mundial/images/famfam/whistle.png", 7, $ih + 40);


/*
 * **************************** AUFSTELLUNG-HEIM-TEAM **********************************************
 */

$querySQL = "SELECT Distinct "
        . " concat(ifnull(s.vorname,''),' ',s.name) as name "
        . ", v.aw "
        . ", v.aw_minute "
        . "  From sp_spieler_spiel_tabelle v join spieler s on v.spieler_id = s.spieler_id	"
        . " and spiel_id = " . $data{0}{"spiel_id"} . " and v.status = 'sa' and v.status2 = 'h' "
        . "     order by v.lfd_nr";


// $fp = fopen("spielerSpielAddDS.txt", "w"); 
// fputs($fp, $querySQL);             
// fclose($fp); 


$rs = $dbSyb->Execute($querySQL);

if (!$rs) {

    $out{'response'}{'status'} = -4;
    $out{'response'}{'errors'} = array('errors' => "Es ist ein Fehler aufgetreten.");

    print json_encode($out);
    return;
}
$fontSize = 11;
$pdf->SetFont('Arial', 'B', $fontSize);
$i = 0;
$ii = 60;
$pdf->Text($w1, $ih + 55, utf8_decode($data{0}{"verein_h"}));
$pdf->SetFont('Arial', '', $fontSize - 2);
while (!$rs->EOF) {

    if (trim($rs->fields{'aw_minute'}) > 0) {
        $data{$i}{"name"} = mb_convert_encoding($rs->fields{'name'}, 'UTF-8', 'CP1252') . " (" . trim($rs->fields{'aw_minute'}) . ".)";
        $pdf->Text($w1, $ih + $ii, utf8_decode($data{$i}{"name"}));
        $pdf->Image("c:/xampp/htdocs/mundial/images/famfam/ic_wechsel_rot.png", $w1 - 5, $ih + $ii - 2.5);
    } else {
        $data{$i}{"name"} = mb_convert_encoding($rs->fields{'name'}, 'UTF-8', 'CP1252');
        $pdf->Text($w1, $ih + $ii, utf8_decode($data{$i}{"name"}));
    }

    $i++;
    $ii+=5;

    $rs->MoveNext();
}

$rs->Close();

/*
 * **************************** AUSWECHSELBANK-HEIM-TEAM **********************************************
 */

$querySQL = "SELECT Distinct "
        . " concat(ifnull(s.vorname,''),' ',s.name) as name "
        . ", v.aw "
        . ", v.aw_minute "
        . "  From sp_spieler_spiel_tabelle v join spieler s on v.spieler_id = s.spieler_id	"
        . " and spiel_id = " . $data{0}{"spiel_id"} . " and v.status = 'ew' and v.status2 = 'h' "
        . "     order by v.lfd_nr";


// $fp = fopen("spielerSpielAddDS.txt", "w"); 
// fputs($fp, $querySQL);             
// fclose($fp); 


$rs = $dbSyb->Execute($querySQL);

if (!$rs) {

    $out{'response'}{'status'} = -4;
    $out{'response'}{'errors'} = array('errors' => "Es ist ein Fehler aufgetreten.");

    print json_encode($out);
    return;
}
$pdf->SetFont('Arial', 'B', $fontSize);
$i = 0;
$ii = 125;
$pdf->Text($w1, $ih + 120, 'Bank');
$pdf->SetFont('Arial', '', $fontSize - 2);
while (!$rs->EOF) {

    if (trim($rs->fields{'aw_minute'}) > 0) {
        $data{$i}{"name"} = mb_convert_encoding($rs->fields{'name'}, 'UTF-8', 'CP1252') . " (" . trim($rs->fields{'aw_minute'}) . ".)";
        $pdf->Text($w1, $ih + $ii, utf8_decode($data{$i}{"name"}));
        $pdf->Image("c:/xampp/htdocs/mundial/images/famfam/ic_wechsel_gruen.png", $w1 - 5, $ih + $ii - 2.5);
    } else {
        $data{$i}{"name"} = mb_convert_encoding($rs->fields{'name'}, 'UTF-8', 'CP1252');
        $pdf->Text($w1, $ih + $ii, utf8_decode($data{$i}{"name"}));
    }

    $i++;
    $ii+=5;

    $rs->MoveNext();
}

$rs->Close();

/*
 * **************************** Trainer-HEIM-TEAM **********************************************
 */

$pdf->SetFont('Arial', 'B', $fontSize);
$i = 0;
$ii += 10;
$pdf->Text($w1, $ih + $ii - 5, utf8_decode('Trainer'));
$pdf->SetFont('Arial', '', $fontSize - 2);
$pdf->Text($w1, $ih + $ii, utf8_decode($data{0}{"trainer_h"}));

/*
 * **************************** Reisekosten **********************************************
 */
if ($data{0}{"ges_kosten"} !== '0,00') {
    $pdf->SetFont('Arial', 'B', $fontSize);
    $i = 0;
    $ii += 15;
    $pdf->Text($w1, $ih + $ii, utf8_decode('Reisekosten'));
    $pdf->SetFont('Arial', '', $fontSize - 2);
    $plus = 35;

    if ($data{0}{"sprit"} !== '0,00') {
        $ii += 5;
        $pdf->Text($w1, $ih + $ii, utf8_decode('Sprit (Eigenanteil):'));
//        $pdf->Text($w1 + $plus, $ih + $ii, utf8_decode($data{0}{"sprit"}));
        $pdf->SetXY($w1 + $plus, $ih + $ii - 1);
        $pdf->Cell(13, 0, utf8_decode($data{0}{"sprit"}), 0, 0, 'R', false);
    }
    if ($data{0}{"sprit_anteilig"} !== '0,00') {
        $ii += 5;
        $pdf->Text($w1, $ih + $ii, utf8_decode('Sprit (anteilig):'));
//        $pdf->Text($w1 + $plus, $ih + $ii, utf8_decode($data{0}{"sprit_anteilig"}));
        $pdf->SetXY($w1 + $plus, $ih + $ii - 1);
        $pdf->Cell(13, 0, utf8_decode($data{0}{"sprit_anteilig"}), 0, 0, 'R', false);
    }
    if ($data{0}{"bahn"} !== '0,00') {
        $ii += 5;
        $pdf->Text($w1, $ih + $ii, utf8_decode('Bahnticket:'));
//        $pdf->Text($w1 + $plus, $ih + $ii, utf8_decode($data{0}{"bahn"}));
        $pdf->SetXY($w1 + $plus, $ih + $ii - 1);
        $pdf->Cell(13, 0, utf8_decode($data{0}{"bahn"}), 0, 0, 'R', false);
    }
    if ($data{0}{"flieger"} !== '0,00') {
        $ii += 5;
        $pdf->Text($w1, $ih + $ii, utf8_decode('Flugticket:'));
//        $pdf->Text($w1 + $plus, $ih + $ii, utf8_decode($data{0}{"flieger"}));
        $pdf->SetXY($w1 + $plus, $ih + $ii - 1);
        $pdf->Cell(13, 0, utf8_decode($data{0}{"flieger"}), 0, 0, 'R', false);
    }
    if ($data{0}{"schiff"} !== '0,00') {
        $ii += 5;
        $pdf->Text($w1, $ih + $ii, utf8_decode('Schiffsfahrkarte:'));
//        $pdf->Text($w1 + $plus, $ih + $ii, utf8_decode($data{0}{"schiff"}));
        $pdf->SetXY($w1 + $plus, $ih + $ii - 1);
        $pdf->Cell(13, 0, utf8_decode($data{0}{"schiff"}), 0, 0, 'R', false);
    }
    if ($data{0}{"uebernachtung"} !== '0,00') {
        $ii += 5;
        $pdf->Text($w1, $ih + $ii, utf8_decode('Übernachtung:'));
//        $pdf->Text($w1 + $plus, $ih + $ii, utf8_decode($data{0}{"uebernachtung"}));
        $pdf->SetXY($w1 + $plus, $ih + $ii - 1);
        $pdf->Cell(13, 0, utf8_decode($data{0}{"uebernachtung"}), 0, 0, 'R', false);
    }
    if ($data{0}{"verpflegung"} !== '0,00') {
        $ii += 5;
        $pdf->Text($w1, $ih + $ii, utf8_decode('Speis und Trank:'));
//        $pdf->Text($w1 + $plus, $ih + $ii, utf8_decode($data{0}{"verpflegung"}));
        $pdf->SetXY($w1 + $plus, $ih + $ii - 1);
        $pdf->Cell(13, 0, utf8_decode($data{0}{"verpflegung"}), 0, 0, 'R', false);
    }
    if ($data{0}{"eintrittskarte"} !== '0,00') {
        $ii += 5;
        $pdf->Text($w1, $ih + $ii, utf8_decode('Eintrittskarte:'));
//        $pdf->Text($w1 + $plus, $ih + $ii, utf8_decode($data{0}{"eintrittskarte"}));
        $pdf->SetXY($w1 + $plus, $ih + $ii - 1);
        $pdf->Cell(13, 0, utf8_decode($data{0}{"eintrittskarte"}), 0, 0, 'R', false);
    }
    if ($data{0}{"taxi"} !== '0,00') {
        $ii += 5;
        $pdf->Text($w1, $ih + $ii, utf8_decode('Taxi:'));
//        $pdf->Text($w1 + $plus, $ih + $ii, utf8_decode($data{0}{"taxi"}));
        $pdf->SetXY($w1 + $plus, $ih + $ii - 1);
        $pdf->Cell(13, 0, utf8_decode($data{0}{"taxi"}), 0, 0, 'R', false);
    }
    if ($data{0}{"handy"} !== '0,00') {
        $ii += 5;
        $pdf->Text($w1, $ih + $ii, utf8_decode('Handy:'));
//        $pdf->Text($w1 + $plus, $ih + $ii, utf8_decode($data{0}{"handy"}));
        $pdf->SetXY($w1 + $plus, $ih + $ii - 1);
        $pdf->Cell(13, 0, utf8_decode($data{0}{"handy"}), 0, 0, 'R', false);
    }
    if ($data{0}{"souvenir"} !== '0,00') {
        $ii += 5;
        $pdf->Text($w1, $ih + $ii, utf8_decode('Souvenir/Devotionalien:'));
//        $pdf->Text($w1 + $plus, $ih + $ii, utf8_decode($data{0}{"souvenir"}));
        $pdf->SetXY($w1 + $plus, $ih + $ii - 1);
        $pdf->Cell(13, 0, utf8_decode($data{0}{"souvenir"}), 0, 0, 'R', false);
    }
    if ($data{0}{"sonstige"} !== '0,00') {
        $ii += 5;
        $pdf->Text($w1, $ih + $ii, utf8_decode('Sonstige:'));
//        $pdf->Text($w1 + $plus, $ih + $ii, utf8_decode($data{0}{"sonstige"}));
        $pdf->SetXY($w1 + $plus, $ih + $ii - 1);
        $pdf->Cell(13, 0, utf8_decode($data{0}{"sonstige"}), 0, 0, 'R', false);
    }
    $ii += 5;
    $pdf->SetFont('Arial', 'B', $fontSize - 1);
    $pdf->Text($w1, $ih + $ii, utf8_decode('Gesamtkosten:'));
//    $pdf->Text($w1 + $plus, $ih + $ii , utf8_decode($data{0}{"ges_kosten"}));

    $pdf->SetXY($w1 + $plus, $ih + $ii - 1);
    $pdf->Cell(13, 0, utf8_decode($data{0}{"ges_kosten"}), 0, 0, 'R', false);
}


/*
 * **************************** AUFSTELLUNG-GAST-TEAM **********************************************
 */

$querySQL = "SELECT Distinct "
        . " concat(ifnull(s.vorname,''),' ',s.name) as name "
        . ", v.aw "
        . ", v.aw_minute "
        . "  From sp_spieler_spiel_tabelle v join spieler s on v.spieler_id = s.spieler_id	"
        . " and spiel_id = " . $data{0}{"spiel_id"} . " and v.status = 'sa' and v.status2 = 'a' "
        . "     order by v.lfd_nr";


// $fp = fopen("spielerSpielAddDS.txt", "w"); 
// fputs($fp, $querySQL);             
// fclose($fp); 


$rs = $dbSyb->Execute($querySQL);

if (!$rs) {

    $out{'response'}{'status'} = -4;
    $out{'response'}{'errors'} = array('errors' => "Es ist ein Fehler aufgetreten.");

    print json_encode($out);
    return;
}
$pdf->SetFont('Arial', 'B', $fontSize);
$i = 0;
$ii = 60;
$w = 140;
$pdf->Text($w, $ih + 55, utf8_decode($data{0}{"verein_a"}));
$pdf->SetFont('Arial', '', $fontSize - 2);
while (!$rs->EOF) {

    if (trim($rs->fields{'aw_minute'}) > 0) {
        $data{$i}{"name"} = mb_convert_encoding($rs->fields{'name'}, 'UTF-8', 'CP1252') . " (" . trim($rs->fields{'aw_minute'}) . ".)";
        $pdf->Text($w, $ih + $ii, utf8_decode($data{$i}{"name"}));
        $pdf->Image("c:/xampp/htdocs/mundial/images/famfam/ic_wechsel_rot.png", $w - 5, $ih + $ii - 2.5);
    } else {
        $data{$i}{"name"} = mb_convert_encoding($rs->fields{'name'}, 'UTF-8', 'CP1252');
        $pdf->Text($w, $ih + $ii, utf8_decode($data{$i}{"name"}));
    }

    $i++;
    $ii+=5;

    $rs->MoveNext();
}

$rs->Close();

/*
 * **************************** AUSWECHSELBANK-GAST-TEAM **********************************************
 */

$querySQL = "SELECT Distinct "
        . " concat(ifnull(s.vorname,''),' ',s.name) as name "
        . ", v.aw "
        . ", v.aw_minute "
        . "  From sp_spieler_spiel_tabelle v join spieler s on v.spieler_id = s.spieler_id	"
        . " and spiel_id = " . $data{0}{"spiel_id"} . " and v.status = 'ew' and v.status2 = 'a' "
        . "     order by v.lfd_nr";


// $fp = fopen("spielerSpielAddDS.txt", "w"); 
// fputs($fp, $querySQL);             
// fclose($fp); 


$rs = $dbSyb->Execute($querySQL);

if (!$rs) {

    $out{'response'}{'status'} = -4;
    $out{'response'}{'errors'} = array('errors' => "Es ist ein Fehler aufgetreten.");

    print json_encode($out);
    return;
}
$pdf->SetFont('Arial', 'B', $fontSize);
$i = 0;
$ii = 125;
$w = 140;
$pdf->Text($w, $ih + 120, 'Bank');
$pdf->SetFont('Arial', '', $fontSize - 2);
while (!$rs->EOF) {

    if (trim($rs->fields{'aw_minute'}) > 0) {
        $data{$i}{"name"} = mb_convert_encoding($rs->fields{'name'}, 'UTF-8', 'CP1252') . " (" . trim($rs->fields{'aw_minute'}) . ".)";
        $pdf->Text($w, $ih + $ii, utf8_decode($data{$i}{"name"}));
        $pdf->Image("c:/xampp/htdocs/mundial/images/famfam/ic_wechsel_gruen.png", $w - 5, $ih + $ii - 2.5);
    } else {
        $data{$i}{"name"} = mb_convert_encoding($rs->fields{'name'}, 'UTF-8', 'CP1252');
        $pdf->Text($w, $ih + $ii, utf8_decode($data{$i}{"name"}));
    }

    $i++;
    $ii+=5;

    $rs->MoveNext();
}

$rs->Close();



/*
 * **************************** Trainer-Gast-TEAM **********************************************
 */

$pdf->SetFont('Arial', 'B', $fontSize);
$i = 0;
$ii += 10;
$pdf->Text($w, $ih + $ii - 5, utf8_decode('Trainer'));
$pdf->SetFont('Arial', '', $fontSize - 2);
$pdf->Text($w, $ih + $ii, utf8_decode($data{0}{"trainer_a"}));


/*
 * **************************** BEGLEITER **********************************************
 */

$querySQL = "SELECT Distinct "
        . " s.name "
        . ", s.spitzname "
        . "  From sp_begleiter_spiel_tabelle v Left join begleiter s on v.begleiter_id = s.begleiter_id	"
        . " Where v.spiel_id = " . $spiel_id
        . "     order by s.name";

$rs = $dbSyb->Execute($querySQL);

if (!$rs) {

    $out{'response'}{'status'} = -4;
    $out{'response'}{'errors'} = array('errors' => $dbSyb->ErrorMsg() . "</br>Es ist ein Fehler aufgetreten.");

    print json_encode($out);
    return;
}
$pdf->SetFont('Arial', 'B', $fontSize);
$i = 0;
$ii += 20;
$pdf->Text($w, $ih + $ii-5, utf8_decode('Begleiter'));
$pdf->SetFont('Arial', '', $fontSize - 2);

while (!$rs->EOF) {
    if (strlen(trim($rs->fields{'spitzname'})) > 0) {
        $data{$i}{"name"} = mb_convert_encoding($rs->fields{'name'}, 'UTF-8', 'CP1252') . " (" . mb_convert_encoding($rs->fields{'spitzname'}, 'UTF-8', 'CP1252') . ")";
        $pdf->Text($w, $ih + $ii, utf8_decode($data{$i}{"name"}));
    } else {
        $data{$i}{"name"} = mb_convert_encoding($rs->fields{'name'}, 'UTF-8', 'CP1252');
        $pdf->Text($w, $ih + $ii, utf8_decode($data{$i}{"name"}));
    }

    $i++;
    $ii+=5;

    $rs->MoveNext();
}

$rs->Close();


/*
 * **************************** TORSCHÜTZEN **********************************************
 */

$querySQL = "SELECT Distinct "
        . "	 sp.lfd_nr "
        . ", sp.spiel_id "
        . ", sp.spieler_id_h "
        . ", sp.spieler_id_a "
        . ", sp.elfer "
        . ", concat(ifnull(s.vorname,''),' ',s.name) as spieler_h "
        . ", concat(ifnull(ss.vorname,''),' ',ss.name) as spieler_a "
        . ", sp.sp_minute "
        . ", sp.besonderheit "
        . ", sp.team "
        . ", sp.spielstand "
        . "  From sp_tore_spiel_tabelle sp Left join spieler s on sp.spieler_id_h = s.spieler_id	"
        . "  Left join spieler ss on sp.spieler_id_a = ss.spieler_id	"
        . " Where sp.spiel_id = " . $spiel_id . " and elfer = 0"
        . "     order by sp.sp_minute ";

$rs = $dbSyb->Execute($querySQL);





if (!$rs) {

    $out{'response'}{'status'} = -4;
    $out{'response'}{'errors'} = array('errors' => "Es ist ein Fehler aufgetreten.");

    print json_encode($out);
    return;
}
$pdf->SetFont('Arial', 'B', $fontSize);
$i = 0;
$ii = 60;
$w = 76;
$pdf->Text($w + 10, $ih + 55, 'Tore');
$pdf->SetFont('Arial', '', $fontSize - 2);

while (!$rs->EOF) {

    if (trim($rs->fields{'team'}) == 'h') {
        if (strlen(trim($rs->fields{'besonderheit'})) > 0) {
            $data{$i}{"spielstand"} = trim($rs->fields{'spielstand'}) . "  " . mb_convert_encoding($rs->fields{'spieler_h'}, 'UTF-8', 'CP1252') . "  " . trim($rs->fields{'sp_minute'}) . ".  " . trim($rs->fields{'besonderheit'});
            $pdf->Text($w, $ih + $ii, utf8_decode($data{$i}{"spielstand"}));
        } else {
            $data{$i}{"spielstand"} = trim($rs->fields{'spielstand'}) . "  " . mb_convert_encoding($rs->fields{'spieler_h'}, 'UTF-8', 'CP1252') . "  " . trim($rs->fields{'sp_minute'}) . ".";
            $pdf->Text($w, $ih + $ii, utf8_decode($data{$i}{"spielstand"}));
        }
    }
    if (trim($rs->fields{'team'}) == 'a') {

        if (strlen(trim($rs->fields{'besonderheit'})) > 0) {
            $data{$i}{"spielstand_a"} = trim($rs->fields{'spielstand'}) . "  " . mb_convert_encoding($rs->fields{'spieler_a'}, 'UTF-8', 'CP1252') . "  " . trim($rs->fields{'sp_minute'}) . ".  " . trim($rs->fields{'besonderheit'});
            $pdf->Text($w, $ih + $ii, utf8_decode($data{$i}{"spielstand_a"}));
        } else {
            $data{$i}{"spielstand_a"} = trim($rs->fields{'spielstand'}) . "  " . mb_convert_encoding($rs->fields{'spieler_a'}, 'UTF-8', 'CP1252') . "  " . trim($rs->fields{'sp_minute'}) . ".";
            $pdf->Text($w, $ih + $ii, utf8_decode($data{$i}{"spielstand_a"}));
        }
    }

    $i++;
    $ii+=5;

    $rs->MoveNext();
}

$rs->Close();


/*
 * **************************** ELFMETERSCHIESSEN **********************************************
 */

$querySQL = "SELECT Distinct "
        . "	 sp.lfd_nr "
        . ", sp.spiel_id "
        . ", sp.spieler_id_h "
        . ", sp.spieler_id_a "
        . ", sp.elfer "
        // . ", s.name as spieler_h"
        . ", concat(ifnull(s.vorname,''),' ',s.name) as spieler_h "
        . ", concat(ifnull(ss.vorname,''),' ',ss.name) as spieler_a "
        // . ", ss.name as spieler_a "
        . ", sp.sp_minute "
        . ", sp.besonderheit "
        . ", sp.team "
        . ", sp.spielstand "
        . "  From sp_tore_spiel_tabelle sp Left join spieler s on sp.spieler_id_h = s.spieler_id	"
        . "  Left join spieler ss on sp.spieler_id_a = ss.spieler_id	"
        . " Where sp.spiel_id = " . $spiel_id . " and elfer in (1,2)"
        . "     order by sp.lfd_nr ";



$rs = $dbSyb->Execute($querySQL);


if (!$rs) {

    $out{'response'}{'status'} = -4;
    $out{'response'}{'errors'} = array('errors' => "Es ist ein Fehler aufgetreten.");

    print json_encode($out);
    return;
}

if (trim($data{0}{"erg_zusatz"}) == "i. E." || trim($data{0}{"erg_zusatz"}) == "nvUiE") {
    $pdf->SetFont('Arial', 'B', $fontSize);
    $i = 0;
    $ii += 10;
    $pdf->Text($w, $ih + $ii - 5, utf8_decode('Elfmeterschießen'));
    $pdf->SetFont('Arial', '', $fontSize - 2);

    while (!$rs->EOF) {

        if (trim($rs->fields{'team'}) == 'h') {

            $data{$i}{"spielstand"} = trim($rs->fields{'spielstand'}) . "  " . mb_convert_encoding($rs->fields{'spieler_h'}, 'UTF-8', 'CP1252');
            $pdf->Text($w, $ih + $ii, utf8_decode($data{$i}{"spielstand"}));
            if (trim($rs->fields{'elfer'} == 2)) {
                $pdf->Image("c:/xampp/htdocs/mundial/images/famfam/missed.png", $w - 5, $ih + $ii - 2.5);
            } else {
                $pdf->Image("c:/xampp/htdocs/mundial/images/famfam/scored.png", $w - 5, $ih + $ii - 2.5);
            }
        }

        if (trim($rs->fields{'team'}) == 'a') {

            $data{$i}{"spielstand_a"} = trim($rs->fields{'spielstand'}) . "  " . mb_convert_encoding($rs->fields{'spieler_a'}, 'UTF-8', 'CP1252');
            $pdf->Text($w, $ih + $ii, utf8_decode($data{$i}{"spielstand_a"}));
            if (trim($rs->fields{'elfer'} == 2)) {
                $pdf->Image("c:/xampp/htdocs/mundial/images/famfam/missed.png", $w - 5, $ih + $ii - 2.5);
            } else {
                $pdf->Image("c:/xampp/htdocs/mundial/images/famfam/scored.png", $w - 5, $ih + $ii - 2.5);
            }
        }

        $i++;
        $ii+=5;

        $rs->MoveNext();
    }

    $rs->Close();
}

//Seitenzahl
//$pdf->SetFont('Arial', '', 10);
////        $this->Cell(0, 10, 'Seite ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
//$pdf->Text(100, 292, 'Seite ' . $pdf->PageNo() . '/{nb}');
//$pdf->AddPage();
// PDF-Ausgabe
$pdf->Output("test.pdf", "I");
?>