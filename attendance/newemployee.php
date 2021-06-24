<?php 
$path=$_SERVER['DOCUMENT_ROOT']; 
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
$showbranches=false;
include_once('../switchboard/contents.php');
if (!allowedToOpen(array(624,62411),'1rtc')){ echo 'No permission'; exit;}
include_once($path.'/acrossyrs/js/includesscripts.php');

include_once('../backendphp/layout/linkstyle.php');


?>

<br>
<a id='link' href="newemployee.php">Add New Employee</a> 
<a id='link' href="newemployee.php?w=ForApprovalList">For Approval</a>
<a id='link' href="newemployee.php?w=ForSalaryRate">For Encoding Salary Rate</a> 
<br><br>
<style>

.tabs {
	width:100%;
	display:inline-block;
}

	.tab-links:after {
	display:block;
	clear:both;
	content:'';
}

.tab-links li {
	margin:0px 5px;
	float:left;
	list-style:none;
}

.tab-links a {
	padding:9px 15px;
	display:inline-block;
	border-radius:3px 3px 0px 0px;
	background:#7FB5DA;
	font-size:16px;
	font-weight:600;
	color:#4c4c4c;
	transition:all linear 0.15s;
	text-decoration: none;
	width:180px;
	text-align:center;
}

.tab-links a:hover {
	background:#a7cce5;
	text-decoration:none;
}

li.active a, li.active a:hover {
	color:#4c4c4c;
	width:180px;
	text-align:center;
	background:#fff;
}

.tab-content {
	padding:15px;
	border-radius:3px;
	box-shadow:-1px 1px 1px rgba(0,0,0,0.15);
	background:#fff;
}

.tab {
	display:none;
}

.tab.active {
	display:block;
}
</style>

<?php

$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    $liststoshow=array('yesno','civilstatus','companynumbers','weekdays','withsat','branches','directoragency', 'zipplaces', 'barangayortown', 'cityorprovince');//'taxclass',
    echo comboBox($link,'SELECT "Male" AS Gender, 1 AS GenderValue UNION SELECT "Female" AS GenderValue, 0 AS Gender','Gender','GenderValue','gender');
	echo comboBox($link,'SELECT IDNo, CONCAT(Fullname," (",Branch,")") AS NameBranch FROM attend_30currentpositions ORDER BY JobLevelID DESC;','NameBranch','IDNo','supid');
	echo comboBox($link,'SELECT PositionID, Position FROM attend_1positions ORDER BY Position;','Position','PositionID','positions');
	   include_once "../generalinfo/lists.inc";
foreach ($liststoshow as $list){
renderlist($list);   
$stmt0=$link->query('Select (IDNo)+1 AS NewIDNo FROM `1_gamit`.`0idinfo` UNION Select (IDNo)+1 AS NewIDNo FROM `1employeesforapproval` ORDER BY NewIDNo DESC LIMIT 1;');
$res=$stmt0->fetch();
$newidno=$res['NewIDNo'];
}
$whichqry=(!isset($_GET['w'])?'AddNewEmployee':$_GET['w']);

switch ($whichqry){
case 'AddNewEmployee':
	if(isset($_GET['IDNo'])){
		$title='Employee for Approval';
		$actionlink='UpdateHoldingTable';
		$sqld='SELECT * FROM `1employeesforapproval` WHERE IDNo='.intval($_GET['IDNo']);
		$stmtd=$link->query($sqld);  $resultd=$stmtd->fetch();
		$IDNo=$resultd['IDNo'];
		$Nickname=$resultd['Nickname'];
		$SurName=$resultd['SurName'];
		$FirstName=$resultd['FirstName'];
		$MiddleName=$resultd['MiddleName'];
		$Gender=$resultd['Gender'];
		$UBPATM=$resultd['UBPATM'];
		$WithLeaves=$resultd['WithLeaves'];
		$DirectOrAgency=$resultd['DirectOrAgency'];
		$PositionID=$resultd['PositionID'];
		$Supervisor=$resultd['Supervisor'];
		$WithSat=$resultd['WithSat'];
		$RestDay=$resultd['RestDay'];
		$ResTel=$resultd['ResTel'];
		$MobileNo=$resultd['MobileNo'];
		$GCashMobileNumber=$resultd['GCashMobileNumber'];
		$Email=$resultd['Email'];
		$DateHired=$resultd['DateHired'];
		$BranchNo=$resultd['BranchNo'];
		$Birthdate=$resultd['Birthdate'];
		$PrevEmployerNetTaxable=$resultd['PrevEmployerNetTaxable'];
		$PrevEmployerTaxWHeld=$resultd['PrevEmployerTaxWHeld'];
		$StreetAddress=$resultd['StreetAddress'];
		$ZipCode=$resultd['ZipCode'];
		$StreetAddress_Provincial=$resultd['StreetAddress_Provincial'];
		$ZipCode_Provincial=$resultd['ZipCode_Provincial'];
		$RCompanyNo=$resultd['RCompanyNo'];
		$ReferredBy=$resultd['ReferredBy'];
		$SSSNo=$resultd['SSSNo'];
		$PHICNo=$resultd['PHICNo'];
		$PAGIBIGNo=$resultd['PAGIBIGNo'];
		$TIN=$resultd['TIN'];
		$NoofDependents=$resultd['NoofDependents'];
		$ICEPerson=$resultd['ICEPerson'];
		$RelationshiptoEmployee=$resultd['RelationshiptoEmployee'];
		$ICEContactInfo=$resultd['ICEContactInfo'];
		$ICEAddress=$resultd['ICEAddress'];
		$PlaceOfBirth=$resultd['PlaceOfBirth'];
		$MMName=$resultd['MMName'];
		$CivilStatus=$resultd['CivilStatus'];
		$RTCEmail=$resultd['RTCEmail'];
		$SpouseName=$resultd['SpouseName'];
		$SpouseBirthdate=$resultd['SpouseBirthdate'];
		$ChildName1=$resultd['ChildName1'];
		$ChildName2=$resultd['ChildName2'];
		$ChildName3=$resultd['ChildName3'];
		$ChildName4=$resultd['ChildName4'];
		$ChildBirthdate1=$resultd['ChildBirthdate1'];
		$ChildBirthdate2=$resultd['ChildBirthdate2'];
		$ChildBirthdate3=$resultd['ChildBirthdate3'];
		$ChildBirthdate4=$resultd['ChildBirthdate4'];
		if (allowedToOpen(62411,'1rtc')){
			$button='<input type="submit" style="width:100%;padding:10px;background-color:maroon;border-radius:10px;color:white;font-size:12pt;font-weight:bold;" value="Approve Employee" OnClick="return confirm(\'Are you sure?\');">';
			$tabcolor='maroon';
		} else {
			$button='<input type="submit" style="width:100%;padding:10px;background-color:green;border-radius:10px;color:white;font-size:12pt;font-weight:bold;" value="Update Employee">';
			$tabcolor='green';
		}
	} else {
		$tabcolor='blue';
		$title='Add New Employee';
		$actionlink='AddtoHoldingTable';
		$IDNo='';
		$Nickname='';
		$SurName='';
		$FirstName='';
		$MiddleName='';
		$Gender='';
		$UBPATM='';
		$WithLeaves=0;
		$DirectOrAgency=0;
		$PositionID='';
		$Supervisor='';
		$WithSat='';
		$RestDay='';
		$ResTel='';
		$MobileNo='';
		$GCashMobileNumber='';
		$Email='';
		$DateHired='';
		$BranchNo='';
		$Birthdate='';
		$PrevEmployerNetTaxable='';
		$PrevEmployerTaxWHeld='';
		$StreetAddress='';
		$ZipCode='';
		$StreetAddress_Provincial='';
		$ZipCode_Provincial='';
		$RCompanyNo='';
		$ReferredBy='';
		$SSSNo='';
		$PHICNo='';
		$PAGIBIGNo='';
		$TIN='';
		$NoofDependents=0;
		$ICEPerson='';
		$RelationshiptoEmployee='';
		$ICEContactInfo='';
		$ICEAddress='';
		$PlaceOfBirth='';
		$MMName='';
		$CivilStatus='';
		$RTCEmail='@1rotary.com.ph';
		$SpouseName='';
		$SpouseBirthdate='';
		$ChildName1='';
		$ChildName2='';
		$ChildName3='';
		$ChildName4='';
		$ChildBirthdate1='';
		$ChildBirthdate2='';
		$ChildBirthdate3='';
		$ChildBirthdate4='';
		$button='<input type="submit" style="width:100%;padding:10px;background-color:blue;border-radius:10px;color:white;font-size:12pt;font-weight:bold;" value="Add New Employee">';
	}
	 echo '<title>'.$title.'</title>';
		echo '<br><br>';
		echo '<div class="tabs">
		<ul class="tab-links">
			<li class="active"><a href="#tab1" style="color:'.$tabcolor.';">'.$title.'</a></li>
		</ul>
		<br>
		
		<div class="tab-content">
			<div id="tab1" class="tab active">';
			
			if (allowedToOpen(62411,'1rtc')){
			?>
				<form action="praddemployee.php?calledfrom=1&action_token=<?php echo $_SESSION['action_token'];?>" method="POST">
			<?php } else { ?>
				<form action="newemployee.php?w=<?php echo $actionlink;?>" method="POST">
			<?php } ?>
			<div style="background-color:white;padding:20px;">
<?php 
if(!isset($_GET['IDNo'])){
?>
    Next available IDNo: <?php echo $newidno;?><br><br>
	<?php } ?>
<fieldset style="padding:6px;background-color:yellowgreen;">
<legend style="background-color:yellow;">
	<span style="color:blue;"><b>&nbsp;EMPLOYEE INFORMATION&nbsp;</b></span>
</legend>
<div>
<div style="float:left;">
 <div style="margin-bottom:3px;"><div><label>IDNo <span>*</span></label><input type="text" name="IDNo" value="<?php echo $IDNo; ?>" size="15" maxlength="255" autocomplete="off" required <?php if (isset($_GET['IDNo'])) { echo 'readonly'; }?>/></div></div>
 <div style="margin-bottom:3px;"><div><label>Nickname <span>*</span></label><input type="text" name="Nickname" value="<?php echo $Nickname; ?>" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>SurName <span>*</span></label><input type="text" name="SurName" value="<?php echo $SurName; ?>" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>FirstName <span>*</span></label><input type="text" name="FirstName" value="<?php echo $FirstName; ?>" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>MiddleName <span>*</span></label><input type="text" name="MiddleName" value="<?php echo $MiddleName; ?>" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>Gender <span>*</span></label><input type="text" name="Gender" value="<?php echo $Gender; ?>" size="15" maxlength="255" autocomplete="off" list="gender" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>UBPATM </label><input type="text" name="UBPATM" value="<?php echo $UBPATM; ?>" size="25" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>WithLeaves <span>*</span></label><input type="text" name="WithLeaves" value="<?php echo $WithLeaves; ?>" size="15" maxlength="255" autocomplete="off" list="yesno" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>DirectOrAgency <span>*</span></label><input type="text" name="DirectOrAgency" value="<?php echo $DirectOrAgency; ?>" size="15" maxlength="255" autocomplete="off" list="directoragency" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>PositionID <span>*</span></label><input type="text" name="PositionID" value="<?php echo $PositionID; ?>" size="15" maxlength="255" autocomplete="off" list="positions" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>Supervisor <span>*</span></label><input type="text" name="Supervisor" value="<?php echo $Supervisor; ?>" size="15" maxlength="255" autocomplete="off" list="supid" required/></div></div>
 </div>
 <div style="margin-left:40%;">
 <div style="margin-bottom:3px;"><div><label>WithSat <span>*</span></label><input type="text" name="WithSat" value="<?php echo $WithSat; ?>" size="15" maxlength="255" autocomplete="off" list="withsat" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>RestDay <span>*</span></label><input type="text" name="RestDay" value="<?php echo $RestDay; ?>" size="15" maxlength="255" autocomplete="off" list="weekdays" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>ResTel </label><input type="text" name="ResTel" value="<?php echo $ResTel; ?>" size="15" maxlength="255" autocomplete="off"/></div></div>
 <div style="margin-bottom:3px;"><div><label>MobileNo </label><input type="text" name="MobileNo" value="<?php echo $MobileNo; ?>" size="15" maxlength="255" autocomplete="off"/></div></div>
 <div style="margin-bottom:3px;"><div><label>GCashMobileNumber <i>(n/a if none)</i> <span>*</span></label><input type="text" name="GCashMobileNumber" value="<?php echo $GCashMobileNumber; ?>" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>Email </label><input type="text" name="Email" value="<?php echo $Email; ?>" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>DateHired <span>*</span></label><input type="date" name="DateHired" value="<?php echo $DateHired; ?>" size="25" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>BranchNo <span>*</span></label><input type="text" name="BranchNo" value="<?php echo $BranchNo; ?>" size="15" maxlength="255" autocomplete="off" list="branches" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>BirthDate <span>*</span></label><input type="date" name="Birthdate" value="<?php echo $Birthdate; ?>" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>TOTAL Net Taxable Income from all previous employers THIS YEAR (BIR 2316 no. 55) <span>*</span></label><input type="text" name="PrevEmployerNetTaxable" value="<?php echo $PrevEmployerNetTaxable; ?>" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>TOTAL Tax Withheld from all previous employers THIS YEAR (BIR 2316 no. 30A) <span>*</span></label><input type="text" name="PrevEmployerTaxWHeld" value="<?php echo $PrevEmployerTaxWHeld; ?>" size="15" maxlength="255" autocomplete="off" required/>
         
         <input type="hidden" name="SLBalDecCutoff" value="0"><input type="hidden" name="PaidSLBenefit" value="0"><input type="hidden" name="EncodedByNo" value="<?php echo $_SESSION['(ak0)'];?>"><input type="hidden" name="action_token" value="<?php echo $_SESSION['action_token'];?>"></div></div>
 </div>
 </div>
</fieldset>
<br>
<fieldset style="padding:6px;background-color:yellowgreen;">
<legend style="background-color:yellow;">
	<span style="color:blue;"><b>&nbsp;OTHER INFORMATION&nbsp;</b></span>
</legend>
<div>
<div style="float:left;">

 <div style="margin-bottom:3px;"><div><label>StreetAddress_Present <span>*</span></label><input type="text" name="StreetAddress" value="<?php echo $StreetAddress; ?>" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>ZipCode_Present <span>*</span></label><input type="text" name="ZipCode" value="<?php echo $ZipCode; ?>" size="25" maxlength="255" autocomplete="off" list="zipplaces" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>StreetAddress_Provincial <span>*</span></label><input type="text" name="StreetAddress_Provincial" value="<?php echo $StreetAddress_Provincial; ?>" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>ZipCode_Provincial <span>*</span></label><input type="text" name="ZipCode_Provincial" value="<?php echo $ZipCode_Provincial; ?>" size="25" maxlength="255" autocomplete="off" list="zipplaces" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>RCompanyNo <span>*</span></label><input type="text" name="RCompanyNo" value="<?php echo $RCompanyNo; ?>" size="15" maxlength="255" autocomplete="off" list="companynumbers" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>ReferredBy </label><input type="text" name="ReferredBy" value="<?php echo $ReferredBy; ?>" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>SSSNo <span>*</span></label><input type="text" name="SSSNo" value="<?php echo $SSSNo; ?>" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>PHICNo <span>*</span></label><input type="text" name="PHICNo" value="<?php echo $PHICNo; ?>" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>PAGIBIGNo <span>*</span></label><input type="text" name="PAGIBIGNo" value="<?php echo $PAGIBIGNo; ?>" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>TIN <span>*</span></label><input type="text" name="TIN" value="<?php echo $TIN; ?>" size="25" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>NoofDependents <span>*</span></label><input type="text" name="NoofDependents" value="<?php echo $NoofDependents; ?>" size="15" maxlength="255" autocomplete="off" required/></div></div>
<div style="margin-bottom:3px;"><div><label>ICEPerson </label><input type="text" name="ICEPerson" value="<?php echo $ICEPerson; ?>" size="15" maxlength="255" autocomplete="off"/></div></div>
 <div style="margin-bottom:3px;"><div><label>RelationshiptoEmployee <span>*</span></label><input type="text" name="RelationshiptoEmployee" value="<?php echo $RelationshiptoEmployee; ?>" size="15" maxlength="255" autocomplete="off"/></div></div>
 <div style="margin-bottom:3px;"><div><label>ICEContactInfo <span>*</span></label><input type="text" name="ICEContactInfo" value="<?php echo $ICEContactInfo; ?>" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>ICEAddress </label><input type="text" name="ICEAddress" value="<?php echo $ICEAddress; ?>" size="15" maxlength="255" autocomplete="off" /></div></div>
 </div>
 <div style="margin-left:40%;">
 
 <div style="margin-bottom:3px;"><div><label>Place Of Birth <span>*</span></label><input type="text" name="PlaceOfBirth" value="<?php echo $PlaceOfBirth; ?>" size="25" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>Mother's Maiden Name <span>*</span></label><input type="text" name="MMName" value="<?php echo $MMName; ?>" size="25" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>CivilStatus <span>*</span></label><input type="text" name="CivilStatus" value="<?php echo $CivilStatus; ?>" size="15" maxlength="255" autocomplete="off" list="civilstatus" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>AssignedEmail </label><input type="text" name="RTCEmail" size="25" maxlength="255" autocomplete="off" value="<?php echo $RTCEmail; ?>" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>SpouseName </label><input type="text" name="SpouseName" value="<?php echo $SpouseName; ?>" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>SpouseBirthdate </label><input type="date" name="SpouseBirthdate" value="<?php echo $SpouseBirthdate; ?>" size="15" maxlength="255" autocomplete="off" /></div></div>
 
 <div style="margin-bottom:3px;"><div><label>ChildName1 </label><input type="text" name="ChildName1" value="<?php echo $ChildName1; ?>" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>ChildName2 </label><input type="text" name="ChildName2" value="<?php echo $ChildName2; ?>" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>ChildName3 </label><input type="text" name="ChildName3" value="<?php echo $ChildName3; ?>" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>ChildName4 </label><input type="text" name="ChildName4" value="<?php echo $ChildName4; ?>" size="15" maxlength="255" autocomplete="off" /></div></div>
 
 
 <div style="margin-bottom:3px;"><div><label>ChildBirthdate1 </label><input type="date" name="ChildBirthdate1" value="<?php echo $ChildBirthdate1; ?>" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>ChildBirthdate2 </label><input type="date" name="ChildBirthdate2" value="<?php echo $ChildBirthdate2; ?>" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>ChildBirthdate3 </label><input type="date" name="ChildBirthdate3" value="<?php echo $ChildBirthdate3; ?>" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>ChildBirthdate4 </label><input type="date" name="ChildBirthdate4" value="<?php echo $ChildBirthdate4; ?>" size="15" maxlength="255" autocomplete="off" /></div></div>
 
 
 </div>
 
 
 </div>
</fieldset>


</div>
			<?php
			
			echo '</div>';
			?>
			
				
			
			<?php
			
			echo '

			
		</div>
	</div>';
	
	echo $button;
	
	echo '</form>';
	
  break;


  case 'AddtoHoldingTable':
case 'UpdateHoldingTable':
	if($whichqry=='AddtoHoldingTable'){
		$condition='';
		$sqlmain='INSERT INTO ';
	} else {
		$condition=' WHERE IDNo='.$_POST['IDNo'];
		$sqlmain='UPDATE ';
	}

	$columnstoadd=array('IDNo','Nickname','SurName','FirstName','MiddleName','Gender','UBPATM','WithLeaves','DirectOrAgency','PositionID','Supervisor','WithSat','RestDay','ResTel','MobileNo','GCashMobileNumber','Email','DateHired','BranchNo','Birthdate','PrevEmployerNetTaxable','PrevEmployerTaxWHeld','EncodedByNo','StreetAddress','ZipCode','StreetAddress_Provincial','ZipCode_Provincial','RCompanyNo','ReferredBy','SSSNo','PHICNo','PAGIBIGNo','TIN','NoofDependents','ICEPerson','RelationshiptoEmployee','ICEContactInfo','ICEAddress','PlaceOfBirth','MMName','CivilStatus','RTCEmail','SpouseName','SpouseBirthdate','ChildName1','ChildName2','ChildName3','ChildName4','ChildBirthdate1','ChildBirthdate2','ChildBirthdate3','ChildBirthdate4'); 
	
	$sql='';
    foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
	$sql=$sqlmain.' 1employeesforapproval SET '.$sql.' TimeStamp=NOW() '.$condition;

	$stmt = $link->prepare($sql);
	$stmt->execute();
	header("Location:newemployee.php?w=AddNewEmployee&IDNo=".$_POST['IDNo']);
break;

case 'ForApprovalList':
	$sql='SELECT IDNo,Branch,Position,Firstname,MiddleName,SurName,Position,Branch FROM 1employeesforapproval ea LEFT JOIN attend_1positions p ON ea.PositionID=p.PositionID LEFT JOIN 1branches b ON ea.BranchNo=b.BranchNo';
	$columnnameslist=array( 'Branch', 'IDNo', 'Firstname', 'MiddleName', 'SurName', 'Position');

	
		$title='Employees For Approval';
		$delprocess='newemployee.php?w=Delete&IDNo=';
		$editprocess='newemployee.php?w=AddNewEmployee&IDNo='; $editprocesslabel='Lookup';
	


	$formdesc=''; $txnidname='IDNo';
	$columnnames=$columnnameslist;

	include('../backendphp/layout/displayastablenosort.php'); 

break;

case 'ForSalaryRate':
$sql='SELECT IDNo,FullName,IF(deptid IN (2,10),Branch,dept) AS Branch,Position FROM attend_30currentpositions WHERE IDNo NOT IN (SELECT DISTINCT(IDNo) FROM payroll_22rates) ORDER BY Branch;';
$columnnameslist=array('IDNo','Branch','FullName','Position');
$title='For Encoding Salary Rate';
	

	$formdesc='<br></i><h3><a href="../payroll/addentry.php?w=Rates" target="_blank">Add New Salary Rate</a></h3><i>'; $txnidname='IDNo';
	$columnnames=$columnnameslist;
	include('../backendphp/layout/displayastablenosort.php'); 

	break;

	case 'Delete':
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$idno=intval($_GET['IDNo']);
			$sql='DELETE FROM `1employeesforapproval` WHERE IDNo='.$idno.' AND IDNo IN (SELECT IDNo FROM 1employees WHERE IDNo='.$idno.')';
			
			$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:newemployee.php?w=ForApprovalList");
		break;
  }
  noform:
     
     $link=null; $stmt=null; 
     $stmt0=null;
?>
