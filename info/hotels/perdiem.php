<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6482,'1rtcinfo')) {    echo 'No permission'; exit;}
include_once $path.'/acrossyrs/dbinit/userinit.php';
include_once($path.'/acrossyrs/js/includesscripts.php');
?><title>Per Diem</title>
<script>
$(document).ready(function(){
    $('#showRank1to2').click(function() {
      $('.menu1to2').toggle("slide");
    });
    $('#showRank3').click(function() {
      $('.menu3').toggle("slide");
    });
    $('#showRank4to6').click(function() {
      $('.menu4to6').toggle("slide");
    });
});
</script>




<?php

$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
echo '<i><font color=blue>All requests for budgets must be submitted to Accounting Department (with complete information and approvals) 3 or more days BEFORE the trip.</font></i><br><br>';
$multiplierrank1to2=1.3; //rank 1-2
$multiplierrank3to5=1.5; //rank 3-5
$multiplierrank6=1.6; //rank 6
$hotelrank1to2=1200;//superceded on Apr 8 1000;
$hotelrank3to5=1500;//superceded on Apr 8 1200;
$hotelrank6=1500; //for ranks 6
$techtrainorperdiem='<br>Technical Trainor: Per Diem Allowance = 250<br>Lodging may be provided, and will be treated on a case-to-case basis.<br>';
$mindanaoauditor='<br>Mindanao Auditor: Per Diem Allowance = 250<br>If no lodging provided, hotel budget is 800.<br>';
$companydriver='<br>Company Driver: Hotel = 600 & Per Diem Allowance = 250<br>';
$sqlhotel='';
if (!allowedToOpen(64821,'1rtcinfo')) {
   goto perdiemperperson;
} else {
	$sqlpos1to2='SELECT AllowedPos FROM permissions_2allprocesses WHERE ProcessID=64822';
    $stmtpos1to2=$link->query($sqlpos1to2);
	$respos1to2=$stmtpos1to2->fetch();
	
	$sqlpos3to5='SELECT AllowedPos FROM permissions_2allprocesses WHERE ProcessID=64823';
    $stmtpos3to5=$link->query($sqlpos3to5);
	$respos3to5=$stmtpos3to5->fetch();
	
	$sqlpos6andup='SELECT AllowedPos FROM permissions_2allprocesses WHERE ProcessID=64825';
    $stmtpos6=$link->query($sqlpos6andup);
	$respos6=$stmtpos6->fetch();
	
	$positionin=" AND PositionID IN (".$respos1to2['AllowedPos'].",".$respos3to5['AllowedPos'].",".$respos6['AllowedPos'].")";
	
	//rank 1-2
	$sqlrank1to2='SELECT `Position` FROM attend_0positions p WHERE PositionID IN ('.$respos1to2['AllowedPos'].')'.$positionin.' ORDER BY Position;';
    $stmt1to2=$link->query($sqlrank1to2);
	$result1to2=$stmt1to2->fetchAll();
	$div1to2='';
	foreach($result1to2 AS $res1to2){
		$div1to2.='<li>'.$res1to2['Position'].'</li>';
	}
	//rank 3-5
	$sqlrank3='SELECT `Position` FROM attend_0positions p WHERE PositionID IN ('.$respos3to5['AllowedPos'].',140,9)'.$positionin.' ORDER BY Position;';
    $stmt3=$link->query($sqlrank3);
	$result3=$stmt3->fetchAll();
	$div3='';
	foreach($result3 AS $res3){
		$div3.='<li>'.$res3['Position'].'</li>';
	}
	//rank 6
	$sqlrank4to6='SELECT `Position` FROM attend_0positions p WHERE PositionID IN ('.$respos6['AllowedPos'].')'.$positionin.' ORDER BY Position;';
    $stmt4to6=$link->query($sqlrank4to6);
	$result4to6=$stmt4to6->fetchAll();
	$div4to6='';
	foreach($result4to6 AS $res4to6){
		$div4to6.='<li>'.$res4to6['Position'].'</li>';
	}
	$colorcount=0;
	$rcolor[0]=(!isset($_REQUEST['print'])?(isset($color1)?$color1:"E6FFCC"):"FFFFFF");
	$rcolor[1]=isset($color2)?$color2:"FFFFFF";
		
	$sqltable='SELECT CONCAT(Region,\' - \',Area) as Place,max(Round((TotalMinWage)*('.$multiplierrank1to2.'),0)) AS JobClass1to2,max(Round((TotalMinWage)*('.$multiplierrank3to5.'),0)) AS JobClass3to5,max(Round((TotalMinWage)*('.$multiplierrank6.'),0)) AS JobClass6andup FROM 1_gamit.payroll_4wageorders wo left join `1_gamit`.`payroll_0regionsminwageareas` rmwa on rmwa.MinWageAreaID=wo.MinWageAreaID group by wo.MinWageAreaID UNION ALL SELECT "Hotel Budget" AS Place,'.$hotelrank1to2.','.$hotelrank3to5.','.$hotelrank6.';';
	$stmttable=$link->query($sqltable);
	$resulttable=$stmttable->fetchAll();
	$tabledata=''; $cntdata=0;
	foreach ($resulttable AS $restable){
		$colorcount++;
		$tabledata.='<tr bgcolor='. $rcolor[$colorcount%2].'><td>'.$restable['Place'].'</td><td>'.$restable['JobClass1to2'].'</td><td>'.$restable['JobClass3to5'].'</td><td>'.$restable['JobClass6andup'].'</td></tr>';
		$cntdata++;
	}
	?>
	
	
<table style="border:1px solid;font-size:10pt;">
<tr valign="bottom" align="left">
	<td><b>Place</b></td>
	<td align="left" style="width:250px;">
		<div class="menu1to2" style="display: none;">
			<ol style="text-align:left;font-size:10pt;">
			<b>Job Class 1-2</b><br>
			<?php echo $div1to2;?>
			</ol>
		</div>
		<div id="showRank1to2" style="padding:3px;background-color:lightblue;text-align:center;border-radius:5px;">Job Class 1-2</div>
	</td>
	<td align="left" style="width:250px;">
		<div class="menu3" style="display: none;">
			<ol style="text-align:left;font-size:10pt;">
			<b>Job Class 3-5</b><br>
			<?php echo $div3;?>
			</ol>
		</div>
		<div id="showRank3" style="padding:3px;background-color:lightblue;text-align:center;border-radius:5px;">Job Class 3-5</div>
	</td>
	<td align="left" style="width:250px;">
		<div class="menu4to6" style="display: none;">
			<ol style="text-align:left;font-size:10pt;">
			<b>Job Class 6 and up</b><br>
			<?php echo $div4to6;?>
			</ol>
		</div>
		<div id="showRank4to6" style="padding:3px;background-color:lightblue;text-align:center;border-radius:5px;">Job Class 6 and up</div>
	</td>
</tr>
<?php echo $tabledata;?>
</table>
<?php echo $cntdata.' records<br>'; echo '<center>'.$techtrainorperdiem.$mindanaoauditor.$companydriver.'</center>'; ?>
	<?php
    
		
	
   goto endbudget; 
}

perdiemperperson:
    if(allowedToOpen(64822,'1rtcinfo')) { $multiplier=$multiplierrank1to2; $hotelbudget=$hotelrank1to2; } 
    elseif((allowedToOpen(64823,'1rtcinfo')) OR ($_SESSION['&pos']==140) OR ($_SESSION['&pos']==9)) { $multiplier=$multiplierrank3to5; $hotelbudget=$hotelrank3to5; } 
    elseif(allowedToOpen(64825,'1rtcinfo')) { $multiplier=$multiplierrank6; $hotelbudget=$hotelrank6; } else { $multiplier=0; $hotelbudget=0;}
   
if ($multiplier<>0){
	

$sql0='CREATE TEMPORARY TABLE currentrates AS SELECT MAX(DateEffective) AS MaxDate, CONCAT(Region,\' - \',Area) as Place FROM 1_gamit.payroll_4wageorders wo left join `1_gamit`.`payroll_0regionsminwageareas` rmwa on rmwa.MinWageAreaID=wo.MinWageAreaID GROUP BY Place;';
    $stmt=$link->prepare($sql0); $stmt->execute();
$sql='select CONCAT(Region,\' - \',Area) as Place, Round((TotalMinWage)*'.$multiplier.',0) as PerDiem from 1_gamit.payroll_4wageorders wo left join `1_gamit`.`payroll_0regionsminwageareas` rmwa on rmwa.MinWageAreaID=wo.MinWageAreaID JOIN currentrates cr ON (CONCAT(Region,\' - \',Area)=cr.Place AND wo.DateEffective=cr.MaxDate) union all Select "Hotel", '.$hotelbudget;
// echo $sql; exit();
$stmt=$link->query($sql);


$result=$stmt->fetchAll();
$perdiem='<table><tr><td>Place</td><td>Per Diem</td></tr>';
foreach ($result as $area){
    $perdiem=$perdiem.'<tr><td>'.$area['Place'].'</td><td>'.$area['PerDiem'].'</td></tr>';
}
    $perdiem=$perdiem.'</table><br><br>';
    
} else {
    $perdiem=$techtrainorperdiem;
    
}
// echo $sql; exit();
echo $perdiem;
endbudget:
?></center>
<div id="content">
   <h4>Per Diem Allowance</h4>
<p>1.&nbsp &nbsp Per Diem Allowance shall cover meals and transportation while at branches outside the employee's homebase.  <i>There is no required liquidation for this</i>, but any official receipt (for food, gasoline, supplies, etc.) in the name of the Company may be requested to cover the expense.   </p>

<p>&nbsp &nbsp &nbsp &nbsp There is no allowance for weekends and holidays when the employee will not work. </p>

<p>2.&nbsp &nbsp Expenses incurred outside of meals and transportation must have corresponding receipts and must be surrendered to the Accounting Department.
    &nbsp &nbsp This includes meals for branch heads (P100 per person per lunch) <u>when on RRR trips</u>.</p>

<p>3.&nbsp &nbsp When an officer accompanies the employees on trips, he/she may pay for all meals and transportation, thus the per diem allowance will not be given to the employee.  On meals where the officer is not present, each meal will have a budget of per diem allowance divided by 3.</p>

<p>4.&nbsp &nbsp On the days with per diem allowance/budget, travel time will not be counted as part of overtime, if any. </p>

<!-- REPLACED THIS:
<p>4.&nbsp &nbsp On the days of travel, the allowance will be prorated depending on the arrival time for the first day and the departure time on the last day.  (Arrival and Departure points are the provincial destination, not the employee's homebase.)  Each meal will be valued at per diem allowance divided by 3. Cut-off times are: </p>
<p>
<table><tr><td>Time of Arrival/Departure </td><td>Day of Arrival</td><td>Day of Departure</td></tr>
   <tr><td>10 a.m. </td><td>No Breakfast</td><td>With Breakfast</td></tr>
   <tr><td>3 p.m.</td><td>No Lunch</td><td>With Lunch</td></tr>
   <tr><td>10 p.m.</td><td>No Dinner</td><td>With Dinner</td></tr></table></p>-->

   
<h4>Hotel Budget</h4> <!-- //superceded on Apr 8, made 30% 5o 50% -->
<p>1.&nbsp &nbsp When two or three employees of the same gender travel together, the total budget will the <u>higher budget</u> among their assigned budgets, <u>plus 50%</u>.  They may opt to stay in a bigger room, or in separate rooms, provided the total daily rate is within the budget.   </p>

<p>&nbsp &nbsp &nbsp &nbsp Members of the Management Committee may choose to get his/her own room, thus budget will be considered separate.</p>

<p>2.&nbsp &nbsp If the travel period in one place is more than one week, the employee may be asked to get an apartment on short-term lease at a much less cost to the Company.</p>
<p>3.&nbsp &nbsp Hotel accommodations should not include breakfast, unless only one price if with or without breakfast.</p>
<p>4.&nbsp &nbsp If feasible, schedule your departures from the provinces in the afternoon or evening to save on hotel expenses.</p>
<p>5.&nbsp &nbsp All hotel receipts must be submitted to the Accounting Department within 48 hours of arrival at homebase.</p>
</div id="content"><center>