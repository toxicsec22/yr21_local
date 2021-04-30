<html>
<?php $title='All Shoutouts'; 
echo '<title>';
echo $title;
echo '</title>';
?>
<body style="background-color:#afcecf;">
<?php
  $path=$_SERVER['DOCUMENT_ROOT']; 
 include_once $path.'/acrossyrs/dbinit/userinit.php';
 $link=!isset($link)?connect_db(''.date('Y').'_1rtc',0):$link;

echo '<div style="margin-left:27%;width:600px;padding:10px;background-color:#fff6d2;"><center>';
echo '<img src="../generalinfo/icons/ikawna.png" width="200px;"></center>';
$sqlmerits='select 0 AS SoM,
   
REPLACE(
 REPLACE(
 REPLACE(
 REPLACE(
 REPLACE(
 REPLACE(
 REPLACE(REPLACE(REPLACE(
 REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(Shoutout,":)","<img src=\"/acrossyrs/myswitch/emojis/smile.png\">"),":|","<img src=\"/acrossyrs/myswitch/emojis/straightface.png\">"),":(","<img src=\"/acrossyrs/myswitch/emojis/sad.png\">"),":D","<img src=\"/acrossyrs/myswitch/emojis/grin.png\">"),":O","<img src=\"/acrossyrs/myswitch/emojis/yikes.png\">"),";)","<img src=\"/acrossyrs/myswitch/emojis/wink.png\">"),":/","<img src=\"/acrossyrs/myswitch/emojis/hmm.png\">"),":P","<img src=\"/acrossyrs/myswitch/emojis/tounge.png\">"),":lol:","<img src=\"/acrossyrs/myswitch/emojis/lol.png\">"),":cool:","<img src=\"/acrossyrs/myswitch/emojis/cool.png\">"),":4smiles:","<img height=\"25px\" src=\"/acrossyrs/myswitch/gifs/4smiles.GIF\">"),":draw:","<img height=\"25px\" src=\"/acrossyrs/myswitch/gifs/draw.GIF\">"),":fresh:","<img height=\"25px\" src=\"/acrossyrs/myswitch/gifs/fresh.GIF\">"),":sbal:","<img height=\"25px\" src=\"/acrossyrs/myswitch/gifs/sbal.GIF\">")
 ,":sclown:","<img height=\"25px\" src=\"/acrossyrs/myswitch/gifs/sclown.GIF\">"),":sds1:","<img height=\"25px\" src=\"/acrossyrs/myswitch/gifs/sds1.GIF\">")
 ,":smiling:","<img height=\"25px\" src=\"/acrossyrs/myswitch/gifs/smiling.GIF\">"),":stom:","<img height=\"25px\" src=\"/acrossyrs/myswitch/gifs/stom.GIF\">"),":sun:","<img height=\"25px\" src=\"/acrossyrs/myswitch/gifs/sun.GIF\">"),":wsun:","<img height=\"25px\" src=\"/acrossyrs/myswitch/gifs/wsun.GIF\">")

AS Shoutout,FontColor,CONCAT(Nickname) AS ShoutedBy,IF(deptid IN (2,10),Branch,dept) AS Branch,ShoutStatTS FROM mktg_2shoutouts so join attend_30currentpositions cp on cp.IDNo=so.EncodedByNo JOIN 1employees e ON cp.IDNo=e.IDNo WHERE Resigned=0 AND ShoutStat=1 AND ShoutStatTS>=NOW() - INTERVAL 24 HOUR UNION SELECT 1 AS SoM,CONCAT("Job well done, <i>",e.Nickname," ",e.SurName,"</i>. You deserve the merit!") AS Shoutout,"#000000","","",ReporteeTS FROM hr_72scores s JOIN 1employees e ON e.IDNo=s.ReporteeNo WHERE ReporteeStatus=5 AND ReporteeTS>=NOW() - INTERVAL 24 HOUR GROUP BY ReporteeNo ORDER BY ShoutStatTS DESC;';

     $stmtmerits=$link->query($sqlmerits);
     $datatoshow=$stmtmerits->fetchAll();

echo '<br><br>';
     foreach($datatoshow as $rows){
        echo '<font style="font-size:11pt;color:'.$rows['FontColor'].';">
        <b>'.$rows['Shoutout'].'</b> '.($rows['SoM']==0?'- <i>'.$rows['ShoutedBy'].' ('.$rows['Branch'].')</i>':'').'
    </font><br><br>';
    
}

echo '</div>';
?>
</body>
</html>