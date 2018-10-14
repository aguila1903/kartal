USE `mundialdb`;
DROP procedure IF EXISTS `deleteStadBildFrontCover`;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `deleteStadBildFrontCover`(`Var_stadion_id` INT, `Var_media_id` INT)
root:BEGIN

Declare Var_anzahl int;

Start Transaction;
SET SQL_SAFE_UPDATES=0;
update media set art = 'ga' where ref = 'st' and art = 'fr' and id = Var_stadion_id and media_id = Var_media_id;

set Var_anzahl = ROW_COUNT();

if Var_anzahl != 1
Then 
Rollback;
SELECT Var_anzahl AS ergebnis;
Leave root;
end If;

SELECT Var_anzahl AS ergebnis;
Commit;

END$$

DELIMITER ;


DROP procedure IF EXISTS `deleteVereinsBildFrontCover`;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `deleteVereinsBildFrontCover`(`Var_id` INT, `Var_media_id` INT)
root:BEGIN

Declare Var_anzahl int;

Start Transaction;
SET SQL_SAFE_UPDATES=0;
update media set art = 'ga' where ref = 'vn' and art = 'fr' and id = Var_id and media_id = Var_media_id;

set Var_anzahl = ROW_COUNT();

if Var_anzahl != 1
Then 
Rollback;
SELECT Var_anzahl AS ergebnis;
Leave root;
end If;

SELECT Var_anzahl AS ergebnis;
Commit;

END$$

DELIMITER ;

