<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(671,672,673,674,675,6714,6743,40102);$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=false; include_once('../switchboard/contents.php');


if (isset($_GET['edit'])){
    $calledfrom=$_REQUEST['edit'];
} else {
$editprocess='employeeinfo.php?edit='.$_GET['calledfrom'].($_GET['calledfrom']==7?'&IDNo=':'');$editprocesslabel='Edit';
$calledfrom=$_REQUEST['calledfrom'];
}

if (in_array($calledfrom,array(6,61,7,71,8,81,9,91,62))){
		include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
}
if (in_array($calledfrom,array(0,1))){
	include_once('../backendphp/layout/linkstyle.php');
	if (allowedToOpen(6717,'1rtc')) {
		echo '<br><a id="link" href="govtreport.php">Government Report</a><br>';
	}
}

$txnid='IDNo';$txnidname='IDNo';
switch ($calledfrom){
    case 0: //all id info
        if (!allowedToOpen(671,'1rtc')){  echo 'No permission'; exit;}

        $show=!isset($_POST['show'])?0:$_POST['show'];
        $title='All ID Information '; $formdesc='<form action="#" method="post"><input type=submit value="'.($show==0?'Show All':'Current Only').'">
        <input type=hidden name="show" value="'.($show==0?1:0).'"></form>';
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Nickname');
        if ($show==1){
            $sql='Select *,MMName AS MothersMaidenName,CONCAT(StreetAddress,", ",BarangayOrTown,", ",CityOrProvince) AS PresentAddress,CONCAT(StreetAddress_Provincial,", ",BarangayOrTown_Provincial,", ",CityOrProvince_Provincial) AS ProvincialAddress from `1_gamit`.`0idinfo` where IDNo>0';} else {
            $formdesc='Current Employees Only<br>'.$formdesc;
        $sql='Select i.*,MMName AS MothersMaidenName,CONCAT(StreetAddress,", ",BarangayOrTown,", ",CityOrProvince) AS PresentAddress,CONCAT(StreetAddress_Provincial,", ",BarangayOrTown_Provincial,", ",CityOrProvince_Provincial) AS ProvincialAddress,truncate(((to_days(now()) - to_days(`i`.`Birthdate`)) / 365.25),2) AS `Age`,truncate(((to_days(now()) - to_days(`i`.`DateHired`)) / 365.25),2) AS `Tenure` from `1_gamit`.`0idinfo` i join `1employees` e on i.IDNo=e.IDNo where i.IDNo>0 and e.Resigned=0  ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');}

        $editprocess='employeeinfo.php?edit=2&IDNo=';$editprocesslabel='Edit';
		 if ($show==1){

		 $columnnames=array('IDNo','Nickname','SurName','FirstName','MiddleName','PlaceOfBirth','MothersMaidenName','PresentAddress','ProvincialAddress','ResTel','MobileNo','Email','DateHired','Birthdate','ReferredBy','SSSNo','PHICNo','PagIbigNo','TIN','NoofDependents','SpouseName','ChildName1','ChildName2','ChildName3','ChildName4','SpouseBirthdate','ChildBirthdate1','ChildBirthdate2','ChildBirthdate3','ChildBirthdate4','ICEPerson','RelationshiptoEmployee','ICEContactInfo','ICEAddress','CivilStatus','Resigned?','DateResigned','ResignedWithClearance','ResignReason');}else{
		 $columnnames=array('IDNo','Nickname','SurName','FirstName','MiddleName','PlaceOfBirth','MothersMaidenName','PresentAddress','ProvincialAddress','MobileNo','Email','DateHired','Tenure','Birthdate','Age','ReferredBy','SSSNo','PHICNo','PagIbigNo','TIN','NoofDependents','SpouseName','ChildName1','ChildName2','ChildName3','ChildName4','SpouseBirthdate','ChildBirthdate1','ChildBirthdate2','ChildBirthdate3','ChildBirthdate4','ICEPerson','RelationshiptoEmployee','ICEContactInfo','ICEAddress','CivilStatus','Resigned?','DateResigned','ResignedWithClearance','ResignReason');
		}
        goto lookup;
        break;
    case 1://current year's employees
        if (!allowedToOpen(672,'1rtc')){  echo 'No permission'; exit;}

        $title='Employees This Year';
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Nickname');
        $sql='SELECT e.*,
		(CASE
			WHEN EmpStatus=0 THEN "Probationary"
			WHEN EmpStatus=1 THEN "Regular"
			ELSE "Resigned"
		END)

			AS EmploymentStatus,IF(Gender=0,"F","M") AS Gender, c.Company, IF(i.DateResigned=\'0000-00-00\' OR ISNULL(i.DateResigned),"", i.DateResigned) AS DateResigned, WithHMO, i.TIN, i.SSSNo, i.PagIbigNo, i.PHICNo, cp.Position, Branch FROM `1employees` e LEFT JOIN `1companies` c on e.RCompanyNo=c.CompanyNo JOIN `1_gamit`.`0idinfo` i ON i.IDNo=e.IDNo JOIN `attend_30currentpositions` cp ON cp.IDNo=e.IDNo JOIN `attend_0positions` `p` ON ((`p`.`PositionID` = `cp`.`PositionID`)) ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');

        if (allowedToOpen(array(6711,67111,6714),'1rtc')){
			$show=!isset($_POST['show'])?'Show Current':$_POST['show'];
			include('employeefilterlinks.php');

            if ($show=='Show Current'){
            $editprocess='employeeinfo.php?edit=3&IDNo=';$editprocesslabel='Edit';
			//'RDateHired',
            $columnnames=array('IDNo','Nickname','SurName','FirstName','MiddleName','Position','Branch','Gender','UBPATM','TIN', 'SSSNo', 'PagIbigNo', 'PHICNo','WithLeaves','WithHMO','DateHired','Company','DirectOrAgency','Birthdate','EmploymentStatus','WithSat','RestDay','SLBalDecCutoff','PaidSLBenefit','SLThisYr','VLfromPosition','VLfromTenure','PrevEmployerNetTaxable','PrevEmployerTaxWHeld','UniformSize','ShirtSize');
            } else if($show=='Show Resigned But With System Access') {
				$sql='SELECT IDNo, CONCAT(Nickname," ",SurName) AS Name FROM 1employees e WHERE Resigned=1 AND IDNo IN (SELECT IDNo FROM 1_gamit.1rtcusers)';
				$stmt=$link->query($sql); $result=$stmt->fetchAll();
				$columnnames=array('IDNo','Name');
				$editprocess='employeeinfo.php?edit=11&IDNo='; $editprocesslabel='Remove System Access';
				$width='50%';
				$title=$show;
				include('../backendphp/layout/displayastable.php');
				exit();
			}
				else if($show=='Show Not Resigned And No System Access'){
					$sql='SELECT IDNo,IDNo AS TxnID, CONCAT(Nickname," ",SurName) AS Name,"@1rotary.com.ph" AS Email,"" AS Remarks FROM 1employees e WHERE Resigned=0 AND IDNo NOT IN (SELECT IDNo FROM 1_gamit.1rtcusers)';
						$stmt=$link->query($sql); $result=$stmt->fetchAll();
						$editprocess='employeeinfo.php?edit=12&IDNo='; $editprocesslabel='Add System Access';
						$width='60%';
						$title=$show;

						$columnnameslist=array('IDNo','Name','Remarks','Email');
			$txnid='TxnID';
			$columnnames=$columnnameslist;
			$columnstoedit=array('Email');

						include('../backendphp/layout/displayastableeditcellswithsorting.php');
						exit();
				} else if($show=='Show Prelim Resign'){
					 $sql='SELECT u.IDNo, FullName,Branch FROM 1_gamit.1rtcusers u JOIN attend_30currentpositions e ON u.IDNo=e.IDNo WHERE Active=0';
					 $editprocess='../attendance/resignemployee.php?notused='; $editprocesslabel='Set Active/Resign';
					 $columnnames=array('IDNo','FullName','Branch');
					 $width='50%';
					$title=$show;
					include('../backendphp/layout/displayastable.php');
					exit();
				}
			else {
	    $title='Resigned Employees';
//,'RDateHired'
	    $columnnames=array('IDNo','Nickname','SurName','FirstName','MiddleName','Gender','UBPATM','TIN', 'SSSNo', 'PagIbigNo', 'PHICNo','WithLeaves','WithHMO','DateHired','Company','DirectOrAgency','LastPosition','LastBranch','Birthdate','WithSat','RestDay','SLBalDecCutoff','PaidSLBenefit','Resigned','DateResigned','PrevEmployerNetTaxable','PrevEmployerTaxWHeld','UniformSize','ShirtSize');
            $sql='SELECT e.*,IF(Gender=0,"F","M") AS Gender, c.Company, DateResigned,
        i.TIN, i.SSSNo, i.PagIbigNo, i.PHICNo, Position AS LastPosition, Branch AS LastBranch FROM `1employees` e
        LEFT JOIN `1companies` c on e.RCompanyNo=c.CompanyNo
        JOIN `1_gamit`.`0idinfo` i ON i.IDNo=e.IDNo
        JOIN `attend_30latestpositionsinclresigned` r ON e.IDNo=r.IDNo
        JOIN `attend_0positions` p ON p.PositionID=r.PositionID
        LEFT JOIN `attend_1defaultbranchassign` dba ON e.IDNo=dba.IDNo
        JOIN `1branches` b ON b.BranchNo=dba.DefaultBranchAssignNo
        WHERE e.Resigned<>0
        ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
            } // end if show
        } else {
			//'RDateHired',
            $columnnames=array('IDNo','Nickname','SurName','FirstName','MiddleName','Position','Branch','UBPATM','TIN', 'SSSNo', 'PagIbigNo', 'PHICNo','Company','DirectOrAgency','DateResigned','Birthdate','PrevEmployerNetTaxable','PrevEmployerTaxWHeld','UniformSize','ShirtSize');
        }
         $txnidname='IDNo';
        goto lookup;
        break;
    case 2:// edit id info
        if (!allowedToOpen(6721,'1rtc')){  echo 'No permission'; exit;}

        $title='All ID Information';
        $fieldname='IDNo';
        $processlabel1='Edit';
        $editprocess='employeeinfo.php?edit=2&IDNo=';$editprocesslabel='Edit';

        $method='POST';
        $processblank='';
        $processlabelblank='';
        if (allowedToOpen(array(6711,67111),'1rtc')){
            $txnid=intval($_REQUEST['IDNo']);
            if (allowedToOpen(67111,'1rtc')){
                $sql001='select IDNo FROM attend_30currentpositions WHERE IDNo='.$txnid;
                $stmt001=$link->query($sql001);
                if ($stmt001->rowCount()>0)
                {
                        goto proceed;
                } else {
                    echo 'Not Allowed To Edit Employee'; exit();
                }

            }
            proceed:
            $sql='Select * from `1_gamit`.`0idinfo` where IDNo=\''.$txnid.'\'';
            $action='prempedit.php?edit=2&IDNo='.$txnid;
            $columnstoedit=array('Nickname','SurName','FirstName','MiddleName','PlaceOfBirth','MMName','ZipCode','StreetAddress','CityOrProvince','BarangayOrTown','ZipCode_Provincial','StreetAddress_Provincial','CityOrProvince_Provincial','BarangayOrTown_Provincial','ResTel','MobileNo','Email','DateHired','Birthdate','ReferredBy','SSSNo','PHICNo','PagIbigNo','TIN','NoofDependents','SpouseName','ChildName1','ChildName2','ChildName3','ChildName4','SpouseBirthdate','ChildBirthdate1','ChildBirthdate2','ChildBirthdate3','ChildBirthdate4','ICEPerson','RelationshiptoEmployee','ICEContactInfo','ICEAddress','CivilStatus','Resigned?','DateResigned','ResignedWithClearance','ResignReason');
        $columnnames=array('IDNo','Nickname','SurName','FirstName','MiddleName','PlaceOfBirth','MMName','ZipCode','StreetAddress','CityOrProvince','BarangayOrTown','ZipCode_Provincial','StreetAddress_Provincial','CityOrProvince_Provincial','BarangayOrTown_Provincial','ResTel','MobileNo','Email','DateHired','Birthdate','ReferredBy','SSSNo','PHICNo','PagIbigNo','TIN','NoofDependents','SpouseName','ChildName1','ChildName2','ChildName3','ChildName4','SpouseBirthdate','ChildBirthdate1','ChildBirthdate2','ChildBirthdate3','ChildBirthdate4','ICEPerson','RelationshiptoEmployee','ICEContactInfo','ICEAddress','CivilStatus','Resigned?','DateResigned','ResignedWithClearance','ResignReason');
            $columnslist=array('Resigned?','CivilStatus');
            $listsname=array('Resigned?'=>'yesno','CivilStatus'=>'civilstatus');//'TaxClassification'=>'taxclass',
            $liststoshow=array('yesno','civilstatus');
        } else {
            $columnstoedit=array();
            $columnnames=array();
            $liststoshow=array();
        }

        include('../backendphp/layout/rendersubform.php');
        goto endofform;
        break;
    case 3: // edit this year's employees
        if (!allowedToOpen(6721,'1rtc')){  echo 'No permission'; exit;}

        $title='Edit Employee IDNo '.$_REQUEST['IDNo'];
        $txnid='IDNo';
        $editprocess='employeeinfo.php?edit=3&IDNo=';$editprocesslabel='Edit';
        $sql='SELECT *, if(year(`e`.`DateHired`) = '.$currentyr.' - 1,if(month(`e`.`DateHired`) = 12,0,round((365 - dayofyear(`e`.`DateHired`)) / 365 * 5 + 0.5,0)),5) AS `RegularSL`, c.Company FROM `1employees` e left join `1companies` c on e.RCompanyNo=c.CompanyNo where IDNo>1002';
		//'RDateHired',
        $columnnames=array('IDNo','Nickname','SurName','FirstName','MiddleName','Gender','UBPATM','WithLeaves','WithHMO','Resigned','DateHired','Company','Birthdate','SLBalDecCutoff','DirectOrAgency','PaidSLBenefit','WithSat','PrevEmployerNetTaxable','PrevEmployerTaxWHeld','UniformSize','ShirtSize');

        $fieldname='IDNo';
        $processlabel1='Edit';
        $editprocess='employeeinfo.php?edit=3&IDNo=';$editprocesslabel='Edit';
        $method='POST';
        $processblank='';
        $processlabelblank='';
        if (allowedToOpen(array(6711,67111),'1rtc')){
            $txnid=intval($_REQUEST['IDNo']);
            $sql='Select *, if(year(`e`.`DateHired`) = '.$currentyr.' - 1,if(month(`e`.`DateHired`) = 12,0,round((365 - dayofyear(`e`.`DateHired`)) / 365 * 5 + 0.5,0)),5) AS `RegularVL`,if(year(`e`.`DateHired`) = '.$currentyr.' - 1,if(month(`e`.`DateHired`) = 12,0,round((365 - dayofyear(`e`.`DateHired`)) / 365 * 5 + 0.5,0)),5) AS `RegularSL`, c.Company from `1employees` e left join `1companies` c on e.RCompanyNo=c.CompanyNo where IDNo=\''.$txnid.'\'';
            $action='prempedit.php?edit=3&IDNo='.$txnid;
			//'RDateHired',
            $columnstoedit=array('Nickname','SurName','FirstName','MiddleName','Gender','UBPATM','WithLeaves','WithHMO','Resigned','DateHired','RCompanyNo','Birthdate','DirectOrAgency','PaidSLBenefit','RegularSL','RegularVL','VLfromTenure','WithSat','RestDay','PrevEmployerNetTaxable','PrevEmployerTaxWHeld','UniformSize','ShirtSize');
            if($_SESSION['(ak0)']==1002){ $columnstoedit[]='VLfromPosition';}
			//'RDateHired',
        $columnnames=array('IDNo','Nickname','SurName','FirstName','MiddleName','Gender','UBPATM','WithLeaves','WithHMO','Resigned','DateHired','RCompanyNo', 'Company', 'Birthdate','SLBalDecCutoff','DirectOrAgency','PaidSLBenefit','SLThisYr','VLfromPosition','VLfromTenure','WithSat','RestDay','PrevEmployerNetTaxable','PrevEmployerTaxWHeld','UniformSize','ShirtSize');
            $columnslist=array('WithLeaves','WithHMO','Resigned','RCompanyNo','DirectOrAgency','WithSat','RestDay');
            $listsname=array('WithLeaves'=>'yesno','WithHMO'=>'yesno','Resigned'=>'yesno','DirectOrAgency'=>'directoragency','WithSat'=>'withsat','RestDay'=>'weekdays','RCompanyNo'=>'companies');
            $liststoshow=array('yesno','civilstatus','directoragency','withsat','weekdays','companies');
        } else {
            $columnstoedit=array();
            $columnnames=array();
            $liststoshow=array();
        }

        include('../backendphp/layout/rendersubform.php');
        goto endofform;
        break;


	 case 7://edit team leader assigns
        $txnid='TxnID';
        unset($editprocess,$txnidname);
        if (allowedToOpen(674,'1rtc')){
            if (allowedToOpen(array(6741,6743),'1rtc')){
        include_once('../generalinfo/lists.inc');

		if (!allowedToOpen(6702,'1rtc')){
			$sql='SELECT deptid,JLID FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].'';
			$stmt=$link->query($sql); $resinfo=$stmt->fetch();

			$acondi=' WHERE p.deptid='.$resinfo['deptid'].' AND cp.JLID<="'.$resinfo['JLID'].'"';
			$bcondi=' WHERE p.deptid='.$resinfo['deptid'].'';
		} else {
			$acondi='';
			$bcondi='';
		}

		echo comboBox($link,'SELECT FullName, IDNo FROM `attend_30currentpositions` cp JOIN attend_0positions p ON cp.PositionID=p.PositionID  '.$acondi.' ORDER BY FullName;','IDNo','FullName','namelist');

		echo comboBox($link,'SELECT PositionID, Position FROM attend_0positions p '.$bcondi.' ORDER BY Position ASC;','PositionID','Position','positions');


        ?><br><br>

        <form method='post' action='employeeinfo.php?calledfrom=71'>
        Change Assignment:  &nbsp &nbsp Date of Change<input type='date' size="6" name='DateofChange' value='<?php echo date('Y-m-d');?>'>
          &nbsp &nbsp Branch <input type='text' size="8" name='Branch' list='branchnames' autocomplete=off>
          <?php
          if(!allowedToOpen(6743,'1rtc')){
          ?>
    		  &nbsp &nbsp Position <input type='text' size="10" name='Position' list='positions' autocomplete=off>
          <?php
          }
          ?>
    		  &nbsp &nbsp IDNo <input type='text' size="10" name='FullName' list='namelist' autocomplete=off>
          &nbsp &nbsp Remarks <input type='text' name='Remarks' autocomplete=off>
          <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>"> &nbsp &nbsp
			    <input type='submit' size=10 name='submit' value='Enter'>&nbsp &nbsp &nbsp
          </form>
        <?php
        renderlist('branchnames');

        }
        $title='STL/CredAnalyst/InvtyPlanner/Ops Assignments';
        $formdesc='</i><br/><b><a href=employeeinfo.php?calledfrom=6>Assignment History</a></b><i><br><br><br>';
        $columnnames=array('DateofChange','BranchNo','Branch','InventoryPlanner','TLFullName','SAMName','OpsSpecialist','OpsManager','CreditAnalyst');//'AssignedAR',
        $columnsub=$columnnames;
					$sql='SELECT bg.DateofChange, b.BranchNo, Branch, concat(e.Nickname," ",e.Surname) as TLFullName, concat(e3.Nickname," ",e3.Surname) as CreditAnalyst, SAM, concat(e4.Nickname," ",e4.Surname) as SAMName, concat(e5.Nickname," ",e5.Surname) AS OpsSpecialist,concat(e6.Nickname," ",e6.Surname) AS OpsManager,concat(e7.Nickname," ",e7.Surname) AS InventoryPlanner FROM attend_1branchgroups bg LEFT join `1employees` e on bg.TeamLeader=e.IDNo  LEFT join `1employees` e3 on bg.CNC=e3.IDNo LEFT join `1employees` e4 on bg.SAM=e4.IDNo  left join `1employees` e5 on bg.OpsSpecialist=e5.IDNo left join `1employees` e6 on bg.OpsManager=e6.IDNo left join `1employees` e7 on bg.InventoryPlanner=e7.IDNo join `1branches` b on b.BranchNo=bg.BranchNo  where b.Active=1;';

        include('../backendphp/layout/displayastable.php');
      } else { echo 'No permission'; exit;}
        goto endofform;
    break;

	 //removed stl fixed assignment
     //Not used anymore

	case '71':
	if (allowedToOpen(array(6743,6741),'1rtc')) {
		$branchno=companyandbranchValue($link,'1branches','Branch',addslashes($_POST['Branch']),'BranchNo');
    if(allowedToOpen(6743,'1rtc')){
        $posid=comboBoxValue($link,'attend_30currentpositions','FullName',addslashes($_POST['FullName']),'PositionID');
    } else{
      $posid=comboBoxValue($link,'attend_0positions','Position',addslashes($_POST['Position']),'PositionID');}
		$idno=comboBoxValue($link,'attend_30currentpositions','FullName',addslashes($_POST['FullName']),'IDNo');


		$sql='INSERT INTO attend_2changebranchgroup SET DateofChange="'.$_POST['DateofChange'].'",BranchNo='.$branchno.',PositionID='.$posid.',IDNo='.$idno.',Remarks="'.$_POST['Remarks'].'",EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW();';
		$stmt=$link->prepare($sql); $stmt->execute();
	}
	header ("Location:employeeinfo.php?calledfrom=7");

	exit();
	break;



    case 6://team leader assignment history
        unset($editprocess);
        $title='STL/CredAnalyst/InvtyPlanner/Ops Assignment History';
        $columnnames=array('DateofChange','BranchNo','Branch', 'PositionID','Position','IDNo','FullName','Remarks','EncodedBy','TimeStamp');
        $columnsub=$columnnames;
        $txnid='TxnID';
    if(allowedToOpen(array(6741,6743),'1rtc')){
		 echo comboBox($link,'SELECT cbg.IDNo,CONCAT(Nickname," ",Surname) AS FullName FROM attend_2changebranchgroup cbg LEFT JOIN 1employees id ON cbg.IDNo=id.IDNo WHERE `Resigned`=0 AND DateofChange=CURDATE() AND cbg.EncodedByNo='.$_SESSION['(ak0)'].' GROUP BY IDNo','FullName','IDNo','encodeidtoday');

		 echo comboBox($link,'SELECT cbg.BranchNo,Branch FROM attend_2changebranchgroup cbg LEFT JOIN 1branches b ON cbg.BranchNo=b.BranchNo WHERE DateofChange=CURDATE() AND cbg.EncodedByNo='.$_SESSION['(ak0)'].' GROUP BY BranchNo','BranchNo','Branch','branchlist');

	 $formdesc=  '</i><br>Delete IDNo (Current Date Encode Only)';
	 $formdesc.= "<form action='employeeinfo.php?calledfrom=61' method='POST' enctype='multipart/form-data' style='display: inline'>
        <input type='text' name='IDNo' list='encodeidtoday' autocomplete='off'> <input type='text' name='Branch' list='branchlist' autocomplete='off'>
        <input type='submit' name='btnDelete' value='Delete' OnClick='return confirm(\"Really delete this?\");' size='10'>
   </form><br>";
}
   $formdesc.='<br><b><a href=employeeinfo.php?calledfrom=7>Change Assignment</a></b><i>';

            $sql='select DateofChange,cbg.BranchNo,Branch,cbg.PositionID,`Position`,cbg.IDNo,concat(e.Nickname," ",e.Surname) AS FullName,concat(e2.Nickname," ",e2.Surname) AS EncodedBy,cbg.Remarks,cbg.EncodedByNo,cbg.TimeStamp FROM attend_2changebranchgroup cbg LEFT JOIN 1branches b ON cbg.BranchNo=b.BranchNo LEFT JOIN 1employees e ON cbg.IDNo=e.IDNo LEFT JOIN attend_0positions p ON cbg.PositionID=p.PositionID LEFT JOIN 1employees e2 ON cbg.EncodedByNo=e2.IDNo ORDER BY `TimeStamp` DESC;';
        include('../backendphp/layout/displayastable.php');



        goto endofform;
        break;

		case '61':
		if (allowedToOpen(array(6741,6743),'1rtc')) {

			 $branchno=companyandbranchValue($link,'1branches','Branch',addslashes($_POST['Branch']),'BranchNo');
			$sql='DELETE FROM `attend_2changebranchgroup` WHERE `IDNo`='.$_POST['IDNo'].' AND BranchNo='.$branchno.' AND DateofChange=CURDATE() AND EncodedByNo='.$_SESSION['(ak0)'].';';

			$stmt=$link->prepare($sql);
			$stmt->execute();
			header('Location:employeeinfo.php?calledfrom=6');
		}	else {
			echo 'No permission';
		}

		exit();
		break;


		case '11';
		$sql='DELETE FROM `1_gamit`.`1rtcusers` WHERE IDNo='.intval($_GET['IDNo']).'';
        $stmt=$link->prepare($sql); $stmt->execute();

		header ("Location:employeeinfo.php?calledfrom=1");

		exit();

		break;

		case '12';
		include_once $path.'/acrossyrs/commonfunctions/fxngenrandpass.php';
		include_once $path.'/acrossyrs/commonfunctions/hashandcrypt.php';

		   $newsalt=generatePassword();
		   $temppass=addslashes($_GET['IDNo']);
		   $newhash=generateHash($temppass);
		   $saltforid=generateSaltforid(9);
		   $progcookie=generatePassword(45);

			$email=($_POST['Email']<>'@1rotary.com.ph')?$_POST['Email']:null;
			$sql='Insert into `1_gamit`.`1rtcusers` set `IDNo`='.addslashes($_GET['IDNo']) .',`uphashmayasin`=\''.$newhash.'\', `saltforid`=\''.$saltforid.'\',`ProgCookie`=\''.$progcookie.'\',`ProgCookieOld`=\''.$progcookie.'\',`Email`=\''.$email.'\', EncodedByNo='.$_SESSION['(ak0)'];

			$stmt=$link->prepare($sql);
			$stmt->execute();

		header ("Location:employeeinfo.php?calledfrom=1");

		exit();

		break;
}

lookup:
    if (allowedToOpen(array(6711,67111),'1rtc')){
        $columnsub=$columnnames;
    include('../backendphp/layout/displayastable.php');
    } else {
        $columnsub=$columnnames;
     include('../backendphp/layout/displayastable.php');
    }
endofform:

?>
