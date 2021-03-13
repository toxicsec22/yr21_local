<html>
<head>
<style>
body  
{ 
    margin-top: 0mm;
    margin-bottom: 20mm;
    font-family: sans-serif; font-size: small;
}
@media print {
    footer {page-break-after: always;}
    a {text-decoration: none;}
    
}
div.cert{
    page-break-inside:avoid;
    text-align: center;
    font-size:130%;
    color:black;
    margin-top: 20mm; margin-bottom: 20mm;
    }

div.bg {
    /*background-image:url("/'.$url_folder.'/hr/pics/completion.jpg");background-repeat: no-repeat;
    background-attachment: fixed;
    background-size: 100%;
    background-position: center;*/
    background-color:#FFFFFF; 
    
}
span.impt {font-size:170%;color:darkblue;font-family: serif; font-style: oblique; font-weight: 800;}
span.secondimpt {font-size:140%;color:black;font-family: serif; font-style: normal; font-weight: 800;}

div.leftsign{ font-size:80%;float:left;margin-left:15%;}
div.rightsign{ font-size:80%;float:right;margin-right:15%;}
</style>
<title>Certificates</title>
</head>
<body>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
if (!allowedToOpen(6901,'1rtc')) {   echo 'No permission'; exit;} 
 
$sql='SELECT StartDate,EndDate,TrainingTitle,Venue, CONCAT(e.FirstName, " ",LEFT(e.MiddleName,1),". ",e.SurName) AS FullName, Trainor, TrainorTitle, CONCAT(e1.FirstName, " ",LEFT(e1.MiddleName,1),". ", e1.Surname) AS TrainingLead, Position AS TrainingLeadPosition FROM `hr_2traintrack` ts
JOIN `hr_2trainsched` tm ON tm.TxnID=ts.TxnID JOIN `hr_1trainings` t ON t.TrainingID=tm.TrainingID
JOIN `1employees` e ON e.IDNo=ts.IDNo
   LEFT JOIN `1employees` e1 ON e1.IDNo=tm.LeadIDNo
	       LEFT JOIN `attend_0positions` p ON p.PositionID=tm.LeadPositionID
WHERE Completed=1 AND tm.TxnID='.$_GET['TxnID']; 
$stmt=$link->query($sql); $result=$stmt->fetchAll();

$cert='';$colcount=0;
foreach ($result as $row){
    $colcount++;
    $cert='<div class="cert"><div class="bg"><img src="/'.$url_folder.'/generalinfo/logo/1Rotary.png"></div><br>This<h3>CERTIFICATE OF ATTENDANCE</h3>is awarded to<br><br>
    <a href="javascript:window.print()"><span class="impt">'.$row['FullName'].'</a></span><br><br>in recognition of his/her valuable participation in the seminar on<br><br>
            <span class="secondimpt">"'.$row['TrainingTitle'].'"</span><br><br>held at '.$row['Venue'].', '
            .($row['StartDate']===$row['EndDate']?
              ' this '.date('jS',strtotime($row['StartDate'])).' day of '.date('M',strtotime($row['StartDate'])).' '.date('Y',strtotime($row['StartDate'])):
              'from the '.date('jS',strtotime($row['StartDate'])).((date('m',strtotime($row['StartDate']))===date('m',strtotime($row['EndDate'])))?'':' of '.date('F',strtotime($row['StartDate']))).' to the '.date('jS',strtotime($row['EndDate'])).' of '.date('F',strtotime($row['EndDate'])).' '.date('Y',strtotime($row['EndDate']))).'.
            <br><br><br><br><br><div id="wrap" style="width:800px;
    height:100px;
    margin:0 auto;">
            <div class="leftsign"><b>'.$row['Trainor'].'</b><br>'.$row['TrainorTitle'].'</div>'
            .'<div class="rightsign"><b>'.$row['TrainingLead'].'</b><br>'.$row['TrainingLeadPosition'].'</div>'
            .'</div id="wrap"></div></div>'.($colcount%2==0?'<footer></footer>':'<br><br><br>');
echo $cert;
}

 $link=null; $stmt=null;
?> 
</body>
</html>
