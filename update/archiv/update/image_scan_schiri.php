<?php

error_reporting(E_ERROR | E_PARSE);
// Script written by Adam Khoury for the following video exercise: // http://www.youtube.com/watch?v=7fTsf80RJ5w 
require_once('adodb5/adodb.inc.php');
include 'db_psw.php';
include("resizer.php");

function sonderzeichen($string) {
    $search = array("Ä", "Ö", "Ü", "ä", "ö", "ü", "ß", "´", " ", ".");
    $replace = array("Ae", "Oe", "Ue", "ae", "oe", "ue", "ss", "", "-", "");
    return str_replace($search, $replace, $string);
}

if (isset($_REQUEST["schiri"])) {
    $schiri = trim($_REQUEST["schiri"]);
    $schiri = sonderzeichen($schiri);
} else {
    $out{'response'}{'status'} = -1;
    $out{'response'}{'errors'} = array('errors' => "Spieler2 fehlt!");
    print json_encode($out);

    return;
}

if (isset($_REQUEST["schiri_id"])) {
    if ($_REQUEST["schiri_id"] != "null" && $_REQUEST["schiri_id"] != "") {
        $schiri_id = $_REQUEST["schiri_id"];
    } else {
        $out{'response'}{'status'} = -1;
        $out{'response'}{'errors'} = array('errors' => "Trainer-ID fehlt!");
        print json_encode($out);
    }
} else {
    $out{'response'}{'status'} = -1;
    $out{'response'}{'errors'} = array('errors' => "Trainer-ID fehlt!");
    print json_encode($out);

    return;
}

function get_image($url, $object, $name, $ref, $bild_art, $id) {

    $out = array();
    $data = array();

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
        $out{'response'}{'status'} = -4;
        $out{'response'}{'errors'} = array('Datenbank-Verbindung konnte nicht hergestellt werden!');
        print json_encode($out);
        return;
    }

    $dbSyb->debug = false;


    $host = $url;
    $base_url = parse_url($url);
    $base_url = $base_url['scheme'] . '://' . $base_url['host'] . '/';
    if (!(@$filestring = file_get_contents($host))) {
        echo 'ERROR: URL NOT VALID OR OFFLINE';
        return null;
    }
    preg_match_all('/<img[^>]+>/is', $filestring, $imgs);

// $imgs = array_unique($imgs[0]);
// foreach ($imgs as $img) {
    // echo '<br/>', htmlspecialchars($img),'<br/>';
// }

    $path1 = getcwd() . "\images\\";
    $path2 = getcwd() . "\images\media\\";
    $path3 = getcwd() . "\images\media\\thumbnails\\";
    $path4 = getcwd() . "\images\media\cover\\";

    if (is_dir($path1) != 1) {
        mkdir($path1, 0777, true);
        chmod($path1, 0777);
    }

    if (is_dir($path2) != 1) {
        mkdir($path2, 0777, true);
        chmod($path2, 0777);
    }

    if (is_dir($path3) != 1) {
        mkdir($path3);
    }
    if (is_dir($path4) != 1) {
        mkdir($path4);
    }

    $spielerImg = array();
    $spielerImg[0] == "";
// print_r($imgs[0]);
// return;
// echo count($imgs[0]); // 68
    for ($i = 0; $i < count($imgs[0]); $i++) {

        if (preg_match("/spieler\/gross/i", $imgs[0][$i])) {
            $spielerImg[0] = $imgs[0][$i];
        }

        // echo $imgs[0][$i];
    }

    if ($spielerImg[0] == "") {
        $data{"pictureName"} = "";
        $out{'response'}{'status'} = 0;
        $out{'response'}{'errors'} = array();
        $out{'response'}{'data'} = $data;

        print json_encode($out);

        return;
    }
//    print($spielerImg[0]); // Für die Ansicht im Testscript zuständig. wird [0] weggelassen, werden alle verfügbaren Bilder angezeigt. Ansonsten wird das erste Bild angezeigt
// return;
    $suchmuster = "/((s.weltsport){1}.*(jpg\"){1})/Us"; // Muss je nach Seite, von der das Bild runtergeladen wird, angepasst werden.
// preg_match_all('/upload.[^>]+"/is',$imgs[0],$imgUrl);
    preg_match_all($suchmuster, $spielerImg[0], $imgUrl); //  Positons für den Download des Bildes zuständig. Bei Wikipedia meistens zwischen [0] oder [1]
// print_r($imgUrl[0][0]);
// return;
    $contents = file_get_contents('http://' . str_replace('"', '', $imgUrl[0][0]));
    setlocale(LC_TIME, 'de_DE');


    $savename = utf8_decode($ref."_".$id . "_" . $name . ".jpg");

    $target = $path2 . $savename;
//    file_put_contents("image_scan.txt", $path1);
    $savefile = fopen($target, "w");
    fwrite($savefile, $contents);
    fclose($savefile);

    /*
     * ************** Front-Thumbnail Upload ********************************
     */

    list($wo, $ho) = getimagesize($target);
    $new = $path3 . $savename; // Pfad für den Thumbnail
    $type = "image/jpeg";

    if ($wo < $ho) {
        $w = ($wo / $ho) * 203;
        $h = 203;
    } else {
        $w = 175;
        $h = ($ho / $wo) * 175;
    }

    resize($target, $new, $w, $h, $type); // Funktion in der reseizer.php wird ausgeführt

    /*
     * ************** Front-Cover Upload ********************************
     */

    if ($bild_art == 'fr') {
        list($wo, $ho) = getimagesize($target);
        $new = $path4 . $savename; // Pfad für den Cover
        $type = "image/jpeg";

        if ($wo < $ho) {
            $w = ($wo / $ho) * 390;
            $h = 390;
        } else {
            $w = 310;
            $h = ($ho / $wo) * 310;
        }
        resize($target, $new, $w, $h, $type); // Funktion in der reseizer.php wird ausgeführt		
    }
    if (file_exists($target)) { // prüft ob ein Bild runtergeladen wurde. Wenn ja kann fortgefahren werden.
        if (filesize($target) > 0) {// prüft ob die Datei auch beschrieben wurde. Wenn ja, kann fortgefahren werden.
            $querySQL = " insert into media (ref, dateiname, id, art) Values(" . $dbSyb->Quote($ref)
                    . ", " . $dbSyb->Quote($savename)
                    . ", " . $id
                    . ", " . $dbSyb->Quote($bild_art) . ")";
            // Select ROW_COUNT() as ergebnis;
            // file_put_contents("bild_upload.txt", $querySQL, FILE_APPEND);

            $rs = $dbSyb->Execute($querySQL);

            if (!$rs) {
                $out{'response'}{'status'} = -1;
                $out{'response'}{'errors'} = array('errors' => utf8_encode($dbSyb->ErrorMsg()));

                print json_encode($out);

                return;
            }
        } else { // Datei wurde nicht beschrieben und muss wieder gelöscht werden.
            unlink($target);
            $data{"pictureName"} = "";
            $out{'response'}{'status'} = 0;
            $out{'response'}{'errors'} = array();
            $out{'response'}{'data'} = $data;

            print json_encode($out);

            return;
        }
    } else {
        $data{"pictureName"} = "";
        $out{'response'}{'status'} = 0;
        $out{'response'}{'errors'} = array();
        $out{'response'}{'data'} = $data;

        print json_encode($out);

        return;
    }

    $data{"pictureName"} = utf8_encode($savename);


    $out{'response'}{'status'} = 0;
    $out{'response'}{'errors'} = array();
    $out{'response'}{'data'} = $data;

    print json_encode($out);
}

get_image("http://www.weltfussball.de/schiedsrichter_profil/$schiri/", "spieler", $schiri, "sr", "fr", $schiri_id);

?>