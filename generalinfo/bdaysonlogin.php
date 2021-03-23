<?php
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/commonfunctions/ordinalnumber.php';
    $link=!isset($link)?connect_db('2021_1rtc',0):$link;
    $sqlbday='Select DAY(Birthdate) AS bdate, ToSort, Nickname,SurName,Branch,(YEAR(NOW())-Yr) AS Age,Yr,IDNo from `birthdays` d WHERE (`ToSort`) BETWEEN CURDATE() AND DATE_ADD(NOW(), INTERVAL 7 DAY) UNION Select DAY(DateToday), DateToday,RemarksOnDates,null,TypeofDayName,null,null,1000 from attend_2attendancedates ad JOIN attend_0typeofday td ON td.TypeOfDayNo=ad.TypeOfDayNo WHERE (`DateToday`)BETWEEN CURDATE() AND DATE_ADD(NOW(), INTERVAL 7 DAY) and RemarksOnDates IS NOT NULL ORDER BY ToSort,Nickname';
  
    $stmt=$link->query($sqlbday);
    $datatoshow=$stmt->fetchAll();


echo '<div style="font-size:10pt; color:#ffffff; text-align:left;">';
echo '<img style="width:0px;height:0px;" src="yr21/generalinfo/bgpics/logolink.jpg"/>';
// echo '<img style="visibility:hidden;" src="yr'.date('y').'/generalinfo/logo/logolink.jpg"/>';
   if ($stmt->rowCount()>0){  
      foreach($datatoshow as $rows){
        echo ($rows['bdate']==date('d')
          ?
          ''.(($rows['IDNo']==1000)?'<div align="center"  class="id" style="background-color:blue;height:30px;">':
            '<div align="center" style="background-color:red;"><img width="25px" src="../yr21/generalinfo/icons/partyleft.png"/><font style="color:white;">').''
          :($rows['IDNo']==1000?'<font color="yellow">':'<font color="white">'))."".$rows['bdate']." - ".$rows['Nickname']. " (".($rows['IDNo']>999?$rows['Branch']:$rows['Age'].($rows['Age']==1?'st':($rows['Age']==2?'nd':($rows['Age']==3?'rd':'th'))).' Anniversary').")</font>".($rows['bdate']==date('d')?(($rows['IDNo']==1000)?'':'<img width="25px" src="../yr21/generalinfo/icons/partyright.png"/>').'</div>':"")."<br>";
    }

   }
  echo '</div>'; 
 $stmt=null;
    ?>
