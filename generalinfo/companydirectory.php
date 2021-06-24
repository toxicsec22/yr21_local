<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false; include_once($path.'/'.$url_folder.'/switchboard/contents.php');
 
 
if(!isset($_REQUEST['deptid'])){ $title='Company Directory'; $formdesc='<i>Click on the department/branch to see specifics.</i>';}
else {
    $deptid=intval($_REQUEST['deptid']);
    $sql1='SELECT Entity FROM `acctg_1budgetentities` WHERE EntityID='.$deptid;
    $stmt1=$link->query($sql1); $res1=$stmt1->fetch(); 
    $title=$res1['Entity']; $formdesc='';
    }
?>
<html>
<head>
<title><?php echo $title; ?></title>
<style type="text/css" media="all">
    table, td, tr { border:0px; font: normal 14px 'Helvetica', Arial, sans-serif; text-align: center; padding: 3px;}
    td { width: 250px;}
    tr { vertical-align: top;}
    #dirwrapper { width: 90%; margin-left: 10px; }
    #dept { width: 40%; margin-left: 10px; float: left; }
    #dept tr,td { vertical-align: middle; }
    #branch { width: 50%; margin-left: 50px; float: right; }
    #branch tr, td { vertical-align: middle; font: normal 12px 'Helvetica', Arial, sans-serif;}
    #deptname a:hover { background: #FFFF99; color: #003366; }
    /* #deptname a:link { color: black; font-weight: bold; font-size: 12pt; } */
</style></head>
<body><div id='dirwrapper'>
<?php
$colorcount=0;
$rcolor[0]=(!isset($_REQUEST['print'])?(isset($color1)?$color1:"E6FFCC"):"FFFFFF");
$rcolor[1]=isset($color2)?$color2:"FFFFFF";
	
echo '<h3>'.$title.'</h3>'.$formdesc.'<br><br>';
include_once('../backendphp/layout/linkstyle.php');
if(isset($_GET['deptid']) and !isset($_GET['ReportsTo'])){
	echo'<a id="link" style="float:right;" href="companydirectory.php?deptid='.$_GET['deptid'].'&ReportsTo=1">Reportorial View</a>';
}elseif(isset($_GET['deptid']) and isset($_GET['ReportsTo'])){
	echo'<a id="link" style="float:right;" href="companydirectory.php?deptid='.$_GET['deptid'].'">Rank View</a>';
}
if (allowedToOpen(64313,'1rtc')) { 
    ?><a id="link" href="directoryedit.php"> Edit Directory Entries</a><BR><BR>
        <form action="changedirectorypicture.php" method="POST" enctype="multipart/form-data">
    		<font size="small">For HR Use Only: Upload photo for ID Number? (Only *.jpg files allowed.) <input type="text" name="IDNum" size=4 autocomplete="off" list='employees'> 
                &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="file" name="userfile" accept="image/jpg">  
    		<input type="submit" name="submit" value="Submit"> 
                </font> </form> <br><br>
    <?php
	
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php'; 
    echo comboBox($link,'SELECT CONCAT(Nickname, " - ", FirstName, " ", SurName) AS Name, IDNo FROM `1employees` WHERE Resigned=0','Name','IDNo','employees');
}
if(isset($_REQUEST['deptid'])){ goto specificdept;}

//landing page
$url='<a style="color: black;  text-decoration:none;" href="companydirectory.php?deptid';
//convert br to n
$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");

$sql='SELECT d.deptid + 800 AS deptid, d.department AS Department, CONCAT("\n",REPLACE(d.tel,";","\n"),"\n\n") AS Telephone, d.address AS Address FROM `1departments` d JOIN attend_30currentpositions cp ON d.deptid=cp.deptid WHERE d.deptid NOT IN (3,4,10) GROUP BY d.deptid ORDER BY d.orderby;';

echo '<h4>OFFICES</h4>';
echo '<div id="dept">';
$stmtsp=$link->query($sql); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;

if ($stmtsp->rowCount()>0){
        $msgcb='<br><table>'
                . '<tr><th>Department</th><th>Telephone</th><th>Address</th></tr>';
    foreach($datatoshowsp as $rows){
        $sp++;
        $msgcb.='<tr bgcolor='. $rcolor[$colorcount%2].'><td><h3>'.$url.'='.$rows['deptid'].'">'.htmlspecialchars($rows['Department']).'</a></h3></td><td>'.nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",$rows['Telephone']))).'</td><td>'.nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",$rows['Address']))).'</td></tr>';
		$colorcount++;
   }
   $msgcb.='</table></div>';
   echo $msgcb;
}
echo '</div>';

$colorcount=0;
// choose areas with existing branches
$sql0='SELECT a.* FROM `0area` a JOIN `1branches` b ON a.AreaNo=b.AreaNo WHERE a.AreaNo<>0 GROUP BY a.AreaNo;'; 
$stmt0=$link->query($sql0); $res0=$stmt0->fetchAll(); 
$columnnames=array('Branch','Telephone','Address'); 
foreach ($res0 as $area) {
    $sql='SELECT BranchNo, Branch, CONCAT("\n",IF(ISNULL(Landline),"",CONCAT(REPLACE(Landline,";","\n"),"\n")),IF(ISNULL(Mobile),"\n",CONCAT(REPLACE(Mobile,";","\n"),"\n")),IFNULL(Email,""),"\n\n") AS Telephone, RegisteredAddress AS Address FROM `1branches` WHERE AreaNo='.$area['AreaNo'].' AND Active=1 AND Pseudobranch<>1 ORDER BY Branch';
    echo '<div id=branch>';
	
    $stmtsp=$link->query($sql); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;
	if ($stmtsp->rowCount()>0){
		echo '<h4>'.$area['Area'].'</h4>';
			$msgcb='<br><table>'
					. '<tr><th>Branch</th><th>Telephone</th><th>Address</th></tr>';
		foreach($datatoshowsp as $rows){
			$sp++;
			
			$msgcb.='<tr style="vertical-align:bottom" bgcolor='. $rcolor[$colorcount%2].'><td><h3>'.$url.'='.$rows['BranchNo'].'">'.htmlspecialchars($rows['Branch']).'</a></h3></td><td>'.nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",$rows['Telephone']))).'</td><td>'.nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",$rows['Address']))).'</td></tr>';
			$colorcount++;
	   }
	   $msgcb.='</table><br></div>';
	   echo $msgcb;
	}
	$colorcount=0;	
    echo '</div>';
	
}
goto end;

specificdept:
    $directory='<br><br><div align="center">';
    if ($deptid>=800){
        $sql0='SELECT d.tel AS Telephone, d.address AS Address FROM `1departments` d WHERE d.deptid='.($deptid-800);
        $stmt0=$link->query($sql0); $res0=$stmt0->fetch();        
        $sql1='CREATE TEMPORARY TABLE deptemployees AS SELECT LatestSupervisorIDNo,e.IDNo, cp.PositionID, Position, IF(JobLevelID LIKE \'%2\', JobLevelID+0.5, JobLevelID) AS Rank, CONCAT(Nickname, " ",SurName) AS Name, IF(ISNULL(Email) OR (Email LIKE ""),"",CONCAT("<br>",Email)) AS Email, IF(ISNULL(LocalNo) OR (LocalNo LIKE ""),"",CONCAT("<br>Office Local No. ",LocalNo)) AS `LocalNo`, IF(ISNULL(mobilenumbers) OR (mobilenumbers LIKE ""),"",CONCAT("<br>",REPLACE(mobilenumbers,";","<BR>"))) AS Mobile, IF(ISNULL(WorkAssign) OR (WorkAssign LIKE ""),"",CONCAT("<br>",REPLACE(WorkAssign,";","<BR>"))) AS WorkAssign,deptheadpositionid, deptid FROM `attend_30currentpositions` cp JOIN `1employees` e ON e.IDNo=cp.IDNo LEFT JOIN `1_gamit`.`1rtcusers` pu ON e.IDNo=pu.IDNo WHERE  (cp.deptid='.($deptid-800).') OR (cp.PositionID=(SELECT deptheadpositionid FROM `1departments` WHERE deptid='.($deptid-800).'))';
		// echo $sql1; exit();
        $stmt1=$link->prepare($sql1); $stmt1->execute();
if(!isset($_GET['ReportsTo'])){		
        $sql2='SELECT * FROM `deptemployees` WHERE PositionID=(SELECT deptheadpositionid FROM `1departments` WHERE deptid='.($deptid-800).') ';
        $stmt2=$link->query($sql2); $res2=$stmt2->fetch(); 
        $directory.='<img src="employeepics/'.$res2['IDNo'].'.jpg" width="100"><br>'.$res2['Name'].'<br>'.$res2['Position'].$res2['LocalNo'].$res2['Email'].$res2['Mobile'].$res2['WorkAssign'].'<br><br>';
        $sql2='SELECT Rank FROM `deptemployees` WHERE (PositionID<>deptheadpositionid)  AND PositionID NOT IN (SELECT deptheadpositionid FROM `1departments` WHERE deptid='.($deptid-800).') AND IF(deptid=2,Rank>=3,1=1) GROUP BY Rank ORDER BY Rank DESC';
        $stmt2=$link->query($sql2); $res2=$stmt2->fetchAll(); 
        foreach ($res2 AS $rank){
            $sql3='SELECT * FROM `deptemployees` WHERE PositionID<>deptheadpositionid AND Rank='.$rank['Rank'];        
          $stmt3=$link->query($sql3); $res3=$stmt3->fetchAll(); 
          $directory.='<table><tr>';
            foreach($res3 as $emp){
                $directory.='<td><img src="employeepics/'.$emp['IDNo'].'.jpg" width="100"><br>'.$emp['Name'].'<br>'.$emp['Position'].$emp['LocalNo'].$emp['Email'].$emp['Mobile'].$emp['WorkAssign'].'</td>';
            }
            
            $directory.='</tr></table><br><br>';
        }
}      
    
    } else {
        $sql0='SELECT CONCAT(IF(ISNULL(Landline),"",CONCAT(Landline,"<BR>")),IF(ISNULL(Mobile),"",CONCAT(Mobile,"<BR><br>")),IFNULL(Email,"")) AS Telephone, RegisteredAddress AS Address FROM `1branches` WHERE BranchNo='.$deptid;
        $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
        $sql1='CREATE TEMPORARY TABLE deptemployees AS SELECT e.IDNo, Position, pu.ProgCookie, IF(JobLevelID LIKE \'%2\', JobLevelID+0.5, JobLevelID) AS Rank, CONCAT(Nickname, " ",SurName) AS Name, Email, LocalNo, mobilenumbers AS Mobile FROM `attend_30currentpositions` cp JOIN `1employees` e ON e.IDNo=cp.IDNo LEFT JOIN `1_gamit`.`1rtcusers` pu ON e.IDNo=pu.IDNo WHERE cp.BranchNo='.$deptid;
        $stmt1=$link->prepare($sql1); $stmt1->execute();
if(!isset($_GET['ReportsTo'])){		
        $sql2='SELECT Rank FROM `deptemployees` GROUP BY Rank ORDER BY Rank DESC';
        $stmt2=$link->query($sql2); $res2=$stmt2->fetchAll(); 
        foreach ($res2 AS $rank){
            $sql3='SELECT * FROM `deptemployees` WHERE Rank='.$rank['Rank'];        
          $stmt3=$link->query($sql3); $res3=$stmt3->fetchAll(); 
          $directory.='<table><tr>';
            foreach($res3 as $emp){
                $directory.='<td><img src="employeepics/'.$emp['IDNo'].'.jpg" width="100"><br>'.htmlspecialchars($emp['Name']).'<br>'.$emp['Position'].'<br>'.htmlspecialchars($emp['LocalNo']).'<br>'.htmlspecialchars($emp['Email']).'<br>'.htmlspecialchars($emp['Mobile']).((allowedToOpen(2201,'1rtc'))?'<br><form action="#" method="POST"><input type="text" size="5" value="'.$emp['ProgCookie'].'" name="UProgCookie"><br><input type="hidden" value="'.$emp['IDNo'].'" name="IDNo"><input type="submit" name="btnUpdateProgCookie" value="Update"></form>':'').'</td>';
            }
            
            $directory.='</tr></table><br><br>';
        }
}
    }
if(isset($_GET['ReportsTo'])){	
?><style>
 a{
  text-decoration:none;
 }</style>
    <title>Compay Directory Reports To Whom</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {packages:["orgchart"]});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Name');
        data.addColumn('string', 'Manager');
        data.addColumn('string', 'ToolTip');

        // For each orgchart box, provide the name, manager, and tooltip to show.
  data.addRows([
  <?php
  if($deptid>=800){
  //head
  $sql='SELECT * FROM `deptemployees` WHERE PositionID=(SELECT deptheadpositionid FROM `1departments` WHERE deptid='.($deptid-800).')';
  $stmt=$link->query($sql); $result=$stmt->fetch();

          echo'[{"v":"'.$result['IDNo'].'", "f":"<img src=employeepics/'.$result['IDNo'].'.jpg width=100><br>'.$result['Name'].'<br>'.$result['Position'].$result['LocalNo'].$result['Email'].$result['Mobile'].$result['WorkAssign'].'<br><br>"},"", ""]';
  //endhead

		  $sql2='SELECT * FROM `deptemployees` WHERE (PositionID<>deptheadpositionid)  AND PositionID NOT IN (SELECT deptheadpositionid FROM `1departments` WHERE deptid='.($deptid-800).') ';
		  $stmt2=$link->query($sql2); $result2=$stmt2->fetchAll();
		  foreach($result2 as $res2){	
		  echo',[{"v":"'.$res2['IDNo'].'", "f":"<img src=employeepics/'.$res2['IDNo'].'.jpg width=100><br>'.$res2['Name'].'<br>'.$res2['Position'].$res2['LocalNo'].$res2['Email'].$res2['Mobile'].$res2['WorkAssign'].'<br><br>"},"'.$res2['LatestSupervisorIDNo'].'", ""]';
		  }
  }
////BRANCH
  else{
	   $sql1='CREATE TEMPORARY TABLE deptemployees1 AS SELECT cp.BranchNo,LatestSupervisorIDNo,e.IDNo, Position, pu.ProgCookie,  JobLevelID as Rank, CONCAT(Nickname, " ",SurName) AS Name, Email, LocalNo, mobilenumbers AS Mobile FROM `attend_30currentpositions` cp JOIN `1employees` e ON e.IDNo=cp.IDNo LEFT JOIN `1_gamit`.`1rtcusers` pu ON e.IDNo=pu.IDNo WHERE cp.BranchNo='.$deptid.' or cp.IDNo=(select LatestSupervisorIDNo from attend_30currentpositions where BranchNo='.$deptid.' Order By Rank Desc limit 1) ';
	   $stmt1=$link->prepare($sql1); $stmt1->execute();
$sqlb='select Pseudobranch from 1branches where BranchNo='.$deptid.'';
$stmtb=$link->query($sqlb); $resultb=$stmtb->fetch();
if($resultb['Pseudobranch']<>2){	   
	  //head
  $sql='SELECT d.* FROM `deptemployees1` d join 1branches b on b.BranchNo=d.BranchNo where Pseudobranch<>0 Order By Rank Desc Limit 1';
  $stmt=$link->query($sql); $result=$stmt->fetch();

          echo'[{"v":"'.$result['IDNo'].'", "f":"<img src=employeepics/'.$result['IDNo'].'.jpg width=100><br>'.htmlspecialchars($result['Name']).'<br>'.$result['Position'].'<br>'.htmlspecialchars($result['LocalNo']).'<br>'.htmlspecialchars($result['Email']).'<br>'.htmlspecialchars($result['Mobile']).((allowedToOpen(2201,'1rtc'))?'<br><form action=# method=POST><input type=text size=5 value='.$result['ProgCookie'].' name=UProgCookie><br><input type=hidden value='.$result['IDNo'].' name=IDNo><input type=submit name=btnUpdateProgCookie value=Update></form>':'').'"},"", ""]';
  //endhead

		  $sql2='SELECT * FROM `deptemployees1` WHERE IDNo<>'.$result['IDNo'].'';
		  $stmt2=$link->query($sql2); $result2=$stmt2->fetchAll();
		  foreach($result2 as $res2){
				$sqlr='SELECT IDNo FROM `deptemployees1` where Rank>'.$res2['Rank'].' and IDNo<>'.$result['IDNo'].' Order By Rank';
				$stmtr=$link->query($sqlr); $resultr=$stmtr->fetch();
				
				if($stmtr->rowCount()==0){
					$resultr['IDNo']=$result['IDNo'];
				}
				
		  echo',[{"v":"'.$res2['IDNo'].'", "f":"<img src=employeepics/'.$res2['IDNo'].'.jpg width=100><br>'.htmlspecialchars($res2['Name']).'<br>'.$res2['Position'].'<br>'.htmlspecialchars($res2['LocalNo']).'<br>'.htmlspecialchars($res2['Email']).'<br>'.htmlspecialchars($res2['Mobile']).((allowedToOpen(2201,'1rtc'))?'<br><form action=# method=POST><input type=text size=5 value='.$res2['ProgCookie'].' name=UProgCookie><br><input type=hidden value='.$res2['IDNo'].' name=IDNo><input type=submit name=btnUpdateProgCookie value=Update></form>':'').'"},"'.$resultr['IDNo'].'", ""]';
		  }
}else{
	  //head
  $sql='SELECT * FROM `deptemployees1` Order By Rank Desc Limit 1';
  $stmt=$link->query($sql); $result=$stmt->fetch();

          echo'[{"v":"'.$result['IDNo'].'", "f":"<img src=employeepics/'.$result['IDNo'].'.jpg width=100><br>'.htmlspecialchars($result['Name']).'<br>'.$result['Position'].'<br>'.htmlspecialchars($result['LocalNo']).'<br>'.htmlspecialchars($result['Email']).'<br>'.htmlspecialchars($result['Mobile']).((allowedToOpen(2201,'1rtc'))?'<br><form action=# method=POST><input type=text size=5 value='.$result['ProgCookie'].' name=UProgCookie><br><input type=hidden value='.$result['IDNo'].' name=IDNo><input type=submit name=btnUpdateProgCookie value=Update></form>':'').'"},"", ""]';
  //endhead

		  $sql2='SELECT * FROM `deptemployees1` WHERE IDNo<>'.$result['IDNo'].'';
		  $stmt2=$link->query($sql2); $result2=$stmt2->fetchAll();
		  foreach($result2 as $res2){
		  echo',[{"v":"'.$res2['IDNo'].'", "f":"<img src=employeepics/'.$res2['IDNo'].'.jpg width=100><br>'.htmlspecialchars($res2['Name']).'<br>'.$res2['Position'].'<br>'.htmlspecialchars($res2['LocalNo']).'<br>'.htmlspecialchars($res2['Email']).'<br>'.htmlspecialchars($res2['Mobile']).((allowedToOpen(2201,'1rtc'))?'<br><form action=# method=POST><input type=text size=5 value='.$res2['ProgCookie'].' name=UProgCookie><br><input type=hidden value='.$res2['IDNo'].' name=IDNo><input type=submit name=btnUpdateProgCookie value=Update></form>':'').'"},"'.$res2['LatestSupervisorIDNo'].'", ""]';
		  }
	
}
  }
?>
        ]);

        // Create the chart.
		var chart = new google.visualization.OrgChart(document.querySelector('#chart_div'));
        // Draw the chart, setting the allowHtml option to true for the tooltips.
        chart.draw(data, {allowHtml: true});
      }
   </script>
<?php
}	
//////////////////////////////////////////////////////////////////////END of ReportsTo///////////////////////////////////////////////////////////////////////////////////////////	
	
    $directory.='</div>'; 
    echo '<h4>'.$res0['Address'].'<br><br>'.$res0['Telephone'].'<br></h4>';
    echo $directory;
	echo '<div id="chart_div" style="width:200px; height:auto;"></div>';
    echo '<font style="font-size: 9pt;"><i>*To change photo, send an approved photo to hrd@1rotary.com.ph.  Photo must have white background, with collar, and preferably smiling.</i></font>';
	
	if (isset($_POST['btnUpdateProgCookie'])) {
		if(!allowedToOpen(2201,'1rtc')) { echo 'No Permission'; exit(); }
		$sql='UPDATE 1_gamit.1rtcusers ru SET ru.ProgCookie="'.$_POST['UProgCookie'].'" WHERE ru.IDNo='.$_POST['IDNo'];
 		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location: ".$_SERVER['HTTP_REFERER']);
	}
end:
 $link=null; $stmt=null;   
?>
</div>
</body>