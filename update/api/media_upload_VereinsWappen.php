<?php

// Script written by Adam Khoury for the following video exercise: // http://www.youtube.com/watch?v=7fTsf80RJ5w 
require_once('adodb5/adodb.inc.php');
require_once('db_psw.php');
include("resizer.php");

$status = json_encode('stop');
$bild = json_encode('');


$ADODB_CACHE_DIR = 'C:/php/cache';


$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC; // Liefert ein assoziatives Array, das der geholten Zeile entspricht 

$ADODB_COUNTRECS = true;

$dbSyb = ADONewConnection("mysqli");

// DB-Abfragen NICHT cachen
$dbSyb->memCache = false;
$dbSyb->memCacheHost = array('localhost'); /// $db->memCacheHost = $ip1; will work too
$dbSyb->memCacheCompress = false; /// Use 'true' arbeitet unter Windows nicht
//$dsn = "'localhost','root',psw,'vitaldb'";
$dbSyb->Connect('localhost', 'root', psw, db); //=>>> Verbindungsaufbau mit der DB


if (!$dbSyb->IsConnected()) {
    $result = json_encode('Datenbank-Verbindung konnte nicht hergestellt werden!');

    echo "<script type=\"text/javascript\">if(window && window.parent && window.parent['{$_POST['uploadFormID']}'] && window.parent['{$_POST['uploadFormID']}'].submitDone) { window.parent['{$_POST['uploadFormID']}'].submitDone($result, $status, $bild); } </script>";

    return;
}

$dbSyb->debug = false;


if (isset($_REQUEST["ref"])) {
    if ($_REQUEST["ref"] != "null" && $_REQUEST["ref"] != "") {
        $ref = $_REQUEST["ref"];
    } else {
        $result = json_encode('Die Media-Referenz fehlt!');

        echo "<script type=\"text/javascript\">if(window && window.parent && window.parent['{$_POST['uploadFormID']}'] && window.parent['{$_POST['uploadFormID']}'].submitDone) { window.parent['{$_POST['uploadFormID']}'].submitDone($result, $status, $bild); } </script>";

        return;
    }
} else {
    $result = json_encode('Die Media-Referenz fehlt!');

    echo "<script type=\"text/javascript\">if(window && window.parent && window.parent['{$_POST['uploadFormID']}'] && window.parent['{$_POST['uploadFormID']}'].submitDone) { window.parent['{$_POST['uploadFormID']}'].submitDone($result, $status, $bild); } </script>";

    return;
}


if (isset($_REQUEST["bild_art"])) {
    if ($_REQUEST["bild_art"] != "null" && $_REQUEST["bild_art"] != "") {
        $bild_art = $_REQUEST["bild_art"];
    } else {
        $result = json_encode('Die Art des Bildes fehlt!');

        echo "<script type=\"text/javascript\">if(window && window.parent && window.parent['{$_POST['uploadFormID']}'] && window.parent['{$_POST['uploadFormID']}'].submitDone) { window.parent['{$_POST['uploadFormID']}'].submitDone($result, $status, $bild); } </script>";

        return;
    }
} else {
    $result = json_encode('Die Art des Bildes fehlt!');

    echo "<script type=\"text/javascript\">if(window && window.parent && window.parent['{$_POST['uploadFormID']}'] && window.parent['{$_POST['uploadFormID']}'].submitDone) { window.parent['{$_POST['uploadFormID']}'].submitDone($result, $status, $bild); } </script>";

    return;
}

if (isset($_REQUEST["stadion_id"])) {
    if ($_REQUEST["stadion_id"] != "null" && $_REQUEST["stadion_id"] != "") {
        $stadion_id = $_REQUEST["stadion_id"];
    } else {
        $result = json_encode('Die Stadion-ID fehlt!');

        echo "<script type=\"text/javascript\">if(window && window.parent && window.parent['{$_POST['uploadFormID']}'] && window.parent['{$_POST['uploadFormID']}'].submitDone) { window.parent['{$_POST['uploadFormID']}'].submitDone($result, $status, $bild); } </script>";

        return;
    }
} else {
    if (isset($_REQUEST["verein_id"])) {
        if ($_REQUEST["verein_id"] != "null" && $_REQUEST["verein_id"] != "") {
            $stadion_id = $_REQUEST["verein_id"];
        } else {
            $result = json_encode('Die Stadion-ID fehlt!');

            echo "<script type=\"text/javascript\">if(window && window.parent && window.parent['{$_POST['uploadFormID']}'] && window.parent['{$_POST['uploadFormID']}'].submitDone) { window.parent['{$_POST['uploadFormID']}'].submitDone($result, $status, $bild); } </script>";

            return;
        }
    } else {
        $result = json_encode('Die ID fehlt!');

        echo "<script type=\"text/javascript\">if(window && window.parent && window.parent['{$_POST['uploadFormID']}'] && window.parent['{$_POST['uploadFormID']}'].submitDone) { window.parent['{$_POST['uploadFormID']}'].submitDone($result, $status, $bild); } </script>";

        return;
    }
}


if (isset($_FILES['datei'])) {

    $name_array = $_FILES['datei']['name'];
    $tmp_name_array = $_FILES['datei']['tmp_name'];
    $type_array = $_FILES['datei']['type'];
    $size_array = $_FILES['datei']['size'];
    $error_array = $_FILES['datei']['error'];
} else {
    $result = json_encode('Die Bild-Datei Fehlt');

    echo "<script type=\"text/javascript\">if(window && window.parent && window.parent['{$_POST['uploadFormID']}'] && window.parent['{$_POST['uploadFormID']}'].submitDone) { window.parent['{$_POST['uploadFormID']}'].submitDone($result, $status, $bild); } </script>";

    return;
}
//for ($i = 0; $i < count($type_array); $i++) {
//    if (
//            ($type_array[$i] != "image/jpeg") && ($type_array[$i] != "image/jpg")
//    ) {
//
//        $result = json_encode('Bitte nur Grafiken mit dem Format jpg oder jpeg hochladen.');
//
//        echo "<script type=\"text/javascript\">if(window && window.parent && window.parent['{$_POST['uploadFormID']}'] && window.parent['{$_POST['uploadFormID']}'].submitDone) { window.parent['{$_POST['uploadFormID']}'].submitDone($result, $status, $bild); } </script>";
//
//        return;
//    }
//}



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

/*
 * ************ Bild-Art-Prüfung *****************************
 */
if ($bild_art == 'fr') {
    if (count($tmp_name_array) > 1) {
        $result = json_encode('Bei Front-Bildern kann nur eine Datei hochgeladen werden!');

        echo "<script type=\"text/javascript\">if(window && window.parent && window.parent['{$_POST['uploadFormID']}'] && window.parent['{$_POST['uploadFormID']}'].submitDone) { window.parent['{$_POST['uploadFormID']}'].submitDone($result, $status, $bild); } </script>";

        return;
    }
}

for ($i = 0; $i < count($tmp_name_array); $i++) {

    if (move_uploaded_file(utf8_decode($tmp_name_array[$i]), $path2 . utf8_decode($ref."_".$stadion_id . "_" . $name_array[$i]))) {
        $target = $path2 . utf8_decode($ref."_".$stadion_id . "_" . $name_array[$i]);

        /*
         * ************** Front-Thumbnail Upload ********************************
         */

        list($wo, $ho) = getimagesize($target);
        $new = $path3 . utf8_decode($ref."_".$stadion_id . "_" . $name_array[$i]); // Pfad für den Thumbnail
        $type = substr($type_array[$i],-3);

        if ($ref == "vn") { // Upload bei Vereinswappen
            if ($wo < $ho) {
                $w = ($wo / $ho) * 100;
                $h = 100;
            } else if ($wo == $ho) {
                $w = 100;
                $h = 100;
            } else if ($wo > $ho) {
                $w = 100;
                $h = ($ho / $wo) * 100;
            }
        } else { // Upload bei Stadion-Bildern
            if ($wo < $ho) {
                $w = ($wo / $ho) * 203;
                $h = 203;
            } else {
                $w = 175;
                $h = ($ho / $wo) * 175;
            }
        }

        resize($target, $new, $w, $h, $type); // Funktion in der reseizer.php wird ausgeführt

        /*
         * ************** Front-Cover Upload ********************************
         */

        if ($bild_art == 'fr') {
            list($wo, $ho) = getimagesize($target);
            $new = $path4 . utf8_decode($ref."_".$stadion_id . "_" . $name_array[$i]); // Pfad für den Cover
            substr($type_array[$i],-3);
            if ($ref == "vn") {
                // Nach der Websearch-Anpassung Auflösung verändert
                if ($wo < $ho) {
                    $w = ($wo / $ho) * 100;
                    $h = 100;
                } else if ($wo == $ho) {
                    $w = 100;
                    $h = 100;
                } else if ($wo > $ho) {
                    $w = 100;
                    $h = ($ho / $wo) * 100;
                }
//                    $w = ($wo / $ho) * 220;
//                    $h = 220;
//                } else if ($wo == $ho) {
//                    $w = 220;
//                    $h = 220;
//                } else if ($wo > $ho) {
//                    $w = 220;
//                    $h = ($ho / $wo) * 220;
//                }
                
            } else {// Upload beim Stadion-Cover
                if ($wo < $ho) {
                    $w = ($wo / $ho) * 390;
                    $h = 390;
                } else {
                    $w = 310;
                    $h = ($ho / $wo) * 310;
                }
            }
            resize($target, $new, $w, $h, $type); // Funktion in der reseizer.php wird ausgeführt		
        }

        $querySQL = " insert into media (ref, dateiname, id, art) Values(" . $dbSyb->Quote($ref)
                . ", " . $dbSyb->Quote(utf8_decode($ref."_".$stadion_id . "_" . $name_array[$i]))
                . ", " . $stadion_id
                . ", " . $dbSyb->Quote($bild_art) . ")";
        // Select ROW_COUNT() as ergebnis;
        // file_put_contents("bild_upload.txt", $querySQL, FILE_APPEND);

        $rs = $dbSyb->Execute($querySQL);

        if (!$rs) {
            $result = json_encode('Datenbank-Fehler beim Upload der Datei ' . $name_array[$i] . ' aufgetregen</br> SQL-Fehlermeldung: ' . $dbSyb->ErrorMsg());
            $status = json_encode("ok");
            $bild = json_encode(utf8_encode($name_array[$i]));
            echo "<script type=\"text/javascript\">if(window && window.parent && window.parent['{$_POST['uploadFormID']}'] && window.parent['{$_POST['uploadFormID']}'].submitDone) { window.parent['{$_POST['uploadFormID']}'].submitDone($result, $status, $bild); } </script>";
        } else {
            if ($i + 1 == 1) {
                $result = json_encode($i + 1 . ' Bild erfolgreich hochgeladen!</br>');
            } else {
                $result = json_encode($i + 1 . ' Bilder erfolgreich hochgeladen!</br>');
            }
            $status = json_encode("ok");
            $bild = json_encode(utf8_encode($name_array[$i]));
            echo "<script type=\"text/javascript\">if(window && window.parent && window.parent['{$_POST['uploadFormID']}'] && window.parent['{$_POST['uploadFormID']}'].submitDone) { window.parent['{$_POST['uploadFormID']}'].submitDone($result, $status, $bild); } </script>";
        }
    } else { //echo 'Datei Upload hat versagt.'; 
        $result = json_encode("Fehler beim verschieben der Datei " . $name_array[$i]);
        $status = json_encode("ok");
        $bild = json_encode(utf8_encode($name_array[$i]));
        echo "<script type=\"text/javascript\">if(window && window.parent && window.parent['{$_POST['uploadFormID']}'] && window.parent['{$_POST['uploadFormID']}'].submitDone) { window.parent['{$_POST['uploadFormID']}'].submitDone($result, $status, $bild); } </script>";
    }
}
?>