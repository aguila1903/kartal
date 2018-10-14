
USE `mundialdb`;
ALTER TABLE `mundialdb`.`laender` 
ADD COLUMN `latitude` DECIMAL(8,4) NULL AFTER `codeMap`,
ADD COLUMN `longitude` DECIMAL(8,4) NULL AFTER `latitude`;


