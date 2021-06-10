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
    margin-top: 15mm; margin-bottom: 15mm;
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

.label{
    display: block;
}
.maindiv{
    display: inline-block;
    width: 33%;
    text-align: center;
    font-weight:bold;
    font-size: 80%;
}

</style>
<title>Certificates</title>
</head>
<body>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
date_default_timezone_set('Asia/Manila'); 
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link; 
if (!allowedToOpen(6901,'1rtc')) {   echo 'No permission'; exit;} 
if (!isset($_GET['TxnID'])) { 
    $sql="SELECT LAST_DAY(CURDATE()) AS 'StartDate',
    LAST_DAY(CURDATE()) AS 'EndDate',
       'Training Title' AS 'TrainingTitle',
        'Venue' AS 'Venue',
           'Trainee Name' AS 'FullName',
           'his/her' AS 'G',
            'Trainor Name' AS 'Trainor',
               'Trainor Title' AS  'TrainorTitle',
                'Training Lead name' AS 'TrainingLead',
                   'Training Lead position' AS 'TrainingLeadPosition',
                   'HR Dept Head Name' AS 'HRDeptHeadName',
                   'HR Dept Head' AS 'HRDeptHead',
                   'Presidet Name' AS 'PresidentName',
                   'President' AS 'President';";
    
} else {
    $sqlgethrpresmain="SELECT CONCAT(Nickname,' ',LEFT(MiddleName,1),'. ',SurName) AS FullName,Position FROM attend_30currentpositions cp JOIN 1employees e ON cp.IDNo=e.IDNo  WHERE PositionID = (SELECT AllowedPos FROM permissions_2allprocesses WHERE ProcessID = ";

    //for HR Signatory
    $getHR = $sqlgethrpresmain." 6902)";
    $pst = $link->query($getHR);
    $row = $pst->fetch();
    $hrname=$row['FullName']; $hrpos=$row['Position'];

    //for Pres Signatory
    $getpres = $sqlgethrpresmain." 6903)";
    $pst = $link->query($getpres);
    $row = $pst->fetch();
    $presname=$row['FullName']; $prespos=$row['Position'];


$sql='SELECT StartDate,EndDate,TrainingTitle,Venue, CONCAT(e.FirstName, " ",LEFT(e.MiddleName,1),". ",e.SurName) AS FullName,IF(e.Gender=1,"his","her") AS G, Trainor, TrainorTitle, CONCAT(e1.FirstName, " ",LEFT(e1.MiddleName,1),". ", e1.Surname) AS TrainingLead, Position AS TrainingLeadPosition,
"'.$hrname.'" AS `HRDeptHeadName`,
                   "'.$hrpos.'" AS `HRDeptHead`,
                   "'.$presname.'" AS `PresidentName`,
                   "'.$prespos.'" AS `President`
 FROM `hr_2traintrack` ts
JOIN `hr_2trainsched` tm ON tm.TxnID=ts.TxnID JOIN `hr_1trainings` t ON t.TrainingID=tm.TrainingID
JOIN `1employees` e ON e.IDNo=ts.IDNo
   LEFT JOIN `1employees` e1 ON e1.IDNo=tm.LeadIDNo
	       LEFT JOIN `attend_0positions` p ON p.PositionID=tm.LeadPositionID
WHERE Completed=1 AND tm.TxnID='.$_GET['TxnID']; 

}

$stmt=$link->query($sql); $result=$stmt->fetchAll();


$cert='';$colcount=0;
foreach ($result as $row){
    $colcount++;
   
        $hrdiv = '<div class="maindiv first"><span class="label">'.$row['HRDeptHeadName'].'</span><span class="label">'.$row['HRDeptHead'].'</span></div>';
        $presdiv = '<div class="maindiv second"><br><br><br><br><span class="label">'.$row['PresidentName'].'</span><span class="label">'.$row['President'].'</span></div>';
    
    $cert='<div class="cert"><div class="bg"><img src="/'.$url_folder.'/generalinfo/logo/1RotaryOnly.png"></div><br>This<h3>CERTIFICATE OF ATTENDANCE</h3>is awarded to<br><br>
    <a href="javascript:window.print()"><span class="impt">'.$row['FullName'].'</a></span><br><br>in recognition of '.$row['G'].' valuable participation in the seminar on<br><br>
            <span class="secondimpt">"'.$row['TrainingTitle'].'"</span><br><br>held at '.$row['Venue'].', '
            .($row['StartDate']===$row['EndDate']?
              ' this '.date('jS',strtotime($row['StartDate'])).' day of '.date('M',strtotime($row['StartDate'])).' '.date('Y',strtotime($row['StartDate'])):
              'from the '.date('jS',strtotime($row['StartDate'])).((date('m',strtotime($row['StartDate']))===date('m',strtotime($row['EndDate'])))?'':' of '.date('F',strtotime($row['StartDate']))).' to the '.date('jS',strtotime($row['EndDate'])).' of '.date('F',strtotime($row['EndDate'])).' '.date('Y',strtotime($row['EndDate']))).'.
            <br><br><br><br><br><div id="wrap" style="width:800px;
    height:100px;
    margin:0 auto;">
           '.
           
           '<div class="maindiv first"><span class="label">'.$row['Trainor'].'</span><span class="label">'.$row['TrainorTitle'].'</span></div>'.
           
           '<div class="maindiv first"><span class="label">'.$row['TrainingLead'].'</span><span class="label">'.$row['TrainingLeadPosition'].'</span></div>'.

          
           $hrdiv .

           '<div class="maindiv"><span class="label"></span><span class="label"></span></div>'.
           
           $presdiv.

           '<div class="maindiv"><span class="label"></span><span class="label"></span></div>'

            .'</div id="wrap"></div></div>'.($colcount%2==0?'<footer></footer>':'<footer></footer>');
echo $cert;
}

 $link=null; $stmt=null;
?> 
</body>
</html>
