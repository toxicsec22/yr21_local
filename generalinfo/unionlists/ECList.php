<?php
$sql0='CREATE TEMPORARY TABLE ECList AS SELECT 
        `c`.`ClientNo` AS `BECSNo`,
        `c`.`ClientName` AS `BECSName`,
        `c`.`TIN` AS `TIN`,
        `c`.`TownOrCity` AS `Address`,
        `c`.`Inactive` AS `Inactive`, "C" AS BECS
    FROM
        `1clients` `c`
    WHERE
        `c`.`Inactive` = 0 
    UNION ALL SELECT 
        `e`.`IDNo` AS `IDNo`,
        CONCAT(`e`.`FirstName`, " ", `e`.`SurName`) ,
        "" AS `TIN`,
        "" AS `Address`,
        `e`.`Resigned` , "E" AS BECS
    FROM
        `1employees` `e`
    WHERE
        `e`.`IDNo` > 1002 
    UNION ALL SELECT 
        `id`.`IDNo` AS `IDNo`,
        CONCAT(`id`.`FirstName`,
                " ",
                `id`.`SurName`,
                " RESIGNED"),
        "" AS `TIN`,
        "" AS `Address`,
        `id`.`Resigned?` , "E" AS BECS
    FROM
        (`1_gamit`.`0idinfo` `id`
        LEFT JOIN `1employees` `e` ON (`id`.`IDNo` = `e`.`IDNo`))
    WHERE
        `id`.`IDNo` > 1002
            AND `e`.`IDNo` IS NULL
            AND `id`.`Resigned?` = 1
            AND `id`.`IDNo` IN (SELECT 
                `acctg_3unpdclientinvlastperiod`.`ClientNo`
            FROM
                `acctg_3unpdclientinvlastperiod`)';
$stmt0=$link->prepare($sql0); $stmt0->execute();