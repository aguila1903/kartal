
USE `mundialdb`;
DROP procedure IF EXISTS `editSpieler`;

DELIMITER $$
USE `mundialdb`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `editSpieler`(
Var_spieler_id int, 
Var_name varchar(264),
Var_vorname varchar(264),
Var_land char(2),
Var_geb_datum date,
Var_position char(2),
Var_trikot_nr int,
Var_nati_spieler bit,
Var_status bit


)
root:BEGIN

Declare Var_anzahl int;


Start Transaction;

if Var_land not in (select code from laender)
then
Rollback;
SELECT 99 AS ergebnis;
 Leave root;
 End IF;
 
IF Var_status = 0
Then 
  Set Var_anzahl = (Select count(*) from spieler where concat(trim(vorname),' ',trim(name)) = concat(trim(Var_vorname),' ',trim(Var_name)));
    If Var_anzahl > 1 then
    SELECT 66 AS ergebnis;
    Leave root;
  End if; 
End if;
 
UPDATE spieler
SET 
    name = Var_name,
    vorname = Var_vorname ,
    land = Var_land,
    geb_datum = Var_geb_datum,
    position = Var_position,
    trikot_nr = Var_trikot_nr,
    nati_spieler = Var_nati_spieler
WHERE
    spieler_id = Var_spieler_id;

set Var_anzahl = ROW_COUNT();


 IF Var_anzahl = 0
 Then 
 Rollback;
SELECT Var_anzahl AS ergebnis;
 Leave root;
 End IF;
commit;
SELECT Var_anzahl AS ergebnis;

END$$

DELIMITER ;


UPDATE `mundialdb`.`laender` SET `code2`='CIV' WHERE `code`='CI';


INSERT INTO `mundialdb`.`versionshistorie` (`version`, `bugfix`) VALUES ('1.10', '- Fehler bei der Pruefung der doppelten Spieler behoben');
INSERT INTO `mundialdb`.`versionshistorie` (`version`, `bugfix`) VALUES ('1.10', '- Fehler bei der Sonderzeichen-Erkennung behoben');
INSERT INTO `mundialdb`.`versionshistorie` (`version`, `bugfix`) VALUES ('1.10', '- Medien-Upload: Dateinamen individualisiert');
INSERT INTO `mundialdb`.`versionshistorie` (`version`, `bugfix`) VALUES ('1.10', '- Automatisches Bild-Upload via Webscan für Spieler hinzugefuegt');
INSERT INTO `mundialdb`.`versionshistorie` (`version`, `bugfix`) VALUES ('1.10', '- Automatisches Bild-Upload via Webscan für Mannschaften hinzugefuegt');
INSERT INTO `mundialdb`.`versionshistorie` (`version`, `bugfix`) VALUES ('1.10', '- Automatisches Bild-Upload via Webscan für Stadien hinzugefuegt');
INSERT INTO `mundialdb`.`versionshistorie` (`version`, `bugfix`) VALUES ('1.10', '- Automatisches Bild-Upload via Webscan für Schiedsrichter hinzugefuegt');
INSERT INTO `mundialdb`.`versionshistorie` (`version`, `bugfix`) VALUES ('1.10', '- Automatisches Bild-Upload via Webscan für Trainer hinzugefuegt');




















