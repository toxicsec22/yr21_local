</center>
<?php

 $title='';
$sql1='SELECT IDNo, FullName, department AS Department FROM `attend_30currentpositions` WHERE IDNo in (SELECT IDNo FROM `hr_2jobdesc` WHERE deptid IN (20,50)) ORDER BY JLID;';
    $sql2='SELECT OrderByNo AS `No.`, JobDesc AS `Job Description` FROM hr_2jobdesc jd  ';

    $groupby='IDNo';
    $orderby=' ORDER BY OrderByNo';
    $columnnames1=array('FullName','Department');
    $columnnames2=array('No.','Job Description');
    $nocount=true;
    include('../backendphp/layout/displayastablewithsub.php');
    
    ?><center>