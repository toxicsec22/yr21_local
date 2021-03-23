<?php
if (allowedToOpen(64872,'1rtc')) {
        $condition=' ';
    } elseif (allowedToOpen(64873,'1rtc')) {
        $condition=' TeamLeader='.$_SESSION['(ak0)'].' AND ';
    } elseif (allowedToOpen(64874,'1rtc')) {
        $condition=' SAM='.$_SESSION['(ak0)'].' AND ';
    } else {
        $condition=' bc.BranchNo='.$_SESSION['bnum'].'  AND ';
    }
    
    $sql0='CREATE TEMPORARY TABLE gen_info_clientbdays AS
SELECT 
        `c`.`ClientNo` AS `ClientNo`,
        `c`.`ClientName` AS `Company`,
        `cb`.`Name` AS `Name`,
        `cb`.`Position` AS `Position`,
        GROUP_CONCAT(`br`.`Branch`
            SEPARATOR ", ") AS `Branches`,
        GROUP_CONCAT(`bc`.`BranchNo`
            SEPARATOR ", ") AS `BranchNos`,
        `bg`.`TeamLeader` AS `TeamLeader`,
        `bg`.`SAM` AS `SAM`,
        DATE_FORMAT(MAKEDATE(YEAR(CURDATE()),
                        DAYOFYEAR(STR_TO_DATE(CONCAT(YEAR(CURDATE()),
                                                "-",
                                                MONTH(`cb`.`Bday`),
                                                "-",
                                                DAYOFMONTH(`cb`.`Bday`),
                                                \'%Y-%m-%d\'),
                                        \'%Y-%m-%d\'))),
                \'%b-%d\') AS `Birthday`,
        MAKEDATE(IF(DAYOFYEAR(STR_TO_DATE(CONCAT(YEAR(CURDATE()),
                                            "-",
                                            MONTH(`cb`.`Bday`),
                                            "-",
                                            DAYOFMONTH(`cb`.`Bday`),
                                            \'%Y-%m-%d\'),
                                    \'%Y-%m-%d\')) >= DAYOFYEAR(CURDATE()),
                    YEAR(CURDATE()),
                    YEAR(CURDATE()) + 1),
                DAYOFYEAR(STR_TO_DATE(CONCAT(YEAR(CURDATE()),
                                        "-",
                                        MONTH(`cb`.`Bday`),
                                        "-",
                                        DAYOFMONTH(`cb`.`Bday`),
                                        \'%Y-%m-%d\'),
                                \'%Y-%m-%d\'))) AS `ToSort`,
        `e`.`Nickname` AS `EncodedBy`,
        `cb`.`TimeStamp` AS `TimeStamp`
    FROM
        (((((`gen_info_1clientbdays` `cb`
        JOIN `1clients` `c` ON (`c`.`ClientNo` = `cb`.`ClientNo`))
        LEFT JOIN `1employees` `e` ON (`e`.`IDNo` = `cb`.`EncodedByNo`))
        JOIN `gen_info_1branchesclientsjxn` `bc` ON (`cb`.`ClientNo` = `bc`.`ClientNo`))
        JOIN `1branches` `br` ON (`br`.`BranchNo` = `bc`.`BranchNo`))
        JOIN `attend_1branchgroups` `bg` ON (`bg`.`BranchNo` = `bc`.`BranchNo`))
    WHERE '.$condition.'
        `br`.`Active` <> 0
    GROUP BY `c`.`ClientNo` , `cb`.`Name`;';
    
    $stmt0=$link->prepare($sql0); $stmt0->execute();
?>