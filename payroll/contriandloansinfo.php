<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

if (!allowedToOpen(9012,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
	
include_once('../switchboard/contents.php');

include_once('../backendphp/layout/linkstyle.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';


?>

<br><div id="section" style="display: block;">
<?php
echo "<a id='link' href='contriandloansinfo.php'>Government Payment Details</a> ";
echo "<a id='link' href='contriandloansinfo.php?w=1'>SSS Contributions</a> ";
echo "<a id='link' href='contriandloansinfo.php?w=2'>SSS Loans - SALARY</a> ";
echo "<a id='link' href='contriandloansinfo.php?w=3'>SSS Loans - CALAMITY</a> ";
echo "<a id='link' href='contriandloansinfo.php?w=4'>PagIbig Contributions</a> ";
echo "<a id='link' href='contriandloansinfo.php?w=5'>PagIbig Loans - SALARY</a> ";
echo "<a id='link' href='contriandloansinfo.php?w=6'>PagIbig Loans - CALAMITY</a> ";
echo "<a id='link' href='contriandloansinfo.php?w=7'>PhilHealth Contributions</a> ";


echo '<br>';


$which=(!isset($_GET['w'])?'select':$_GET['w']);



switch ($which)
{

    case 'select':
        $title='Government Payment Details';
    echo '<title>'.$title.'</title>';
    echo '<br><h3>'.$title.'</h3>';

    $sqlcompanies='SELECT CompanyNo,Company FROM 1companies WHERE CompanyNo<=6';

    $stmt=$link->query($sqlcompanies); $results=$stmt->fetchAll();

    $arrgpids=array('1'=>'SSS Contributions','2'=>'SSS Loans - SALARY','3'=>'SSS Loans - CALAMITY','4'=>'PagIbig Contributions','5'=>'PagIbig Loans - SALARY','6'=>'PagIbig Loans - CALAMITY','7'=>'PhilHealth Contributions');
    $sqlfield='';
    foreach($arrgpids AS $arrgpid => $arrgpname){
        $sqlfield.=' SELECT "'.$arrgpname.'" AS GPName,';
        $sqlselectcomp='';

        $columnnames=array('GovtAgency');

        foreach($results AS $result){
            $sqlfield.=' (SELECT COUNT(GPID) AS CountNoPayment FROM payroll_1govtpaymentsinfo WHERE RefNo IS NULL AND ApplicableMonth<='.date('m').' AND GAID='.$arrgpid.' AND CompanyNo='.$result['CompanyNo'].') AS `'.$result['Company'].'`,';
            $sqlselectcomp.='IF('.$result['Company'].'=0,"",CONCAT("<font color=\"red\">",'.$result['Company'].'," Pending</font>")) AS '.$result['Company'].', ';
            array_push($columnnames,$result['Company']);

        }

        $sqlfield=substr($sqlfield, 0, -1);
        $sqlfield.=' UNION ';
    }
    $sqlfield=substr($sqlfield, 0, -6);

    $sql0='CREATE TEMPORARY TABLE govtpendingpayment '.$sqlfield;
    $stmt0=$link->prepare($sql0); $stmt0->execute();

    // echo $sql0.'<br><br>';
    $sql='SELECT GPName AS GovtAgency,'.$sqlselectcomp.'1 FROM govtpendingpayment';
$title='';

include '../backendphp/layout/displayastablenosort.php';

    break;
 
	case '1': // SSS Contri
		$title='SSS Contributions';
        $refnofield='SBRNo';
        $color='#fff';
        goto here;
    
    case '2': 
        $title='SSS Loans - SALARY';
        $refnofield='RefNo';
        $color='#fff';
        goto here;

    case '3':
        $title='SSS Loans - CALAMITY';
        $refnofield='RefNo';
        $color='#fff';
        goto here;

    case '4': 
        $title='PagIbig Contributions';
        $refnofield='RefNo';
        $color='#fff';
        goto here;

    case '5': 
        $title='PagIbig Loans - SALARY';
        $refnofield='RefNo';
        $color='#fff';
        goto here;
    
    case '6': 
        $title='PagIbig Loans - CALAMITY';
        $refnofield='RefNo';
        $color='#fff';
        goto here;
    
    case '7': 
        $title='PhilHealth Contributions';
        $refnofield='RefNo';
        $color='#fff';
        goto here;

        here:

        echo '<style>
        tr:hover {
            background-color: #e5e5e5;
        }
        
        tr:hover td {
            background-color: transparent;
            color:blue;
        }
        </style>';
		echo '<title>'.$title.'</title>';
		echo '<br><h3>'.$title.'</h3><br>';

        $arrmonths=array('12','11','10','09','08','07','06','05','04','03','02','01');

        $optionm='';
        foreach($arrmonths AS $arrmonth){
            if($arrmonth<=date('m') OR date('Y')==$currentyr+1){
                $optionm.='<option value="'.$arrmonth.'">'.date("F", mktime(0, 0, 0, $arrmonth, 10)).'</option>';
            }
        }

        $sqlcompanies='SELECT CompanyNo,Company FROM 1companies WHERE CompanyNo<=6';

        echo comboBox($link,$sqlcompanies,'CompanyNo','Company','companies');
        $req='<font color="red">*</font>';
        echo '<div style="background-color:#fff;padding:10px;border:1px solid #000;width:100%;"><form action="contriandloansinfo.php?w=UpdatePayment&refnofield='.$refnofield.'&GAID='.$which.'" method="POST" autocomplete=off>';
        echo 'Company: '.$req.' <input type="text" name="Company" list="companies" value="" size="15" required> ';
        echo 'Month: '.$req.' <select name="ApplicableMonth"><option value="">-- Select Month --</option>'.$optionm.'</select> ';
        echo ''.$refnofield.': '.$req.' <input type="text" name="SBRNo" value="" size="15" required> ';
        echo 'TotalAmount: '.$req.' <input type="text" name="TotalAmount" value="" size="10" required> ';
        echo 'DatePaid: '.$req.' <input type="date" name="DatePaid" value="" size="10" required> ';
        echo 'Remarks: <input type="text" name="Remarks" value="" size="25"> ';
        echo '<input style="background-color:blue;color:white;padding:3px;" type="submit" value="Update Payment" name="btnUpdatePayment" OnClick="return confirm(\'Are you sure you want to Update Payment?\');">';
        echo '</form></div><br><br>';
		

        
		$stmt=$link->query($sqlcompanies); $results=$stmt->fetchAll();

        
        echo '<h3>'.$title.' Summary Report</h3>';
        echo '<table border="1px solid black;" style="border-collapse:collapse;background-color:#fff;">';
        $th='';
        foreach($arrmonths AS $arrmonth){
            if($arrmonth<=date('m') OR date('Y')==$currentyr+1){
                $th.='<th>'.date("F", mktime(0, 0, 0, $arrmonth, 10)).'</th>';
            }
        }
        echo '<tr><th style="padding:3px;">Company</th>'.$th.'</tr>';
        $colorcount=0;
        $rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
        $rcolor[1]="FFFFFF";
        
        foreach($results AS $result){
            echo '<tr bgcolor="'. $rcolor[$colorcount%2].'"><td style="padding:3px;"><b>'.$result['Company'].'</b></td>';
            foreach($arrmonths AS $arrmonth){
                if($arrmonth<=date('m') OR date('Y')==$currentyr+1){
                    $sqlfetch='SELECT (SELECT CONCAT("<font color=\"blue\">SBRNo: ",RefNo,"</font><br> <font color=\"green\">Amount: ",TotalAmount,"</font><br> <font color=\"maroon\">DatePaid: ",DatePaid,"</font>",IFNULL(CONCAT("<br><font style=\"color:#800080;\">Remarks: ",Remarks,"</font>"),"")) FROM payroll_1govtpaymentsinfo WHERE CompanyNo='.$result['CompanyNo'].' AND ApplicableMonth='.$arrmonth.' AND GAID='.$which.') AS Details';
                    
                    $stmtfetch=$link->query($sqlfetch); $resultfetch=$stmtfetch->fetch();

                    echo '<td style="width:250px;font-size:8pt;padding:3px;">'.($resultfetch['Details']==''?'<font color="red"><center><font style="font-size:6.5pt;">'.str_repeat('-',5).' NO PAYMENT '.str_repeat('-',5).'</font></center></font>':$resultfetch['Details']).'</td>';
                }
            }
            echo '</tr>';
            $colorcount++;
        }
        echo '</table>';

	break;
	
    
	
	
	case 'UpdatePayment':

        
         $companyno=comboBoxValue($link,'1companies','Company',addslashes($_POST['Company']),'CompanyNo');

        
		$sql='UPDATE `payroll_1govtpaymentsinfo` SET RefNo="'.addslashes($_POST[$_GET['refnofield']]).'",TotalAmount="'.addslashes($_POST['TotalAmount']).'",Remarks="'.addslashes($_POST['Remarks']).'",DatePaid="'.addslashes($_POST['DatePaid']).'", EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE CompanyNo='.$companyno.' AND ApplicableMonth='.$_POST['ApplicableMonth'].' AND GAID='.$_GET['GAID'].'';
		
		$stmt = $link->prepare($sql);
		$stmt->execute();
		header("Location:contriandloansinfo.php?w=".$_GET['GAID']);
	
	break;

}
$link=null; $stmt=null; 

?>
</div>
