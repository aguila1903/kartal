<?php

error_reporting(0);
session_start();
require_once('adodb5/adodb.inc.php');
include 'db_psw.php';
include("../resizer.php");
header('Cache-Control: no-store, no-cache, must-revalidate');
$host = (htmlspecialchars($_SERVER["HTTP_HOST"]));
$uri = rtrim(dirname(htmlspecialchars($_SERVER["PHP_SELF"])), "/\\");

if (isset($_SESSION["login"]) && $_SESSION["login"] == login && $_SESSION["admin"] == admin) {

    /*     * *****************************************************************************
      System: infotool - SVK-Versaende
      Funktion: Versandfehler anzeigen
      Autor: jra
      Datum: 04.12.2012

      Zusatzhinweise:

      Änderungen:

     * ***************************************************************************** */


    $out = array();
    $data = array();

    function sonderzeichen($string) {
        $search = array("Ä", "Ö", "Ü", "ä", "ö", "ü", "ß", "´");
        $replace = array("Ae", "Oe", "Ue", "ae", "oe", "ue", "ss", "");
        return str_replace($search, $replace, $string);
    }

    if (isset($_REQUEST["spieler"])) {
        $spieler = $_REQUEST["spieler"];
        $spieler = sonderzeichen($spieler);

        $spieler = str_replace("-", "", strtolower($spieler));
    } else {
        $out{'response'}{'status'} = -1;
        $out{'response'}{'errors'} = array('errors' => "Spieler fehlt!");
        $out{'response'}{'data'} = "1900.01.01";
        print json_encode($out);

        return;
    }



//    file_put_contents("spielerDatenScan.txt", str_replace("ü", "ue",utf8_decode($_REQUEST["spieler"])));
    $_url = "http://www.fussballdaten.de/spieler/" . trim(str_replace(" ", "", $spieler));



    $_buffer = implode('', file($_url));

    $suchmuster = "|Geboren(.*)+[0-9.]{10}|Us";
    $suchmuster2 = "/(Angriff|Mittelfeld|Abwehr|Torwart)/";
    $suchmuster3 = "|Nationalit(.*)+[A-Z]{1,3}+|Us";


    preg_match_all($suchmuster, utf8_decode($_buffer), $treffer, PREG_OFFSET_CAPTURE);
    preg_match_all($suchmuster2, utf8_decode($_buffer), $treffer2, PREG_OFFSET_CAPTURE);
    preg_match_all($suchmuster3, utf8_decode($_buffer), $treffer3, PREG_OFFSET_CAPTURE);





    $datum = trim(str_replace("Geboren:", "", $treffer[0][0][0]));
    $datum = str_replace("\n", "", $datum);
    $datum = str_replace("</td>", "", $datum);
    $datum = str_replace("<td>", "", $datum);

    $position = trim($treffer2[0][0][0]);

    if ($position == "Angriff") {
        $position = "an";
    } elseif ($position == "Abwehr") {
        $position = "aw";
    } elseif ($position == "Mittelfeld") {
        $position = "mi";
    } elseif ($position == "Torwart") {
        $position = "tw";
    } else {
        $position = "";
    }



    $nat = $treffer3[0][0][0];
    $nat = trim(str_replace("<td>", "", $nat));
    $nat = str_replace("\n", "", $nat);
    $nat = str_replace("</td>", "", $nat);
    $nat = str_replace("<td>", "", $nat);
    $nat = utf8_encode($nat);



    $data{"datum"} = $datum;
    $data{"position"} = $position;
    $data{"nat"} = htmlentities($nat);



    $out{'response'}{'status'} = 0;
    $out{'response'}{'errors'} = array();
    $out{'response'}{'data'} = $data;

    print json_encode($out);
} else {
    header("Location: http://$host/mundial/noadmin.php");
}
?>