<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	if (!allowedToOpen(array(607,6071,6072,6073),'1rtc')){ echo 'No permission'; exit;}

        $which=isset($_REQUEST['which'])?$_REQUEST['which']:'Lookup';
        if($which=='BPAReport'){
	$showbranches=false; 
} else {
        $showbranches=true; 
}
        include_once('../switchboard/contents.php');
        
        if($which=='Lookup'){
                include('../backendphp/layout/showallbranchesbutton.php');
        }
	include_once('../backendphp/functions/getnumber.php'); include_once('../generalinfo/lists.inc');
	$branchno=isset($_REQUEST['BranchNo'])?$_REQUEST['BranchNo']:$_SESSION['bnum'];
	
	
        include_once('../backendphp/layout/linkstyle.php');
        echo '<br><br> <a id=\'link\' href="budgets.php?which=Lookup">Branch Pre-Approved Budgets</a> ';
        if (allowedToOpen(6073,'1rtc')){ 
            echo '<a id=\'link\' href="branchpreapprovedbudgetlist.php?w=BudgetList">Manage Budget Types</a> ';
        }
        echo ' <a id=\'link\' href="budgets.php?which=BPAReport">BPA Report</a> ';
        if (in_array($which, array('Add','Edit'))){
			if (isset($_POST['monthfrom'])){
				$begmonth=$_POST['monthfrom']; $closedmonth=$_POST['monthto']; $whichdata='static';
			}
			include_once('../backendphp/functions/monthsarray.php'); $sqledit='';
			
				if ($_GET['which']=='Add'){
					foreach ($months as $month) { $sqledit=$sqledit.',`'.str_pad ($month,2,'0',STR_PAD_LEFT).'`='.$_POST['BudgetPerMonth']; }
				} else {
					$begmonth=date('m');
					while ($begmonth<=12){
						$sqledit=$sqledit.', `'.str_pad ($begmonth,2,'0',STR_PAD_LEFT).'`= CASE WHEN `'.str_pad ($begmonth,2,'0',STR_PAD_LEFT).'` <> 0 THEN '.$_POST['BudgetPerMonth'].' ELSE 0 END';
						$begmonth++;
					}
				}
        }
        
        if (in_array($which, array('Lookup','BPAReport'))){
            include_once('../backendphp/functions/monthsarray.php'); 
            $columnnames=array(); $sql0=''; $sql1=''; $sqlm=''; $sqlen=''; $sqlcompare='';
            foreach ($months as $month) { 
            $monthname=date('M',strtotime(''.$currentyr.'-'.$month.'-01'));
            $monthfield='`'.str_pad ($month,2,'0',STR_PAD_LEFT).'`';
            $sqlm.=', FORMAT('.$monthfield.',0) AS `'.$monthname.'`'; 
            
            $columnnames[]=$monthname; //Lookup

                   if ($which=='BPAReport') {
                        
            $columnnames[]=$monthname.'ExEn';
            $sqlm.=', FORMAT(`'.$monthname.'ExEn`,0) AS `'.$monthname.'ExEn`';


                        $columnnamescompare[]=$monthname.'Exp';
                        $sql0.='SUM(IFNULL('.$monthfield.',0)) AS '.$monthfield.($month=="12"?' FROM `acctg_5branchpreapprovedbudgetspermonth` bm JOIN acctg_1branchpreapprovedbudgetlist bl ON bm.TypeID=bl.TypeID WHERE bl.BudgetDesc="'.$_POST['BudgetDesc'].'" GROUP BY `BranchNo`, `TypeID`' :', ') ;

                        $sql1.='TRUNCATE(Sum(case when Month(dm.`Date`)='.$month.' then de.Amount end),0) as `'.$monthname
                                .($month=="12"?'Exp`, TRUNCATE(Sum(ifnull(de.Amount,0)),0) as YearActual, TRUNCATE((`01`+`02`+`03`+`04`+`05`+`06`+`07`+`08`+`09`+`10`+`11`+`12`),0) as `YearBudget`  FROM `acctg_2depencashsub` de '
                                . 'join `acctg_2depositmain` dm on dm.TxnID=de.TxnID join `totalbudgets` bm on bm.BranchNo= de.BranchNo and bm.TypeID=de.TypeID JOIN acctg_1branchpreapprovedbudgetlist bl on bl.TypeID=bm.TypeID WHERE bl.BudgetDesc="'.$_POST['BudgetDesc'].'" group by de.`BranchNo`, de.`TypeID`' :'Exp`, ') ;

                        $sqlcompare.='(IFNULL(`'.$monthname.'Exp`,0)+IFNULL(`'.$monthname.'En`,0)) AS `'.$monthname.'ExEn`, ';

                        $sqlen.='TRUNCATE(Sum(case when Month(a.`Date`)='.$month.' then a.Amount end),0) as `'.$monthname
                                .($month=="12"?'En` from `approvals_2encashedexpenses` a join `acctg_1branchpreapprovedbudgetlist` l on l.TypeID=a.TypeID
                                join `1branches` b on b.BranchNo=a.BranchNo WHERE BudgetDesc="'.$_POST['BudgetDesc'].'" group by a.`BranchNo`, a.`TypeID`' :'En`, ');
                        // $sqlenc=$sqlenc.'`'.$monthname.'En`, ';
                   } 
            }

            $sql='SELECT bud.*, bl.BudgetDesc, bud.Remarks AS FullRemarks, Branch, ca.ShortAcctID, e.Nickname as EncodedBy'.$sqlm.', FORMAT((`01`+`02`+`03`+`04`+`05`+`06`+`07`+`08`+`09`+`10`+`11`+`12`),0) as `YearBudget` from `acctg_5branchpreapprovedbudgetspermonth` bud join 1branches b on b.BranchNo=bud.BranchNo
            join acctg_1branchpreapprovedbudgetlist bl on bl.TypeID=bud.TypeID
            left join 1employees e on e.IDNo=bud.EncodedByNo
            join acctg_1chartofaccounts ca on ca.AccountID=bl.AccountID ';
            }
	
switch ($which){
        case 'Add':
	
	if (!allowedToOpen(6071,'1rtc')) { header ("Location:budgets.php?msg=No_permission");	}
        if ((!allowedToOpen(6072,'1rtc')) and ($_POST['BudgetPerMonth']>1000)) { goto skipaddnew;}
        
	$sql='Insert into `acctg_5branchpreapprovedbudgetspermonth` 
            SET `BranchNo`='.$branchno.',`TypeID`='.$_POST['TypeID'].',`Specifics`="'.$_POST['Specifics']
                .'",`Remarks`="'.$_POST['Remarks'].'",`BudgetPerMonth`='.$_POST['BudgetPerMonth'].',
                `EncodedByNo`='.$_SESSION['(ak0)'].',`TimeStamp`=Now() '.$sqledit;
        // echo $sql;break;
        $stmt=$link->prepare($sql);  $stmt->execute();
        skipaddnew:
        header("Location:budgets.php");
        break;
		
    case 'Delete':
	if (!allowedToOpen(6071,'1rtc')) { header ("Location:budgets.php?msg=No_permission");	}
	$sql='Delete from acctg_5branchpreapprovedbudgetspermonth where budgetid='.$_GET['budgetid'];
	$stmt=$link->prepare($sql);
        //echo $sql;break;
        $stmt->execute();
        header("Location:budgets.php");
	break;
    case 'Edit':
	if (!allowedToOpen(6071,'1rtc')) { header ("Location:budgets.php?msg=No_permission");	}
        if (allowedToOpen(6072,'1rtc')) { $condition='';} else {$condition=' AND '.$_POST['BudgetPerMonth'].'<=1000 ';}
	$sql='Update acctg_5branchpreapprovedbudgetspermonth set Remarks="'.$_POST['Remarks'].'",`Specifics`="'.$_POST['Specifics'].'", BudgetPerMonth='.$_POST['BudgetPerMonth'].', EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now() '.$sqledit.' WHERE budgetid='.$_GET['budgetid'].$condition;
	// echo $sql;break;
	$stmt=$link->prepare($sql);
        $stmt->execute();
        header("Location:budgets.php");
	break;
    case 'Lookup':
        $title=($show==1?'Monthly Budgets - All Branches':'Monthly Budgets for '.$_SESSION['@brn']);
        
        array_unshift($columnnames,'Branch','ShortAcctID','BudgetPerMonth','BudgetDesc','Specifics','Remarks','FullRemarks');
        array_push($columnnames,'YearBudget','EncodedBy','TimeStamp');
	include_once $path.'/acrossyrs/commonfunctions/renderspeciallist.php';
	genericList('SELECT * FROM acctg_1branchpreapprovedbudgetlist',$link,'BudgetType','TypeID','BudgetDesc');
        $formdesc="Accounting manager must encode values above P1,000.<br><br>Add Budget &nbsp &nbsp &nbsp
	<form method='post' action='budgets.php?which=Add' style='display:inline'>
	Type ID<input type='text' size=15 name='TypeID' list='BudgetType'>
	Monthly Budget<input type='text' size=15 name='BudgetPerMonth' value=0>
	MonthFrom<input type='text' size=5 name='monthfrom' value='".date('m')."'>
	MonthTo<input type='text' size=5 name='monthto' value='12'><br><br>
	Specifics (shown in Deposit page) <input type='text' size=25 name='Specifics'> Remarks<input type='text' size=25 name='Remarks'>
	<input type='submit' name='submit' value='Submit'></form><br><br>";
	$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'ShortAcctID'); $columnsub=$columnnames;
        $sql.=' ' . ($show==1?'':' where bud.BranchNo='.$branchno.' AND b.Active<>0').' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');  // echo $sql; break;
        // echo $sql.'<br><br>';
        
        if (allowedToOpen(6071,'1rtc')) { $columnstoedit=array('AccountID','BudgetPerMonth','Specifics','Remarks');
        $txnidname='budgetid'; $editprocess='budgets.php?which=Edit&budgetid=';$editprocesslabel='Change!'; $delprocess='budgets.php?which=Delete&budgetid=';}
	include('../backendphp/layout/displayastableeditcells.php');	
     
     //case 'Compare':
        skipadd:
// 	$sql0='create temporary table totalbudgets as SELECT bm.`BranchNo`, bm.`TypeID`, '.$sql0;
//         echo $sql0.'<br><br>';
//         $sql1='create temporary table comparebudgets as SELECT bm.*, '.$sql1;
//         echo $sql1.'<br><br>';
// 	$sql='Select Branch, bl.BudgetDesc, '.$sqlcompare;
//         echo $sql.'<br><br>';
//        // if($_SESSION['(ak0)']==1002){ echo $sql0.'<br><br>'.$sql1.'<br><br>'.$sql; break;}
// 	array_unshift($columnnamescompare,'Branch','BudgetDesc','YearBudget', 'YearActual', 'YearDiff');
//         $columnnames=$columnnamescompare;
// 	//
// 	$title=($show==1?'Compare Budgets - All Branches':'Compare Budgets for '.$_SESSION['@brn']);
// 	$link->query($sql0); $link->query($sql1); 
// 	$sql=$sql.'FORMAT(YearBudget,0) AS YearBudget, FORMAT(YearActual,0) AS YearActual, FORMAT(YearBudget-YearActual,0) as YearDiff from comparebudgets cb join `1branches` b on b.BranchNo=cb.BranchNo join `acctg_1branchpreapprovedbudgetlist` bl on bl.TypeID=cb.TypeID ORDER BY BudgetDesc';
//        // if($_SESSION['(ak0)']==1002){ echo $sql0.'<br><br>'.$sql; }
//         unset($editprocess,$sortfield);
//         echo '<br><br>';
//         $subtitle='Expense and Available';
// 	include ('../backendphp/layout/displayastableonlynoheaders.php');
	break;


        case 'BPAReport':

                $title='BPA Report';
        
                array_unshift($columnnames,'Branch','ShortAcctID','BudgetPerMonth','BudgetDesc','Specifics','Remarks','FullRemarks');
                array_push($columnnames,'YearBudget','EncodedBy','TimeStamp');
                include_once $path.'/acrossyrs/commonfunctions/renderspeciallist.php';
                genericList('SELECT * FROM acctg_1branchpreapprovedbudgetlist',$link,'BudgetType','BudgetDesc','TypeID');
                
                
                echo "<br><form style='display:inline;' method='post' action='budgets.php?which=BPAReport'> Short Account Desc <input type='text' size=25 name='BudgetDesc' list='BudgetType' placeholder='BudgetDesc'".(isset($_POST['BudgetDesc'])?'value="'.$_POST['BudgetDesc'].'"':'')."> <input type='submit' name='btnLookUp' value='Lookup'></form> <br><br>";

           
                
                // $columnsub=$columnnames;
               
               
                

        if(isset($_POST['btnLookUp'])){

                $sql0='create temporary table totalbudgets as SELECT bm.`BranchNo`, bm.`TypeID`, '.$sql0;
                $link->query($sql0);

                $sql1='create temporary table comparebudgets as SELECT bm.*, '.$sql1;
                $link->query($sql1);

                //encashment
                $sqlen='CREATE TEMPORARY TABLE encashments AS Select a.BranchNo, l.TypeID, '.$sqlen;
                $link->query($sqlen); 

                $sqlene='CREATE TEMPORARY TABLE expandenc Select bl.BudgetDesc, '.$sqlcompare.$sqlenc.' cb.BranchNo from comparebudgets cb join `1branches` b on b.BranchNo=cb.BranchNo join `acctg_1branchpreapprovedbudgetlist` bl on bl.TypeID=cb.TypeID JOIN encashments e ON cb.BranchNo=e.BranchNo ORDER BY Branch';
                $link->query($sqlene);
                // echo $sqlene.'<br><br>';

                $sql.=' LEFT JOIN expandenc ene ON bud.BranchNo=ene.BranchNo WHERE bl.BudgetDesc="'.$_POST['BudgetDesc'].'" AND b.Active<>0 GROUP BY bud.BranchNo ORDER BY Branch ASC';

               
                include('../backendphp/layout/displayastablenosort.php');	
             
           
               
                
               
                
             }
        break;
}
nodata:
     $link=null; $stmt=null; 
?>