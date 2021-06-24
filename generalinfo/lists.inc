<?php

function renderlist($whichlist){
    global $currentyr;
    $linkinfunction=connect_db($currentyr.'_1rtc',0);
    
switch ($whichlist) {
    case 'areas':        
        $listname='areas';
        $listsql='SELECT AreaNo,Area from 0area';
        $listvalue='Area';
        $listlabel='AreaNo';
        $listcaption='For Area';
        break;
    case 'zipplaces':
    //Combination of zipcode, barangay and city
    $listname='zipplaces';
    $listsql = 'SELECT CONCAT(ZipCode, " - ", BarangayOrTown, ", " , CityOrProvince) AS PLACE, ZipCode FROM `1_gamit`.`0zipcodes`';
    $listvalue = 'PLACE';
    $listcaption = 'Zip Codes';
    $listlabel='ZipCode';
    break;
    case 'branches':        
        $listname='branches';
        if (!in_array($_SESSION['&pos'],array(13))) { // accounting is limited to company
            $listsql='SELECT BranchNo,Branch from 1branches where Active=1 AND PseudoBranch NOT IN (1,3) ORDER BY Branch';
        } else {
            $listsql='SELECT BranchNo,Branch from 1branches where Active=1 AND CompanyNo='.$_SESSION['*cnum'].' ORDER BY Branch';
        }
        $listvalue='BranchNo';
        $listlabel='Branch';
        $listcaption='For Branch';
        break;
    case 'branchnames': 
    case 'branchnamesall':        
        $listname='branchnames';
        if (!in_array($_SESSION['&pos'],array(13))) { // accounting is limited to company
        $listsql='SELECT BranchNo,Branch from 1branches '.($whichlist=='branchnames'?' WHERE Active<>0 ':'').' ORDER BY Branch';} else {    
        $listsql='SELECT BranchNo,Branch from 1branches WHERE Active<>0 '.($whichlist=='branchnames'?' AND CompanyNo='.$_SESSION['*cnum']:'').' ORDER BY Branch';}
        $listvalue='Branch';
        $listlabel='BranchNo';
        $listcaption='For Branch';
        break;
    case 'branchesper':// not used yet bec choosebranchheader has direct list
        $listname='branchesper';
        $listsql='SELECT b.BranchNo,b.Branch from `1branches` as b
join `attend_1branchgroups` as bt on b.BranchNo=bt.BranchNo
where b.BranchNo not in (1,12,19) and (bt.ARGroup='.$_SESSION['(ak0)'].' or bt.GroupHead='.$_SESSION['(ak0)'].' or AcctgGroup='.$_SESSION['(ak0)'].') order by b.Branch;';
        $listvalue='BranchNo';
        $listlabel='Branch';
        $listcaption='For Branch';
        break;
    case 'companies':
        $listname='companies';
        $listsql='SELECT Company, concat(`CompanyName`, " - ",CompanyNo) as CompanyName FROM `1companies` where Active<>0';
        $listvalue='Company';
        $listlabel='CompanyName';
        $listcaption='CompanyName';
        break;
    case 'companiesshortname':
        $listname='companiesshortname';
        $listsql='SELECT Company, concat(`CompanyName`, " - ",CompanyNo) as CompanyName FROM `1companies` where Active<>0';
        $listvalue='Company';
        $listlabel='CompanyName';
        $listcaption='Company';
        break;
    case 'companynumbers':
        $listname='companynumbers';
        $listsql='SELECT Company, CompanyNo FROM `1companies` where Active<>0';
        $listvalue='CompanyNo';
        $listlabel='Company';
        $listcaption='Company';
        break;
    case 'clientswhole':
    case 'clientsnodatedcheck':
    case 'clients':
        $listname=($whichlist=='clientsnodatedcheck'?'clientsnodatedcheck':'clients');
        $listsql='SELECT '.($whichlist=='clientswhole'?'`ClientName`':'Left(`ClientName`,20)'). ' as `ClientName`, ClientNo FROM `acctg_1clientsperbranch` WHERE `BranchNo`='.$_SESSION['bnum'] .($whichlist=='clientsnodatedcheck'?' and `ClientNo`<>10004 ':'').' ORDER BY ClientName';
        $listvalue='ClientName';
        $listlabel='ClientNo';
        $listcaption='Client';
		// echo $listsql; exit();
        break;
    case 'clientsemployees':
        $listname='clientsemployees';
        $listsql='SELECT Left(`ClientName`,20) as `ClientName`, ClientNo FROM `acctg_0uniclientsalesperson` WHERE `BranchNo`='.$_SESSION['bnum'] .' ORDER BY ClientName';
        $listvalue='ClientName';
        $listlabel='ClientNo';
        $listcaption='Client';
        break;
    case 'allclients': //for printsoa.php and assign clients to branches
        $listname='allclients';
        $listsql='SELECT Left(`ClientName`,20) as `ClientName`, ClientNo FROM `1clients` ORDER BY ClientName';
        $listvalue='ClientName';
        $listlabel='ClientNo';
        $listcaption='Client';
        break;
    case 'clientsnos':
        $listname='clientsnos';
        $listsql='SELECT Left(`ClientName`,20) as ClientName, ClientNo FROM `acctg_1clientsperbranch` WHERE `BranchNo`='.$_SESSION['bnum'] .' ORDER BY ClientName';
        $listvalue='ClientNo';
        $listlabel='ClientName';
        $listcaption='Client';
        break;
    case 'arclients':
        $listname='arclients';
        $listsql='  SELECT 
                        LEFT(`ClientName`, 20) AS `ClientName`, c.ClientNo
                    FROM
                        `acctg_1clientsperbranch` AS c
                    WHERE
                        ((ClientNo NOT IN (SELECT 
                                ClientNo
                            FROM
                                `acctg_34holdstatus`
                            WHERE
                                ((HoldonRecord + HoldfromTerms + HoldfromLimit) <> 0)
                                    AND HoldonRecord <> 2))
                            AND c.BranchNo = '.$_SESSION['bnum'].'
                            AND c.ARClientType <> 0)
                            OR ClientNo = 10004 OR ClientNo = 10001 GROUP BY ClientNo
                    ORDER BY ClientName';
        $listvalue='ClientName';
        $listlabel='ClientNo';
        $listcaption='Client';
        break;
    case 'unpdclient': //NOT FINISHED
        $listname='unpdclient';
        $listsql='SELECT `ClientName`, ClientNo FROM `acctg_1clientsperbranch` as c join `comments_clientsonhold` as h on c.ClientNo=h.OnHold WHERE c.`BranchNo`='.$_SESSION['bnum'] .' and c.ARClientType=1 and h.Hold=0 Group By c.ClientNo ORDER BY ClientName';
        $listvalue='ClientName';
        $listlabel='ClientNo';
        $listcaption='Client';
        break;
    case 'suppliers':
        $listname='suppliers';
        $listsql='SELECT `SupplierName`, SupplierNo FROM `1suppliers` WHERE Inactive=0 ORDER BY SupplierName';
        $listvalue='SupplierName';
        $listlabel='SupplierNo';
        $listcaption='Supplier';
        break;
    case 'employees': //used in Approvals
        $listname='employees';
        $listsql='select concat(e.`Nickname`,\' \',e.`SurName`) AS `Name`,concat(e.`IDNo`,\' -\',e.`FirstName`,\' \',e.`SurName`,\' - \',b.`Branch`,\' (\',b.`BranchNo`,\')\') AS `NameandBranch` from ((`1employees` as e join `attend_1defaultbranchassign` as d on((e.`IDNo` = d.`IDNo`))) join `1branches` as b on((d.`DefaultBranchAssignNo` = b.`BranchNo`))) where (e.`IDNo` > 1002) and (e.Resigned=0) order by `NameandBranch`;';
        $listvalue='Name';
        $listlabel='NameandBranch';
        $listcaption='Employee';
        break;
    case 'employeeid': 
        $listname='employeeid';
        $listsql='select e.`IDNo` AS `IDNo`,concat(e.`Nickname`,\' \',e.`FirstName`,\' \',e.`SurName`,\' - \',b.`Branch`,\' (\',b.`BranchNo`,\')\') AS `NameandBranch` from ((`1employees` as e join `attend_1defaultbranchassign` as d on((e.`IDNo` = d.`IDNo`))) join `1branches` as b on((d.`DefaultBranchAssignNo` = b.`BranchNo`))) where (e.`IDNo` > 1002) and (e.Resigned=0) order by `NameandBranch`;';
        $listvalue='IDNo';
        $listlabel='NameandBranch';
        $listcaption='Employee ID';
        break;
    case 'employeesforlist':
        $listname='employeesforlist';
        $listsql='SELECT Left(concat(e.`Nickname`,\' \',e.`SurName`),20) as `ClientName`, IDNo as ClientNo FROM `1employees` as e  ORDER BY Nickname';
        $listvalue='ClientName';
        $listlabel='ClientNo';
        $listcaption='Client';
        break;
    case 'positions':
        $listname='positions';
        $listsql='SELECT Position, PositionID FROM `attend_1positions` p JOIN `attend_0joblevels` jl ON jl.JobLevelID=p.JobLevelID ORDER BY JobLevelID,jl.JobLevelID;';
        $listvalue='Position';
        $listlabel='PositionID';
        $listcaption='Position';
        break;
    case 'salespersons': //not used yet; will be relevant when POS is implemented
        $listname='salespersons';
        $listsql='';
        $listvalue='';
        $listlabel='';
        $listcaption='';
        break;
    case 'payperiods':
        $listname='payperiods';
        $listsql='SELECT PayrollID, concat(PayrollID, " : ", FromDate, " - ", ToDate) as PayPeriod FROM payroll_1paydates;';
        $listvalue='PayrollID';
        $listlabel='PayPeriod';
        $listcaption='For Payroll Period';
        break;
    case 'activepayperiods':
        $listname='activepayperiods';
        $listsql='SELECT PayrollID, concat(PayrollID, " : ", FromDate, " - ", ToDate) as PayPeriod FROM payroll_1paydates where Posted=0;';
        $listvalue='PayrollID';
        $listlabel='PayPeriod';
        $listcaption='For Payroll Period';
        break;
    case 'leaves':
        $listname='leaves';
        $listsql='SELECT LeaveNo, LeaveName FROM `attend_0leavetype`;';
        $listvalue='LeaveNo';
        $listlabel='LeaveName';
        $listcaption='Leave Type';
        break;
  /*   case 'taxclass':
        $listname='taxclass';
        $listsql='SELECT Classification FROM `payroll_0taxexemptions`;';
        $listvalue='Classification';
        $listlabel='Classification';
        $listcaption='TaxClassification';
        break; */
    case 'civilstatus':
        $listname='civilstatus';
        $listsql='SELECT \'Single\' AS choice, \'S\' as choicevalue UNION ALL SELECT \'Married\', \'M\'';
        $listvalue='choicevalue';
        $listlabel='choice';
        $listcaption='Civil Status';
        break;
    case 'yesno':
        $listname='yesno';
        $listsql='SELECT \'yes\' AS choice, 1 as choicevalue UNION ALL SELECT \'no\', 0';
        $listvalue='choicevalue';
        $listlabel='choice';
        $listcaption='Yes or No';        
        break;
    case 'paytype':
        $listname='paytype';
        $listsql='SELECT \'Cash\' AS choice, 1 as choicevalue UNION ALL SELECT \'Charge/Check\', 2 UNION ALL SELECT \'DirectDeposit\', 3';
        $listvalue='choicevalue';
        $listlabel='choice';
        $listcaption='PayType';        
        break;
    case 'movetype':
        $listname='movetype';
        $listsql='SELECT \'Active\' AS choice, 0 as choicevalue UNION ALL SELECT \'NonStock\', 1 UNION ALL SELECT \'NonMoving\', 3 UNION ALL SELECT \'Obsolete\', 5';
        $listvalue='choicevalue';
        $listlabel='choice';
        $listcaption='MoveType';        
        break;
    case 'directoragency':
        $listname='directoragency';
        $listsql='SELECT \'Direct\' AS choice, 0 as choicevalue UNION ALL SELECT \'HR Network\', 1 UNION ALL SELECT \'Prime Movers\', 2';
        $listvalue='choicevalue';
        $listlabel='choice';
        $listcaption='Direct or Agency';        
        break;
    case 'withsat':
        $listname='withsat';
        $listsql='SELECT \'Wholeday Saturdays\' AS choice, 2 as choicevalue UNION ALL SELECT \'Halfday Saturdays\', 1 UNION ALL SELECT \'No Saturdays\', 0';
        $listvalue='choicevalue';
        $listlabel='choice';
        $listcaption='Saturdays';        
        break;
    case 'typeofday':
        $listname='typeofday';
        $listsql='SELECT TypeOfDayNo,TypeofDayName FROM attend_0typeofday;';
        $listvalue='TypeOfDayNo';
        $listlabel='TypeofDayName';
        $listcaption='TypeofDay';        
        break;
    case 'weekdays':
        $listname='weekdays';
        $listsql='SELECT \'Sunday\' AS choice, 6 as choicevalue UNION ALL SELECT \'Monday\', 0 UNION ALL SELECT \'Tuesday\', 1 UNION ALL SELECT \'Wednesday\', 2 UNION ALL SELECT \'Thursday\', 3 UNION ALL SELECT \'Friday\', 4 UNION ALL SELECT \'Saturday\', 5';
        $listvalue='choicevalue';
        $listlabel='choice';
        $listcaption='Choose Restday';        
        break;
    case 'payrolladjtype':
        $listname='payrolladjtype';
        $listsql='SELECT AdjustTypeNo, AdjustType FROM `payroll_0acctid`';
        $listvalue='AdjustTypeNo';
        $listlabel='AdjustType';
        $listcaption='Adjustment Type';        
        break;
    case 'categories':
        $listname='categories';
        $listsql='SELECT CatNo, Category from invty_1category order by Category';
        $listvalue='Category';
        $listlabel='CatNo';
        $listcaption='Category';
        break;
    case 'items':
        $listname='items';
        $listsql='SELECT ItemCode, concat(ItemDesc,\' - \',Category) as ItemDesc from invty_1items join invty_1category on invty_1category.CatNo=invty_1items.CatNo order by Category,ItemDesc';
        $listvalue='ItemCode';
        $listlabel='ItemDesc';
		 
        $listcaption='ItemCode';
        break;
    case 'repackitems':
        $listname='repackitems';
        $listsql='SELECT i.ItemCode, concat(Category,\' - \',ItemDesc) as ItemDesc from invty_1items i join invty_1category c on c.CatNo=i.CatNo join invty_1itemsforrepack p on i.ItemCode=p.RepackItemCode order by Category,ItemDesc';
        $listvalue='ItemCode';
        $listlabel='ItemDesc';
        $listcaption='ItemCode';
        break;
    case 'bulkitems':
        $listname='bulkitems';
        $listsql='SELECT i.ItemCode, concat(Category,\' - \',ItemDesc) as ItemDesc from invty_1items i join invty_1category c on c.CatNo=i.CatNo join invty_1itemsforrepack p on i.ItemCode=p.BulkItemCode order by Category,ItemDesc';
        $listvalue='ItemCode';
        $listlabel='ItemDesc';
        $listcaption='ItemCode';
        break;
    case 'vacuumitems':
        $listname='vacuumitems';
        $listsql='SELECT i.ItemCode, concat(Category,\' - \',ItemDesc) as ItemDesc from invty_1items i join invty_1category c on c.CatNo=i.CatNo where c.CatNo=90 order by Category,ItemDesc';
        $listvalue='ItemCode';
        $listlabel='ItemDesc';
        $listcaption='ItemCode';
        break;
    case 'undeliveredrequestsOUT':
        $listname='undeliveredrequestsOUT';
        $listsql='select ud.RequestNo, b.`Branch` as ClientBranch from invty_44undeliveredreq as ud
        join `1branches` as b on ud.BranchNo=b.BranchNo 
        where ud.Posted<>0 and SendBal<>0 and SupplierBranchNo='.$_SESSION['bnum'] . ' group by ud.SupplierBranchNo, ud.RequestNo, ud.BranchNo';
        $listvalue='RequestNo';
        $listlabel='ClientBranch';
        $listcaption='RequestNo';
        break;
    case 'undeliveredrequestsIN':
        $listname='undeliveredrequestsIN';
        $listsql='select ud.RequestNo, b.`Branch` as SupplierBranch from invty_44undeliveredreq as ud
        join `1branches` as b on ud.SupplierBranchNo=b.BranchNo 
        where ud.Posted<>0 and RcvBal<>0 and ud.BranchNo='.$_SESSION['bnum'] . ' group by ud.SupplierBranchNo, ud.RequestNo, ud.BranchNo';
        $listvalue='RequestNo';
        $listlabel='SupplierBranch';
        $listcaption='RequestNo';
        break;
    
    case 'banks':
    case 'allbanks':
        $listname='banks';
        $listsql='SELECT m.ShortAcctID, m.AccountID, m.AcctNo, m.AcctName, '.$_SESSION['bnum'].' FROM `banktxns_1maintaining` as m 
WHERE (((m.OwnedByCompany)='.$_SESSION['*cnum'].')) UNION SELECT m.ShortAcctID, m.AccountID, m.AcctNo, m.AcctName, d.BranchNo FROM `banktxns_1maintaining` as m left JOIN `banktxns_branchdefaultbank` as d ON m.AccountID=d.BankAcctID '.($whichlist=='allbanks'?' group by m.AccountID union all SELECT ca.ShortAcctID, ca.AccountID, "" as AcctNo, "" as AcctName,'.$_SESSION['bnum'].' as BranchNo FROM `acctg_1chartofaccounts` ca where AccountID=705':'WHERE (((d.BranchNo)='.$_SESSION['bnum'].'))').' order by ShortAcctID';
        $listvalue='AccountID'; 
        $listlabel='ShortAcctID';
        $listcaption='Bank';
        break;
    case 'tools':
        $listname='tools';
        $listsql='SELECT ToolID,ToolDesc FROM audit_1tools;';
        $listvalue='ToolID';
        $listlabel='ToolDesc';
        $listcaption='Tools';
        break;
    
    default:
        goto nolist;
        break;
}
?>
<datalist id="<?php echo $listname ?>"> 
<?php  
		foreach ($linkinfunction->query($listsql) as $row) {
                ?>
                <option value="<?php echo $row[$listvalue]; ?>" label="<?php echo $row[$listlabel]; ?>"></option>
                <?php
                } // end while
                ?>
</datalist id="<?php echo $listname ?>">
<?php
nolist:
}

function getValue($linkinfunction,$table,$givenfield,$givenvalue,$getfield){
    global $currentyr;
    $listsql="SELECT `" . $getfield . "` from `" . $table . "` WHERE " . $givenfield . " Like '" . addslashes($givenvalue) ."' Limit 1";

    // if ($_SESSION['(ak0)']==1002) {echo $listsql; }
    $stmt=$linkinfunction->query($listsql);
    $result=$stmt->fetch(PDO::FETCH_ASSOC);
    return $result[$getfield];
}
function linkList($which){
    global $currentyr;
     
    switch ($which) {
        case 1:
            
            break;
        case 2:
            
            break;
        case 3:
            
            break;
        case 4:
            
            break;
        case 5:
            
            break;
        default:
            break;
    }
    return $linkinfunction;
}

function renderListWithCondition($whichlist,$uniqid){
     global $currentyr;
     
    switch ($whichlist) {
    case 'items':
        $listname='itemspercat';
        $listsql='SELECT ItemCode, Category, ItemDesc from invty_1items join invty_1category on invty_1category.CatNo=invty_1items.CatNo where invty_1items.CatNo='.$uniqid;
        $listvalue='ItemCode';
        $listlabel='ItemDesc';
        $listcaption='ItemCode';
        break;
    case 'employeesperposition': 
        $listname='employeesperposition';
        $listsql='SELECT e.IDNo, concat(Nickname," ",Surname) as FullName FROM attend_30currentpositions p join 1employees e on p.IDNo=e.IDNo where PositionID in '.$uniqid;
        $listvalue='FullName';
        $listlabel='IDNo';
        $listcaption='EmployeesPerPosition';
        break;
    default:
        goto nolist;
        break;
}
?>
<br><datalist id="<?php echo $listname ?>" style="height: 150px; width: 150px; overflow: scroll">

<?php  
		$linkinfunction=connect_db($currentyr.'_1rtc',0);
                foreach ($linkinfunction->query($listsql) as $row) {
                ?>
                <option value="<?php echo $row[$listvalue]; ?>" label="<?php echo $row[$listlabel]; ?>"></option>
                <?php
                } // end while
                ?>
</datalist id="<?php echo $listname ?>">
<?php

nolist:
    
}

