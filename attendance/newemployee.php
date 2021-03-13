<?php $path=$_SERVER['DOCUMENT_ROOT']; 
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
$showbranches=false;
include_once('../switchboard/contents.php');
if (!allowedToOpen(624,'1rtc')){ echo 'No permission'; exit;}
include_once($path.'/acrossyrs/js/includesscripts.php');
?>

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
 echo '<title>Add New Employee</title>';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    $liststoshow=array('yesno','civilstatus','companynumbers','weekdays','withsat','branches','directoragency', 'zipplaces', 'barangayortown', 'cityorprovince');//'taxclass',
    echo comboBox($link,'SELECT "Male" AS Gender, 1 AS GenderValue UNION SELECT "Female" AS GenderValue, 0 AS Gender','Gender','GenderValue','gender');
	echo comboBox($link,'SELECT IDNo, CONCAT(Fullname," (",Branch,")") AS NameBranch FROM attend_30currentpositions ORDER BY JLID DESC;','NameBranch','IDNo','supid');
	echo comboBox($link,'SELECT PositionID, Position FROM attend_0positions ORDER BY Position;','Position','PositionID','positions');
	   include_once "../generalinfo/lists.inc";
foreach ($liststoshow as $list){
renderlist($list);   
$stmt0=$link->query('Select (IDNo)+1 AS NewIDNo FROM `1_gamit`.`0idinfo` ORDER BY IDNo DESC LIMIT 1;');
$res=$stmt0->fetch();
$newidno=$res['NewIDNo'];
}
$whichqry=(!isset($_GET['w'])?'AddNewEmployee':$_GET['w']);

switch ($whichqry){
case 'AddNewEmployee':
		echo '<br><br>';
		echo '<div class="tabs">
		<ul class="tab-links">
			<li class="active"><a href="#tab1">Employee Information</a></li>
		</ul>
		<br>
		
		<div class="tab-content">
			<div id="tab1" class="tab active">';
			?>
			<form action="praddemployee.php?calledfrom=1" method="POST">
			<div style="background-color:white;padding:20px;">

    Next available IDNo: <?php echo $newidno;?><br><br>
<fieldset style="padding:6px;background-color:yellowgreen;">
<legend style="background-color:yellow;">
	<span style="color:blue;"><b>&nbsp;EMPLOYEE INFORMATION&nbsp;</b></span>
</legend>
<div>
<div style="float:left;">
 <div style="margin-bottom:3px;"><div><label>IDNo <span>*</span></label><input type="text" name="IDNo" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>Nickname <span>*</span></label><input type="text" name="Nickname" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>SurName <span>*</span></label><input type="text" name="SurName" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>FirstName <span>*</span></label><input type="text" name="FirstName" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>MiddleName <span>*</span></label><input type="text" name="MiddleName" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>Gender <span>*</span></label><input type="text" name="Gender" value="" size="15" maxlength="255" autocomplete="off" list="gender" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>UBPATM </label><input type="text" name="UBPATM" value="" size="25" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>WithLeaves <span>*</span></label><input type="text" name="WithLeaves" value="0" size="15" maxlength="255" autocomplete="off" list="yesno" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>DirectOrAgency <span>*</span></label><input type="text" name="DirectOrAgency" value="Direct" size="15" maxlength="255" autocomplete="off" list="directoragency" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>PositionID <span>*</span></label><input type="text" name="PositionID" value="" size="15" maxlength="255" autocomplete="off" list="positions" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>Supervisor <span>*</span></label><input type="text" name="Supervisor" value="" size="15" maxlength="255" autocomplete="off" list="supid" required/></div></div>
 </div>
 <div style="margin-left:40%;">
 <div style="margin-bottom:3px;"><div><label>WithSat <span>*</span></label><input type="text" name="WithSat" value="" size="15" maxlength="255" autocomplete="off" list="withsat" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>RestDay <span>*</span></label><input type="text" name="RestDay" value="" size="15" maxlength="255" autocomplete="off" list="weekdays" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>ResTel </label><input type="text" name="ResTel" value="" size="15" maxlength="255" autocomplete="off"/></div></div>
 <div style="margin-bottom:3px;"><div><label>MobileNo </label><input type="text" name="MobileNo" value="" size="15" maxlength="255" autocomplete="off"/></div></div>
 <div style="margin-bottom:3px;"><div><label>GCashMobileNumber <i>(n/a if none)</i> <span>*</span></label><input type="text" name="GCashMobileNumber" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>Email </label><input type="text" name="Email" value="" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>DateHired <span>*</span></label><input type="date" name="DateHired" value="" size="25" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>BranchNo <span>*</span></label><input type="text" name="BranchNo" value="" size="15" maxlength="255" autocomplete="off" list="branches" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>BirthDate <span>*</span></label><input type="date" name="Birthdate" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>TOTAL Net Taxable Income from all previous employers THIS YEAR (BIR 2316 no. 55) <span>*</span></label><input type="text" name="PrevEmployerNetTaxable" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>TOTAL Tax Withheld from all previous employers THIS YEAR (BIR 2316 no. 30A) <span>*</span></label><input type="text" name="PrevEmployerTaxWHeld" value="" size="15" maxlength="255" autocomplete="off" required/>
         
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

 <div style="margin-bottom:3px;"><div><label>StreetAddress_Present <span>*</span></label><input type="text" name="StreetAddress" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>ZipCode_Present <span>*</span></label><input type="text" name="ZipCode" value="" size="15" maxlength="255" autocomplete="off" list="zipplaces" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>StreetAddress_Provincial <span>*</span></label><input type="text" name="StreetAddress_Provincial" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>ZipCode_Provincial <span>*</span></label><input type="text" name="ZipCode_Provincial" value="" size="15" maxlength="255" autocomplete="off" list="zipplaces" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>RCompanyNo <span>*</span></label><input type="text" name="RCompanyNo" value="" size="15" maxlength="255" autocomplete="off" list="companynumbers" required/></div></div>
 <!--<div style="margin-bottom:3px;"><div><label>RDateHired <span>*</span></label><input type="date" name="RDateHired" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>-->
 <div style="margin-bottom:3px;"><div><label>ReferredBy </label><input type="text" name="ReferredBy" value="" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>SSSNo <span>*</span></label><input type="text" name="SSSNo" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>PHICNo <span>*</span></label><input type="text" name="PHICNo" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>PAGIBIGNo <span>*</span></label><input type="text" name="PAGIBIGNo" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>TIN <span>*</span></label><input type="text" name="TIN" value="" size="25" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>NoofDependents <span>*</span></label><input type="text" name="NoofDependents" value="0" size="15" maxlength="255" autocomplete="off" required/></div></div>
<div style="margin-bottom:3px;"><div><label>ICEPerson </label><input type="text" name="ICEPerson" value="" size="15" maxlength="255" autocomplete="off"/></div></div>
 <div style="margin-bottom:3px;"><div><label>RelationshiptoEmployee <span>*</span></label><input type="text" name="RelationshiptoEmployee" value="" size="15" maxlength="255" autocomplete="off"/></div></div>
 <div style="margin-bottom:3px;"><div><label>ICEContactInfo <span>*</span></label><input type="text" name="ICEContactInfo" value="" size="15" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>ICEAddress </label><input type="text" name="ICEAddress" value="" size="15" maxlength="255" autocomplete="off" /></div></div>
 </div>
 <div style="margin-left:40%;">
 
 <div style="margin-bottom:3px;"><div><label>Place Of Birth <span>*</span></label><input type="text" name="PlaceOfBirth" value="" size="25" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>Mother's Maiden Name <span>*</span></label><input type="text" name="MMName" value="" size="25" maxlength="255" autocomplete="off" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>CivilStatus <span>*</span></label><input type="text" name="CivilStatus" value="" size="25" maxlength="255" autocomplete="off" list="civilstatus" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>AssignedEmail </label><input type="text" name="RTCEmail" size="25" maxlength="255" autocomplete="off" value="@1rotarytrading.com" required/></div></div>
 <div style="margin-bottom:3px;"><div><label>SpouseName </label><input type="text" name="SpouseName" value="" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>SpouseBirthdate </label><input type="date" name="SpouseBirthdate" value="" size="15" maxlength="255" autocomplete="off" /></div></div>
 
 <div style="margin-bottom:3px;"><div><label>ChildName1 </label><input type="text" name="ChildName1" value="" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>ChildName2 </label><input type="text" name="ChildName2" value="" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>ChildName3 </label><input type="text" name="ChildName3" value="" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>ChildName4 </label><input type="text" name="ChildName4" value="" size="15" maxlength="255" autocomplete="off" /></div></div>
 
 
 <div style="margin-bottom:3px;"><div><label>ChildBirthdate1 </label><input type="date" name="ChildBirthdate1" value="" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>ChildBirthdate2 </label><input type="date" name="ChildBirthdate2" value="" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>ChildBirthdate3 </label><input type="date" name="ChildBirthdate3" value="" size="15" maxlength="255" autocomplete="off" /></div></div>
 <div style="margin-bottom:3px;"><div><label>ChildBirthdate4 </label><input type="date" name="ChildBirthdate4" value="" size="15" maxlength="255" autocomplete="off" /></div></div>
 
 
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
	echo '<input type="submit" style="width:100%;padding:10px;background-color:blue;border-radius:10px;color:white;font-size:12pt;font-weight:bold;" value="Add New Employee"></form>';
	
  break;
 
  }
  noform:
     
     $link=null; $stmt=null; 
     $stmt0=null;
?>
