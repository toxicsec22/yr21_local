<?php

$sqlcopy='
DELIMITER $$  '.PHP_EOL.PHP_EOL.'

CREATE PROCEDURE `copy_schema`(
    IN in_oldDB varchar(256),
    IN in_newDB varchar(256),
    IN in_copyData int(1)
)
BEGIN

DECLARE v_finished INTEGER DEFAULT 0;
DECLARE v_tname varchar(100) DEFAULT "";

DECLARE curTable CURSOR FOR 
SELECT `table_name`
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = in_oldDB
and TABLE_TYPE="BASE TABLE"
;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_finished = 1;

set @result=NULL;
';

# Create new schema if it doesn't exist
$sqlcopy.='SET @sql = CONCAT("CREATE SCHEMA IF NOT EXISTS ",in_newDB,";");
PREPARE create_schema FROM @sql;
EXECUTE create_schema;
DEALLOCATE PREPARE create_schema;
';

# Loop over tables in old schema
$sqlcopy.='OPEN curTable;
clone_tables: LOOP
';

# get next table name CONCAT(REPLACE(in_oldDB,LEFT(in_oldDB,5),""),"_",v_tname);
$sqlcopy.='FETCH curTable INTO v_tname
'; ECHO $sqlcopy;
# Quit if we're done
$sqlcopy.='IF v_finished = 1 THEN LEAVE clone_tables; END IF;
';
# Clone the table
$sqlcopy.='SET @sql = CONCAT("CREATE TABLE `", in_newDB, "`.`", REPLACE(in_oldDB,LEFT(in_oldDB,5),""),"_",v_tname, "` LIKE `", in_oldDB, "`.`", v_tname, "`;");
PREPARE clone_table FROM @sql;
EXECUTE clone_table;
DEALLOCATE PREPARE clone_table;
';
# Optionally copy data
#select v_tname; # This just gives some feedback in workbench for long-running copies
$sqlcopy.='IF (in_copyData > 0) THEN
    SET @sql = CONCAT("INSERT INTO `", in_newDB, "`.`", REPLACE(in_oldDB,LEFT(in_oldDB,5),""),"_",v_tname, "` SELECT * FROM `", in_oldDB, "`.`", v_tname, "`;");
    PREPARE clone_data FROM @sql;
    EXECUTE clone_data;
    DEALLOCATE PREPARE clone_data;
END IF;
';
# Result message
$sqlcopy.='SET @result = IFNULL(CONCAT(@result,",",v_tname),v_tname);

END LOOP clone_tables;
';
# Close cursor
$sqlcopy.='CLOSE curTable;
';
# Print result message
# $sqlcopy.='SELECT CONCAT("Copied the following tables from ", in_oldDB, " to ", in_newDB, ": ", @result);
$sqlcopy.='
END $$';