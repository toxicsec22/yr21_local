<?php
session_start();
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db('2021_1rtc',0):$link;
?>
<html>
<style>
#table2 {
  border-collapse: collapse;
  font-size:10pt;
  width: auto;
}

#table2 td, #table2 th {
  border: 1px solid black;
  padding: 3px;
}
</style>
<?php $title="Holiday"; ?>
<title><?php echo $title; ?></title>
<body style="font-family:Arial;background-color:#afcecf;">
<div style="border:1px solid blue;padding:10px;width:70%;margin-left:15%;background-color:#fff;font-size:10.5pt;">
<?php 
		echo '<a href="../index.php">Back to Home</a>'; 
	$sql='select monthname(DateToday) as `monthname`,MONTH(DateToday) as `Month`,year(DateToday) as `year`,dayname(DateToday) as `dayname`,day(DateToday) as `day`,DateToday,RemarksOnDates,Details,Details,ad.TypeOfDayNo from attend_2attendancedates ad left join attend_0typeofday td on td.TypeOfDayNo=ad.TypeOfDayNo  WHERE DateToday=\''.$_GET['DateToday'].'\' and ad.TypeOfDayNo=\''.$_GET['TypeOfDayNo'].'\'';
	
	$stmt=$link->query($sql);$result=$stmt->fetch();
	$monthname=$result['monthname'];
        $month=$result['Month'];
        $day=$result['day'];
	echo'<p>Please be advised that <b>'.$monthname.' '.$day.', '.$result['year'].', '.$result['dayname'].'</b> has been declared a <b> '.$result['Details'].'</b> in the celebration of <b>'.$result['RemarksOnDates'].'</b>.</p>';
	
	if($_GET['TypeOfDayNo']==2){
	echo '<p>Treatment of attendance on the said date is as follows:</p>
		<table id="table2">
		<tr><th>When an employee..</th><th>Then..</th></tr>
		<tr><td>Reports for work</td><td>Will be paid 200%</td></tr>
		<tr><td>Is absent</td><td>Will be paid 100%</td></tr>
		<tr><td>Is absent <b>before</b> '.$monthname.' '.$day.'</td><td>NO HOLIDAY PAY</td></tr>
		</table></br></br>
		
		<u><b>Work Schedule</b></u></br></br>';
        if(($month==12 and $day==25) or ($month==1 and $day==1) or ($month==11 and $day==1) or (strpos($result['RemarksOnDates'],'Good Friday'))) { echo 
		
		'
		Branches - NO WORK</br>'; } else { echo 'Branches - With Work</br>';}
	
        
	echo '	Office & Warehouse - NO WORK</br></br>
		
		';
	}else{
		echo '<p>Treatment of attendance on the said date is as follows:</p>
		<table id="table2">
		<tr><th>When an employee..</th><th>Then..</th></tr>
		<tr><td>Reports for work</td><td>Will be paid additional 30%</td></tr>
		<tr><td>Is absent (daily rate employees)</td><td>No Work No Pay</td></tr>
                <tr><td>Is absent (monthly rate employees)</td><td>With Holiday Pay</td></tr>
		<tr><td>Is absent <b>before</b> '.$monthname.' '.$day.'</td><td>NO HOLIDAY PAY</td></tr>
		</table></br></br>
		
		<u><b>Work Schedule</b></u></br></br>';
		if(($month==12 and $day==25) or ($month==1 and $day==1) or ($month==11 and $day==1) or (strpos($result['RemarksOnDates'],'Good Friday'))) { echo 
		
		'
		Branches - NO WORK</br>'; } else { echo 'Branches - With Work</br>'; }
		// Branches - With Work</br>
		echo '
		Office & Warehouse - NO WORK</br></br>
		';
	}
	
	
 ?>



</div>
</body>
</html>



