<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(2203,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT bl.IDNo, Concat(Nickname,\' \', SurName) as FullName FROM `events_2biggestloser` bl left join 1employees e on e.IDNo=bl.IDNo ORDER BY SurName','IDNo','FullName','employees'); 
$startofevent=$currentyr.'-03-01';
$which=$_GET['w'];
switch ($which){	
	case'Participants':
	case'Encode':
	case'Edit':
	case'Announcement':
	case'Teams':
	case'Individuals':
	
	?><html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Biggest Loser</title>
	<link rel='shortcut icon' href='img/favicon.ico' type='image/x-icon' />
  <!-- Custom fonts for this template-->
  <link href="https://<?php echo $_SERVER['HTTP_HOST'];?>/acrossyrs/js/bootstrapSBADMIN2/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="https://<?php echo $_SERVER['HTTP_HOST'];?>/acrossyrs/js/bootstrapSBADMIN2/css/sb-admin-2.min.css" rel="stylesheet">
  
  <!-- Custom styles for this page -->
  <link href="https://<?php echo $_SERVER['HTTP_HOST'];?>/acrossyrs/js/bootstrapSBADMIN2/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

</head>

<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <?php include_once('blsidebar-admin.php');?>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <?php
			include_once('bltopbar.php');
		  ?>
		
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">

        
          <!-- DataTales Example -->
          <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary"><?php echo isset($_GET['w'])?$_GET['w']:'';?></h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
			   <?php 
			 if($_GET['w']=='Participants'){
				 if(isset($_GET['Message'])){
					 echo $_GET['Message'];
					 
				 }
				 
				 $condition=date('Y-m-d');
		// echo $condition; exit();
		
			if($condition>=$startofevent){
	//team1
		$sql='select CONCAT(Nickname,\' \',SurName,\' \',\'(\',dept,\')\') as FullName from events_2biggestloser bl left join attend_30currentpositions cp on cp.IDNo=bl.IDNo left join 1employees e on e.IDNo=cp.IDNo where Team=1 ORDER BY FullName ASC';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table style="float:left; display: inline;">
				<thead>
					<tr><th>Fit & Happy</th></tr>
				</thead>';
		foreach($result as $res){
			echo'<tr><td>'.$res['FullName'].'</td></tr>';
			
		}
		echo'</table>';
		
	//team2
		$sql='select CONCAT(Nickname,\' \',SurName,\' \',\'(\',dept,\')\') as FullName from events_2biggestloser bl left join attend_30currentpositions cp on cp.IDNo=bl.IDNo left join 1employees e on e.IDNo=cp.IDNo where Team=2 ORDER BY FullName ASC';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table style="float:left; display: inline; margin-left:30px;">
				<thead>
					<tr><th>Won Direction</th></tr>
				</thead>';
		foreach($result as $res){
			echo'<tr><td>'.$res['FullName'].'</td></tr>';
			
		}
		echo'</table>';
		
		//team3
		$sql='select CONCAT(Nickname,\' \',SurName,\' \',\'(\',dept,\')\') as FullName from events_2biggestloser bl left join attend_30currentpositions cp on cp.IDNo=bl.IDNo left join 1employees e on e.IDNo=cp.IDNo where Team=3 ORDER BY FullName ASC';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table style="float:left; display: inline; margin-left:30px;">
				<thead>
					<tr><th>Lean & Mean</th></tr>
				</thead>';
		foreach($result as $res){
			echo'<tr><td>'.$res['FullName'].'</td></tr>';
			
		}
		echo'</table>';	
			}else{
		$sql='select CONCAT(Nickname,\' \',SurName,\' \',\'(\',dept,\')\') as FullName from events_2biggestloser bl left join attend_30currentpositions cp on cp.IDNo=bl.IDNo left join 1employees e on e.IDNo=cp.IDNo';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		
		echo '<table style="float:left; display: inline; margin-left:30px;">
				<thead>
					<tr><th>FullName</th></tr>
				</thead>';
		foreach($result as $res){
			echo'<tr><td>'.$res['FullName'].'</td></tr>';
			
		}
				
			}
			 }elseif($_GET['w']=='Encode'){
				 if (!allowedToOpen(2204,'1rtc')) { echo 'No permission'; exit();}
				 echo '<form method="post" action="biggestloser.php?w=EncodeProcess">
							Participant <input type="text" name="Employee" list="employees">
							Weight in Kilos: <input type="text" name="weight" size="5">
							Date: <input type="date" name="Date" value="'.date('Y-m-d').'">
							<input class="btn btn-primary btn-user " type="submit" name="submit">
						</form>';
				 $sql='select bls.*,Concat(Nickname,\' \', SurName) AS FullName from events_2biggestlosersub bls left join 1employees e on e.IDNo=bls.IDNo Order By Date Asc';
				 $stmt=$link->query($sql); $result=$stmt->fetchAll(); 
				 
				 
				 echo '<table class="table table-bordered" id="dataTable" cellspacing="0"">
				<thead>
					<tr><th>Participant</th><th>Weight In Kilos</th><th>Date</th><th>Edit/Delete</th></tr>
				</thead><tbody>';
		foreach($result as $res){
			echo'<tr><td>'.$res['FullName'].'</td><td>'.$res['Weight'].'</td><td>'.$res['Date'].'</td><td><a href="biggestloser.php?w=Edit&TxnSubId='.$res['TxnSubId'].'" ><i title="Edit" class="fas fa-fw fa-edit"></i></a><a href="biggestloser.php?w=Delete&TxnSubId='.$res['TxnSubId'].'" OnClick="return confirm(\'Really delete this?\')"><i title="Delete" class="fas fa-fw fa-trash"></i></a>
						</td></tr>';
			
		}
		echo'</tbody></table>';	 
			 }elseif($_GET['w']=='Edit'){
				 $txnsubid=intval($_GET['TxnSubId']);
				  $sql='SELECT bls.*,Concat(Nickname,\' \', SurName) AS FullName from events_2biggestlosersub bls left join 1employees e on e.IDNo=bls.IDNo WHERE TxnSubID='.$txnsubid.'';
				  // echo $sql; exit();
				  $stmt=$link->query($sql); $result=$stmt->fetch(); 
				  
				  echo '<table style="border"><tr><th>Participant</th><th>Weight in Kilos</th><th>Date</th><th></th></tr>
					<tr><td>'.$result['FullName'].'</td><td>'.$result['Weight'].'</td><td>'.$result['Date'].'</td></tr></table></br> ';
					
					
				echo '<table><form action="biggestloser.php?w=EditProcess&TxnSubId='.$txnsubid.'" method="POST">
				
				<tr><th>Participant</th><th>Weight in Kilos</th><th>Date</th><th></th></tr>
				
				<tr>
				<td><input type="text" value="'.$result['FullName'].'" name="Employee" list="employees" required></td>		
				<td><input type="text" value="'.$result['Weight'].'" name="Weight" required></td>
				<td><input type="text" value="'.$result['Date'].'" name="Date" required></td>
				<td><input type="submit" class="btn btn-primary btn-user " name="submit" OnClick="return confirm(\'Are you sure you want to edit?\');"></td>
				</form></tr></table>';
				
				 
			 }elseif($_GET['w']=='Announcement'){
				 echo '<title>Biggest loser</title>';
	?>
	<style>
		#button {
		  margin-top:5%;
		  margin-bottom:95%;
		  margin-left:45%;
		  margin-right:55%;
		  width: auto;
		  border: 1px solid black;
		  background-color: #4CAF50;
		  color: white;
		  padding: 14px 28px;
		  font-size: 25px;
		  cursor: pointer;
		  text-align: center;
		  border-radius: 5px; 
		}

		#button:hover {
		  background-color: black;
		  color: white;
		}
</style>
	<?php
	
	echo'<div style="border: 1px solid black; padding:5px; border-radius:5px;">
		<b>OBJECTIVES:</b></br>
		<ol>
		<li> To develop a healthy lifestyle, self-determination, and positive attitude "I can".</li>
		<li> To enhance teamwork and encourage participants to reach their desired goal.</li>
		<li> To attain the desired weight.</li>
		<li> To develop self-discipline, self confidence, and serve as role model.</li></ol></div>
		<div>
		</br>
		<b>GENERAL GUIDELINES:</b></br>
		<b>1.</b> Activity runs from February 27, '.$currentyr.' until May 08, '.$currentyr.'.</br>
		<b>2.</b> This event is open for Office personnel only.</br>
		<b>3.</b> Activity schedule:</br>			
				'.str_repeat('&nbsp;',5).' a. February 27-29 - Registration thru log-in page.</br>
				'.str_repeat('&nbsp;',5).' b. March 02, Monday - 10:00am to 12:00pm - 1st weigh-in.<br>
				'.str_repeat('&nbsp;',5).' c. April 02, Wednesday - 10:00am to 12:00pm - 2nd weigh-in.<br>
				'.str_repeat('&nbsp;',5).' d. May 02, Saturday - 10:00am to 12:00pm - last weigh-in.<br>
				'.str_repeat('&nbsp;',5).' e. May 04 - 07, consolidation and validation of data.<br>
				'.str_repeat('&nbsp;',5).' f. May 08 - Friday announcement of winners c/o HR thru log-in page.<br>
		<b>4.</b> Teams with incomplete team members during weigh-in shall be penalized with an additional 5 points (5 kilos) for each of the member absent.</br>
		<b>5.</b> Extension for weighing-in is not allowed, in order to be fair with all those who have followed the guidelines.</br>
		<b>6.</b> Group winner will be given cash prize amounting of <b>Php 10,000.00.</b></br>
				'.str_repeat('&nbsp;',4).'Biggest loser winner (individual) will be given cash prize amounting to <b>Php 5,000.00.</b>
	    </div>';
		$condition=date('Y-m-d');
		// echo $condition; exit();
		
		if($condition<$startofevent){
			$sql='select IDNo from events_2biggestloser where IDNo=\''.$_SESSION['(ak0)'].'\'';
			$stmt=$link->query($sql); 
		if($stmt->rowCount()==0){
		echo '<form method="post" action="biggestloser.php?w=join">
			<input id="button" type="submit" name="submit" value="Join Now" OnClick="return confirm(\'Are you sure you want to join?\');">';
		}
		}
	break;
			 }elseif($_GET['w']=='Teams'){
				 	
	$condition=date('Y-m-d');
	// echo $condition; exit();
				include_once($path.'/acrossyrs/js/reportcharts/mgraphlabel.php'); 
				include($path.'/acrossyrs/js/reportcharts/includejscharts.php');
				 $echo='';
$sql0='CREATE TEMPORARY TABLE `graphreport11` (
  `ReportID` tinyint(4) NOT NULL AUTO_INCREMENT,
  `ReportTitle` varchar(100) DEFAULT NULL,
  `OtherDesc` varchar(20) NOT NULL,
  `Label` varchar(100) NOT NULL,
  `xaxis` varchar(25) NOT NULL,
  `yaxis` varchar(25) NOT NULL,
  `min` tinyint(1)DEFAULT NULL,
  `legend1` varchar(55) NOT NULL,
  `legend2` varchar(55) NOT NULL,
  `legend3` varchar(55) NOT NULL,
  PRIMARY KEY (`ReportID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;'; //graph report
$stmt=$link->prepare($sql0);$stmt->execute();

$sql0='CREATE TEMPORARY TABLE `graphboard11` (
  `TxnID` smallint(6) NOT NULL AUTO_INCREMENT,
  `IDNo` smallint(6) DEFAULT NULL,
  `GraphID` tinyint(2) NOT NULL,
  `DataSet1` varchar(2000) NOT NULL,
  `DataSet2` varchar(2000) NOT NULL,
  `DataSet3` varchar(2000) NOT NULL,
  `ReportID` tinyint(4) NOT NULL,
  PRIMARY KEY (`TxnID`),
  UNIQUE KEY `IDNo` (`IDNo`,`GraphID`,`ReportID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1'; //graphboard
$stmt=$link->prepare($sql0);$stmt->execute();

	$bwidth='80%';
		$ReportTitle1='ReportTitle="Total Percent Weight Loss (%)"';
		$ReportTitle='ReportTitle="Total Weight Loss"';
		$graphtitle='IDNo';
		
		$select=' \'\' as IDNo';
		$condi='ORDER BY g.ReportID ASC';
		$leftjoin='';
		$teamarray="'Fit & Happy','Won Direction','Lean & Mean'";
	

		$dataset1=''; 
		$dataset2=''; 
		
		//first
		for($c=1; $c<=3; $c++){
		
		if($condition>=''.$currentyr.'-04-02'){
		//toarrange
		$sqlt2='Create temporary table toarrange2 as select ifnull(truncate(sum(Weight),1),0) as TotalWeight,\'1\' as Col  from events_2biggestloser bl left join events_2biggestlosersub bls on bls.IDNo=bl.IDNo where Team=\''.$c.'\' AND month(Date)=3 UNION select ifnull(truncate(sum(Weight),1),0) as TotalWeight,\'2\' as Col  from events_2biggestloser bl left join events_2biggestlosersub bls on bls.IDNo=bl.IDNo where Team=\''.$c.'\' AND '.(($condition>=''.$currentyr.'-05-02')?'month(Date)=5':'month(Date)=4').'';
		$stmtt2=$link->prepare($sqlt2); $stmtt2->execute();
		//arrange
		$sqla2='Create temporary table arrange2 as select sum(CASE WHEN Col=1 then TotalWeight end) as FirstCol,sum(CASE WHEN Col=2 then TotalWeight end) as SecondCol from toarrange2';
		$stmta2=$link->prepare($sqla2); $stmta2->execute();
		
		
		$sql12='select ifnull(truncate(((FirstCol-SecondCol)/FirstCol)*100,2),0) as TotalWeight from arrange2';
		}else{
			$sql12='select \'0\' as TotalWeight';
		}
		
		$stmt12=$link->query($sql12); $res12=$stmt12->fetch();
		if($stmt12->rowCount()==0){
			$dataset2.='0,';
		
		} else {
			$dataset2.=$res12['TotalWeight'].',';
		}
		
		if($condition>=''.$currentyr.'-04-02'){
		$sqldt2='drop temporary table toarrange2;';
		$stmtdt2=$link->prepare($sqldt2); $stmtdt2->execute();
		$sqlda2='drop temporary table arrange2;';
		$stmtda2=$link->prepare($sqlda2); $stmtda2->execute();
		}
		
	}		
	
	$dataset2=substr($dataset2, 0, -1);
			//second
	for($c=1; $c<=3; $c++){
		
		if($condition>=''.$currentyr.'-04-02'){
		//toarrange
		$sqlt='Create temporary table toarrange as select ifnull(truncate(sum(Weight),1),0) as TotalWeight,\'1\' as Col  from events_2biggestloser bl left join events_2biggestlosersub bls on bls.IDNo=bl.IDNo where Team=\''.$c.'\' AND month(Date)=3 UNION select ifnull(truncate(sum(Weight),1),0) as TotalWeight,\'2\' as Col  from events_2biggestloser bl left join events_2biggestlosersub bls on bls.IDNo=bl.IDNo where Team=\''.$c.'\' AND '.(($condition>=''.$currentyr.'-05-02')?'month(Date)=5':'month(Date)=4').'';
		$stmtt=$link->prepare($sqlt); $stmtt->execute();
		//arrange
		$sqla='Create temporary table arrange as select sum(CASE WHEN Col=1 then TotalWeight end) as FirstCol,sum(CASE WHEN Col=2 then TotalWeight end) as SecondCol from toarrange';
		$stmta=$link->prepare($sqla); $stmta->execute();
		
		
		$sql1='select (FirstCol-SecondCol) as TotalWeight from arrange';
		}else{
			$sql1='select \'0\' as TotalWeight';
		}
		
		$stmt1=$link->query($sql1); $res1=$stmt1->fetch();
		if($stmt1->rowCount()==0){
			$dataset1.='0,';
		
		} else {
			$dataset1.=$res1['TotalWeight'].',';
		}
		
		if($condition>=''.$currentyr.'-04-02'){
		$sqldt='drop temporary table toarrange;';
		$stmtdt=$link->prepare($sqldt); $stmtdt->execute();
		$sqlda='drop temporary table arrange;';
		$stmtda=$link->prepare($sqlda); $stmtda->execute();
		}
		
	}		
	
	$dataset1=substr($dataset1, 0, -1);

	// echo $dataset1; exit();
	//first graph
	$sqlcsub='INSERT INTO graphboard11 SET GraphID=2,DataSet2="'.$dataset2.'",ReportID=4;';
	$stmt=$link->prepare($sqlcsub);$stmt->execute(); 
	//second graph
	$sqlcsub='INSERT INTO graphboard11 SET GraphID=2,DataSet1="'.$dataset1.'",ReportID=5;';
	$stmt=$link->prepare($sqlcsub);$stmt->execute(); 
	// echo $sqlcsub; exit();  	
	
	//div table
	echo'<table style="width:auto; bottom:0; border:1px solid green; font-size:10pt; float:left; margin:3px;">
	<tr><th>Fit & Happy</th></tr>';
	
	$sqlp='select CONCAT(Nickname,\' \',SurName,\' \',\'(\',dept,\')\') as FullName from events_2biggestloser bl left join attend_30currentpositions cp on cp.IDNo=bl.IDNo left join 1employees e on e.IDNo=cp.IDNo where Team=1 ORDER BY FullName ASC';
	$stmtp=$link->query($sqlp); $resultp=$stmtp->fetchAll();
		foreach($resultp as $resp){
			echo'<tr><td>'.$resp['FullName'].'</td></tr>';
		
		}
	
	echo'</table>';
	
	echo'<table style="width:auto; display:inline; bottom:0; border:1px solid green; font-size:10pt; float:left; margin:3px;">
	<tr><th>Won Direction</th></tr>';
	
	$sqlp='select CONCAT(Nickname,\' \',SurName,\' \',\'(\',dept,\')\') as FullName from events_2biggestloser bl left join attend_30currentpositions cp on cp.IDNo=bl.IDNo left join 1employees e on e.IDNo=cp.IDNo where Team=2 ORDER BY FullName ASC';
	$stmtp=$link->query($sqlp); $resultp=$stmtp->fetchAll();
		foreach($resultp as $resp){
			echo'<tr><td>'.$resp['FullName'].'</td></tr>';
		
		}
	
	echo'</table>';
	
	
	echo'</table>';
	
	echo'<table style="width:auto; display:inline; bottom:0; border:1px solid green; font-size:10pt; float:left; margin:3px;">
	<tr><th>Lean & Mean</th></tr>';
	
	$sqlp='select CONCAT(Nickname,\' \',SurName,\' \',\'(\',dept,\')\') as FullName from events_2biggestloser bl left join attend_30currentpositions cp on cp.IDNo=bl.IDNo left join 1employees e on e.IDNo=cp.IDNo where Team=3 ORDER BY FullName ASC';
	$stmtp=$link->query($sqlp); $resultp=$stmtp->fetchAll();
		foreach($resultp as $resp){
			echo'<tr><td>'.$resp['FullName'].'</td></tr>';
		
		}
	
	echo'</table>';
//first graph
$sqlcmain='INSERT INTO graphreport11 SET xaxis="",legend2="Total Weight Loss (%)",yaxis="In Percent",min="",'.$ReportTitle1.',ReportID=4,Label="'.$teamarray.'"';

// echo $sqlcmain; exit();
$stmt=$link->prepare($sqlcmain);$stmt->execute();

//second graph
$sqlcmain='INSERT INTO graphreport11 SET xaxis="",legend1="Total Weight Loss",yaxis="In Kilos",min="",'.$ReportTitle.',ReportID=5,Label="'.$teamarray.'"';

// echo $sqlcmain; exit();
$stmt=$link->prepare($sqlcmain);$stmt->execute();


//End

$sql = 'SELECT g.*,gr.*,'.$select.' FROM graphboard11 g '.$leftjoin.' JOIN graphreport11 gr ON g.ReportID=gr.ReportID WHERE g.ReportID IN (4,5) '.$condi.' ';
// echo $sql; exit();
$stmt=$link->query($sql); $res=$stmt->fetchall();

$sqldrop='DROP TEMPORARY TABLE graphboard11';
$stmt=$link->prepare($sqldrop);$stmt->execute();

$sqldrop='DROP TEMPORARY TABLE graphreport11';
$stmt=$link->prepare($sqldrop);$stmt->execute();
// print_r($res); exit();
$c=1;
$displaydiv=''; $newdiv=''; 

foreach ($res as $field) {
	 if ($field['GraphID']==2){
		include($path.'/acrossyrs/js/reportcharts/vbar.php');
	 }
	
$c++;	 
 
} 
	echo $displaydiv;
	echo '<script>';
	echo 'window.onload = function() {';
	echo $echo;
	echo '}';	
	echo '</script>';
				 
			 }
//INDIVIDUAL GRAPH
			 elseif($_GET['w']=='Individuals'){
				 $condition=date('Y-m-d');
	// echo $condition; exit();
				include_once($path.'/acrossyrs/js/reportcharts/mgraphlabel.php'); 
				include($path.'/acrossyrs/js/reportcharts/includejscharts.php');
				 $echo='';
$sql0='CREATE TEMPORARY TABLE `graphreport11` (
  `ReportID` tinyint(4) NOT NULL AUTO_INCREMENT,
  `ReportTitle` varchar(100) DEFAULT NULL,
  `OtherDesc` varchar(20) NOT NULL,
  `Label` varchar(3000) NOT NULL,
  `xaxis` varchar(25) NOT NULL,
  `yaxis` varchar(25) NOT NULL,
  `min` tinyint(1)DEFAULT NULL,
  `legend1` varchar(55) NOT NULL,
  `legend2` varchar(55) NOT NULL,
  `legend3` varchar(55) NOT NULL,
  PRIMARY KEY (`ReportID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;'; //graph report
$stmt=$link->prepare($sql0);$stmt->execute();

$sql0='CREATE TEMPORARY TABLE `graphboard11` (
  `TxnID` smallint(6) NOT NULL AUTO_INCREMENT,
  `IDNo` smallint(6) DEFAULT NULL,
  `GraphID` tinyint(2) NOT NULL,
  `DataSet1` varchar(2000) NOT NULL,
  `DataSet2` varchar(2000) NOT NULL,
  `DataSet3` varchar(2000) NOT NULL,
  `ReportID` tinyint(4) NOT NULL,
  PRIMARY KEY (`TxnID`),
  UNIQUE KEY `IDNo` (`IDNo`,`GraphID`,`ReportID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1'; //graphboard
$stmt=$link->prepare($sql0);$stmt->execute();

	$bwidth='80%';
	echo''.str_repeat('&nbsp;',5).'Formula: ( Kilos Lost / Starting Weight ) * 100 = Total Weight Loss (%)</br>
	'.str_repeat('&nbsp;',7).'Example:</br>
	'.str_repeat('&nbsp;',9).'(10 kilos / 75 kilos) * 100 = 13.33%</br>
	'.str_repeat('&nbsp;',9).'So, if your starting weight is 75 kilos and you lost 10 kilos, you’ve lost 13.33% of your body weight.
	</br></br>';
	$ReportTitle='ReportTitle="Percent Weight Loss (%)"';
		$graphtitle='ID';
		
		$select=' \'\' as ID';
		$condi='';
		$leftjoin='';
		$sql='Select CONCAT(Nickname,\' \',SurName,\' \',\'(\',dept,\')\') as FullName,bl.IDNo from events_2biggestloser bl left join attend_30currentpositions cp on cp.IDNo=bl.IDNo left join 1employees e on e.IDNo=cp.IDNo';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		$teamarray='';
		$dataset1=''; 	
		$name='\'\'';
			foreach($result as $res){
				$teamarray.="'".$res['FullName'].'\',';
				if($condition>=''.$currentyr.'-04-02'){
					$sqlt='Create temporary table toarrange as select ifnull(truncate(sum(Weight),1),0) as TotalWeight,\'1\' as Col  from events_2biggestloser bl left join events_2biggestlosersub bls on bls.IDNo=bl.IDNo where bl.IDNo=\''.$res['IDNo'].'\' AND month(Date)=3 UNION select ifnull(truncate(sum(Weight),1),0) as TotalWeight,\'2\' as Col  from events_2biggestloser bl left join events_2biggestlosersub bls on bls.IDNo=bl.IDNo where bl.IDNo=\''.$res['IDNo'].'\' AND '.(($condition>=''.$currentyr.'-05-02')?'month(Date)=5':'month(Date)=4').' ';
					// echo $sqlt; exit();
					$stmtt=$link->prepare($sqlt); $stmtt->execute();
					//arrange
					$sqla='Create temporary table arrange as select sum(CASE WHEN Col=1 then TotalWeight end) as FirstCol,sum(CASE WHEN Col=2 then TotalWeight end) as SecondCol from toarrange';
					$stmta=$link->prepare($sqla); $stmta->execute();
					
					
					$sql1='select ifnull(truncate(((FirstCol-SecondCol)/FirstCol)*100,2),0) as TotalWeight from arrange';
				}else{
					$sql1='select \'0\' as TotalWeight';
				}
					$stmt1=$link->query($sql1); $res1=$stmt1->fetch();
					if($stmt1->rowCount()==0){
						$dataset1.='0,';
					
					} else {
						$dataset1.=$res1['TotalWeight'].',';
						// echo $dataset1; exit();
					}
						if($condition>=''.$currentyr.'-04-02'){
					$sqldt='drop temporary table toarrange;';
					$stmtdt=$link->prepare($sqldt); $stmtdt->execute();
					$sqlda='drop temporary table arrange;';
					$stmtda=$link->prepare($sqlda); $stmtda->execute();
						}
					}
				$teamarray=substr($teamarray, 0, -1);
				// echo $teamarray; exit();	
				$dataset1=substr($dataset1, 0, -1);
				// echo $dataset1; exit();
	
	$sqlcsub='INSERT INTO graphboard11 SET GraphID=2,DataSet1="'.$dataset1.'",IDNo='.$name.',ReportID=5;';
	$stmt=$link->prepare($sqlcsub);$stmt->execute(); 
	// echo $sqlcsub; exit();  	
	
	
	

$sqlcmain='INSERT INTO graphreport11 SET xaxis="",legend1="Percent Weight Loss (%)",yaxis="In Percent",min="",'.$ReportTitle.',ReportID=5,Label="'.$teamarray.'"';

// echo $sqlcmain; exit();
$stmt=$link->prepare($sqlcmain);$stmt->execute();
//End

$sql = 'SELECT g.*,gr.*,'.$select.' FROM graphboard11 g '.$leftjoin.' JOIN graphreport11 gr ON g.ReportID=gr.ReportID WHERE g.ReportID IN (5) '.$condi.' ';
// echo $sql; exit();
$stmt=$link->query($sql); $res=$stmt->fetchall();

$sqldrop='DROP TEMPORARY TABLE graphboard11';
$stmt=$link->prepare($sqldrop);$stmt->execute();

$sqldrop='DROP TEMPORARY TABLE graphreport11';
$stmt=$link->prepare($sqldrop);$stmt->execute();
// print_r($res); exit();
$c=1;
$displaydiv=''; $newdiv=''; 

foreach ($res as $field) {
	 if ($field['GraphID']==2){
		include($path.'/acrossyrs/js/reportcharts/vbar.php');
	 }
	
$c++;	 
 
} 
	echo $displaydiv;
	echo '<script>';
	echo 'window.onload = function() {';
	echo $echo;
	echo '}';	
	echo '</script>';
				 
			 }
break;

				  ?>
	              </div>
            </div>
          </div>

       
          

          <!-- Content Row -->
          

        </div>
        <!-- /.container-fluid -->

      </div>
      <!-- End of Main Content -->


    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>


  <!-- Bootstrap core JavaScript-->
  <script src="https://<?php echo $_SERVER['HTTP_HOST'];?>/acrossyrs/js/bootstrapSBADMIN2/vendor/jquery/jquery.min.js"></script>
  <script src="https://<?php echo $_SERVER['HTTP_HOST'];?>/acrossyrs/js/bootstrapSBADMIN2/vendor/https://<?php echo $_SERVER['HTTP_HOST'];?>/acrossyrs/js/bootstrapSBADMIN2/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="https://<?php echo $_SERVER['HTTP_HOST'];?>/acrossyrs/js/bootstrapSBADMIN2/vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="https://<?php echo $_SERVER['HTTP_HOST'];?>/acrossyrs/js/bootstrapSBADMIN2/js/sb-admin-2.min.js"></script>

  <!-- Page level plugins -->
  <script src="https://<?php echo $_SERVER['HTTP_HOST'];?>/acrossyrs/js/bootstrapSBADMIN2/vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="https://<?php echo $_SERVER['HTTP_HOST'];?>/acrossyrs/js/bootstrapSBADMIN2/vendor/datatables/dataTables.bootstrap4.min.js"></script>

  <!-- Page level custom scripts -->
  <script src="https://<?php echo $_SERVER['HTTP_HOST'];?>/acrossyrs/js/bootstrapSBADMIN2/js/demo/datatables-demo.js"></script>
	
</body>

</html>
	<?php
	break;
	
	case'EncodeProcess':
			$idno=comboBoxValue($link, '1employees', 'Concat(Nickname,\' \', SurName)', $_REQUEST['Employee'], 'IDNo');	
			$sqli='INSERT INTO events_2biggestlosersub SET IDNo=\''.$idno.'\',Date=\''.$_POST['Date'].'\',Weight=\''.$_POST['weight'].'\' ';
			// echo $sqli; exit();
			$stmti=$link->prepare($sqli); $stmti->execute();
			header("Location:biggestloser.php?w=Encode");
	break;
	
	case'Delete':
			$txnsubid=intval($_GET['TxnSubId']);	
			$sqli='delete from events_2biggestlosersub where TxnSubId=\''.$txnsubid.'\'';
			// echo $sqli; exit();
			$stmti=$link->prepare($sqli); $stmti->execute();
			header("Location:biggestloser.php?w=Encode");
	break;
	
	case'EditProcess':
			$txnsubid=intval($_GET['TxnSubId']);	
			$idno=comboBoxValue($link, '1employees', 'Concat(Nickname,\' \', SurName)', $_REQUEST['Employee'], 'IDNo');	
			$sql='Update events_2biggestlosersub set IDNo=\''.$idno.'\',Weight=\''.$_POST['Weight'].'\',Date=\''.$_POST['Date'].'\' where TxnSubId=\''.$txnsubid.'\'';
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:biggestloser.php?w=Encode");
	break;
	
	case'join':
	if(isset($_POST['submit'])){
		$sqls='select Team from events_2biggestloser Order By TxnID Desc limit 1';
		$stmts=$link->query($sqls); $results=$stmts->fetch();
		
		if($results['Team']==1){
			$sql='INSERT INTO events_2biggestloser set IDNo=\''.$_SESSION['(ak0)'].'\',Team=2';
			$stmt=$link->prepare($sql); $stmt->execute();
		}elseif($results['Team']==2){
			$sql='INSERT INTO events_2biggestloser set IDNo=\''.$_SESSION['(ak0)'].'\',Team=3';
			$stmt=$link->prepare($sql); $stmt->execute();
		}elseif($results['Team']==3){
			$sql='INSERT INTO events_2biggestloser set IDNo=\''.$_SESSION['(ak0)'].'\',Team=1';
			$stmt=$link->prepare($sql); $stmt->execute();
		}
		else{
		$sql='INSERT INTO events_2biggestloser set IDNo=\''.$_SESSION['(ak0)'].'\',Team=1';
		$stmt=$link->prepare($sql); $stmt->execute();
		}
		header("Location:biggestloser.php?w=Participants&Message=Successfully Joined");
	}else{
		echo 'Bawal na';
		exit();
	}
	break;
}


?>