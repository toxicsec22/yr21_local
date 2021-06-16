<?php

$sql0='CREATE TEMPORARY TABLE effectivedate AS
            SELECT MAX(DateEffective) AS DateEffective, MinWageAreaID FROM `1_gamit`.`payroll_4wageorders` WHERE YEAR(DateEffective)<='.$currentyr.' GROUP BY MinWageAreaID;'; 
            $stmt0=$link->prepare($sql0); $stmt0->execute();

            $sql0='SELECT TotalMinWage AS NCRrate FROM `1_gamit`.`payroll_4wageorders` wo JOIN `effectivedate` ed ON ed.DateEffective=wo.DateEffective AND ed.MinWageAreaID=wo.MinWageAreaID WHERE wo.MinWageAreaID=1';
            $stmt0=$link->query($sql0); $result0=$stmt0->fetch();
            $ncrrate=$result0['NCRrate']; $multiplier=1.1; //10% above provincial rate


            $increaserate=1.1; $steprate=1.1;

            ?>