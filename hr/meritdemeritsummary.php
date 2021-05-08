<?php
 $sql0='SELECT ReporteeNo AS TxnID,
                IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  1 THEN  
WeightinPoints END), 0),"") AS January,
           IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  2 THEN  
WeightinPoints END), 0),"") AS February,
           IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  3 THEN  
WeightinPoints END), 0),"") AS March,
           IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  4 THEN  
WeightinPoints END), 0),"") AS April,
           IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  5 THEN  
WeightinPoints END), 0),"") AS May,
           IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  6 THEN  
WeightinPoints END), 0),"") AS June,
           IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  7 THEN  
WeightinPoints END), 0),"") AS July,
           IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  8 THEN  
WeightinPoints END), 0),"") AS August,
           IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN  9 THEN  
WeightinPoints END), 0),"") AS September,
           IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN 10 THEN  
WeightinPoints END), 0),"") AS October,
           IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN 11 THEN  
WeightinPoints END), 0),"") AS November,
           IFNULL(round(SUM(CASE MONTH(DateOfIncident) WHEN 12 THEN  
WeightinPoints END), 0),"") AS  
December,IFNULL(round(SUM(WeightinPoints), 0),"") AS YrTotal FROM  
hr_72scores s JOIN hr_71scorestmt ss ON s.SSID=ss.SSID JOIN  
attend_30currentpositions cp ON s.ReporteeNo=cp.IDNo JOIN  
hr_70points p ON ss.PointID=p.PointID WHERE ReporteeNo='.$idno.'';


                $columnnameslist=array('January', 'February', 'March', 'April',  
'May', 'June', 'July', 'August', 'September', 'October', 'November',  
'December','YrTotal');

                $title=''; $formdesc=''; $txnidname='TxnID';
                $columnnames=$columnnameslist;
                $width='80%';
                $sql = $sql0.' AND stmtcat=1 AND DecisionStatus=3;';
                $title='';
                $subtitle = '<font color="green">Merits</font>'; $hidecount=true; $hidecontents=1;
                include('../backendphp/layout/displayastablenosort.php');

                $title=''; $formdesc=''; $txnidname='TxnID';

                $columnnames=$columnnameslist;
                $width='80%';
                $sql = $sql0.' AND stmtcat=0 AND DecisionStatus=1;';
                $title='';
                $subtitle = '<font color="blue">Demerits</font>'; 
                include('../backendphp/layout/displayastablenosort.php');
