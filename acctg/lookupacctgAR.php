<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';

if($_GET['w']=="LookupPic")  { goto skipcontents2;}
$showbranches=true;
include_once('../switchboard/contents.php');
skipcontents:

function generateBranchPDCTable($currentyr, $canDoShowAll, $canShowPerBranch){
  if ($canDoShowAll) {
    include('../backendphp/layout/showallbranchesbutton.php');
    $sqlcondition=($show==1?'':' WHERE  up.`BranchNo`='.$_SESSION['bnum']);    
  } 
  elseif ($canShowPerBranch) {
    $sqlcondition=' WHERE  up.`BranchNo`='.$_SESSION['bnum'];
  } 
  else {
    $sqlcondition=' WHERE  up.`BranchNo` in (SELECT `BranchNo` FROM `attend_1branchgroups` WHERE TeamLeader='.$_SESSION['(ak0)'].' OR SAM='.$_SESSION['(ak0)'].') ';
  }
  return <<<SQL
    CREATE TEMPORARY TABLE `branchpdc` AS
      SELECT 
        up.PDCID AS TxnID, up.* , b.Branch, c.ClientName, 
        (Cash+PDC) as Total, IFNULL(PDCRemarks,"") AS AcctgRemarks,
        IFNULL(ARPDCRemarks,"") AS ARRemarks , 
        e.Nickname AS AcctgBy, e1.Nickname AS ARBy, 
        e2.Nickname AS SendToBankBy, e3.Nickname AS OfcAcceptedBy, 
        e4.Nickname AS WithBankBy, pdcr.TimeStamp AS AcctgTS, 
        pdcr.ARTimeStamp AS ARTS
      FROM acctg_undepositedclientpdcs up
      join 1branches b on b.BranchNo=up.BranchNo
      join 1clients c on c.ClientNo=up.ClientNo 
      LEFT JOIN acctg_2provcollectsubpdcremarks pdcr ON up.PDCID=pdcr.PDCID
      LEFT JOIN 1employees e ON e.IDNo=pdcr.EncodedByNo
      LEFT JOIN 1employees e1 ON e1.IDNo=pdcr.AREncodedByNo
      LEFT JOIN 1employees e2 ON e2.IDNo=up.SendToBankByNo 
      LEFT JOIN 1employees e3 ON e3.IDNo=up.OfcAcceptedByNo 
      LEFT JOIN 1employees e4 ON e4.IDNo=up.WithBankByNo
      {$sqlcondition}
      ORDER BY up.DateofPDC, ClientName, PDCNo;
SQL;
}


// check if allowed
$allowed=array(546,547,548,549,550,5481,5482,5501,5502,5503,5504,5505,5506); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit; }
allowed:
// end of check
if (!isset($_REQUEST['print'])) { include_once('../switchboard/contents.php');}
skipcontents2:
$showbranches=true;

//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="f6ebf9";
        $rcolor[1]="FFFFFF";
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

$whichqry=$_GET['w'];

//allowedToOpen Variables are assigned to variables so that database will only execute these statements once
$canOpenUndepositedPDC = allowedToOpen(550,'1rtc');
$canDoShowAll = allowedToOpen(5501, '1rtc');
$canShowPerBranch = allowedToOpen(5502, '1rtc');
$canEditPDCRemarksGenAcctg = allowedToOpen(5504, '1rtc');
$canEditPDCRemarksCSO = allowedToOpen(5505, '1rtc');
$canGenerateOutputforUBP = allowedToOpen(5509, '1rtc');

switch ($whichqry){
case 'UndepositedPDCs': 

  if (!$canOpenUndepositedPDC) { 
    echo 'No permission'; exit; 
    exit;
  }
  
  $title='Undeposited PDC\'s'; 

  $sortfield=(!isset($_POST['sortfield'])?' `DateofPDC`,`ClientName`,`PDCNo`':$_POST['sortfield']);
  
  $columnnames=array('CRNo','DateofPDC','ClientName','PDCBank', 'ClientCheckBankAccountNo','PDCNo','PDCBRSTN','Branch','Cash','PDC','ARRemarks','AcctgRemarks','Send_To_Bank');

  if ($canDoShowAll) {
    include('../backendphp/layout/showallbranchesbutton.php');
    $sqlcondition=($show==1?'':' WHERE  up.`BranchNo`='.$_SESSION['bnum']);
    if ($canEditPDCRemarksGenAcctg) {
      //Acctg remarks is a duplicate
      //$columnstoedit=array('AcctgRemarks'); 
      //$columnnames[]='AcctgRemarks';
    }
    $showtotals=true; 
    $showgrandtotal=true; 
    $runtotal=true;
  } 
  elseif ($canShowPerBranch) {
    $sqlcondition=' WHERE  up.`BranchNo`='.$_SESSION['bnum'];
  } 
  else {
    $sqlcondition=' WHERE  up.`BranchNo` in (SELECT `BranchNo` FROM `attend_1branchgroups` WHERE TeamLeader='.$_SESSION['(ak0)'].' OR SAM='.$_SESSION['(ak0)'].') ';
  }

  $sql0 = generateBranchPDCTable($currentyr, $canDoShowAll, $canShowPerBranch);
  $stmt0=$link->prepare($sql0); 
  $stmt0->execute();

  $orderby='ORDER BY '.$sortfield.' '.(isset($_POST['sortarrange'])?$_POST['sortarrange']:' ASC');
  $coltototal='Total';
  $txnid='PDCID'; 
  $columnsub=$columnnames;

  $subtitle='With Branch';
  $sql='SELECT *, IF(SendToBank=1,"Send","") AS Send_To_Bank FROM `branchpdc` WHERE WithBank=0 AND AtOffice=0 AND ISNULL(AcctgAcceptedByNo) '.$orderby;

  $formdesc=<<<HTML
    </i><br><a href="lookupacctgAR.php?w=SendTo">PDC Warehousing Receiving List</a><br><br>
    For MM & Cavite dated and postdated checks: Client -> Branch -> AR -> Bank  (AR encodes deposits)
    <br>
    For provincial dated and postdated checks, as chosen by AR: Client -> Branch -> Bank  (AR encodes deposits)
    <br>
    For unsure postdated checks: Client -> Branch -> AR -> Acctg -> Bank  (Acctg encodes deposits)<br><br><i>All checks must be with either with the bank or accounting.
    <br><br>
    <h4>{$subtitle}</h4>
HTML;

  include_once('../backendphp/layout/showencodedbybutton.php'); 
  echo '<br><br>';

  if ($showenc==1) {
    array_push($columnnames,'SendToBankBy','ARBy', 'ARTS','AcctgBy', 'AcctgTS','OfcAcceptedBy','OfcAcceptTS','WithBankBy','WithBankTS'); 
  }

  /* choice of options */
  $remarks='pdcremarks.php?w=Edit&PDCID='; 
  $remarkslabel='Remarks';
  $ofcaccept='pdcremarks.php?w=OfcAccept&PDCID='; 
  $ofcacceptlabel='Ofc Accept';
  //No more send to bank for now.
  //$sendtobank='pdcremarks.php?w=SendToBank&PDCID='; 
  //$sendtobanklabel='Send To Bank';
  $withbank='pdcremarks.php?w=WithBank&PDCID='; 
  $withbanklabel='With Bank';
  $acctgaccept='pdcremarks.php?w=AcctgAccept&PDCID='; 
  $acctgacceptlabel='Acctg Accept';
  /* end of choices */

  if ($canEditPDCRemarksCSO) { 
    $editprocess=$remarks;
    $editprocesslabel=$remarkslabel;
    $addlprocess=$ofcaccept;
    $addlprocesslabel=$ofcacceptlabel;
    //$addlprocess2=$sendtobank;
    //$addlprocesslabel2=$sendtobanklabel;
  }
  elseif ($canEditPDCRemarksGenAcctg) {
    $editprocess=$remarks; 
    $editprocesslabel=$remarkslabel;
    $addlprocess=$acctgaccept; 
    $addlprocesslabel=$acctgacceptlabel;
    $addlprocess2=$withbank; 
    $addlprocesslabel2=$withbanklabel;
  }
  else {
    unset($editprocess);
  }

  include('../backendphp/layout/displayastable.php');

  $subtitle='With AR or In Transit';
  $sql='SELECT *, IF(SendToBank=1,"Send","") AS Send_To_Bank FROM `branchpdc` WHERE WithBank=0 AND AtOffice<>0 AND ISNULL(AcctgAcceptedByNo) '.$orderby;

  if ($canEditPDCRemarksCSO) { 
      #$addlprocess=$sendtobank; 
      #$addlprocesslabel=$sendtobanklabel;
      unset($addlprocess2);}
  elseif ($canEditPDCRemarksGenAcctg) {}    //same options, same options with?
  else {
    unset($editprocess);
  }
  
  include('../backendphp/layout/displayastableonlynoheaders.php');

  $subtitle='<br><br>With Accounting';
  $sql=<<<SQL
    SELECT *, IF(SendToBank=1,"Send","") AS Send_To_Bank 
    FROM `branchpdc` 
    WHERE WithBank=0 AND NOT ISNULL(AcctgAcceptedByNo)
    {$orderby};
SQL;

  if ($canEditPDCRemarksCSO) { 
    #$addlprocess=$sendtobank; 
    #$addlprocesslabel=$sendtobanklabel;
    unset($addlprocess2);
  }
  elseif ($canEditPDCRemarksGenAcctg) {    
    $addlprocess=$withbank; 
    $addlprocesslabel=$withbanklabel;
    unset($addlprocess2);
  }
  else {
    unset($editprocess);
  }
  
  //Show the link to generate UBP stuff
  include('../backendphp/layout/displayastableonlynoheaders.php');
  if($canGenerateOutputforUBP){
    echo '<br><a href="/'.$url_folder.'/acctg/lookupacctgAR.php?w=UBPGenerate">Generate Output for UBP Checkhousing</a>';
  }

  $subtitle='<br><br>With Bank';
  $sql='SELECT * FROM `branchpdc` WHERE WithBank<>0 '.$orderby;

  $columnnames=array('CRNo','DateofPDC','ClientName','PDCBank', 'ClientCheckBankAccountNo','PDCNo','PDCBRSTN','Branch','Cash','PDC','ARRemarks','AcctgRemarks');
  if ($canEditPDCRemarksCSO) {
    unset($addlprocess,$addlprocess2);
  }
  elseif ($canEditPDCRemarksGenAcctg) {
    unset($addlprocess2);
    $addlprocesslabel='Retrieve_from_Bank';
  }
  else {
    unset($editprocess);
  }

  //if(allowedToOpen(5508,'1rtc')){ $addlprocess='pdcremarks.php?w=Retrieve&PDCID='; $addlprocesslabel='Retrieve from Bank';}
  
  include('../backendphp/layout/displayastableonlynoheaders.php');
  

  //End of note on page
  echo <<<HTML
  <br><br>
  <h4>Notes</h4>
  <div style="margin: 15px;" >
    <ol type="1">
      <li>Remarks - Only AR and Acctg may encode.</li>
      <li>Office Accept - To be clicked by the office personnel who picks up the checks, such as Invty Auditor, AR, Sales, or other office personnel. When accepted, it is assumed that ALL conditions of the check are correct: with complete payee name, amount in numbers, amount in words, signature, and no erasures.</li>
      <li>Send to Bank - Only AR can set this to inform everyone that these checks may be sent to the bank for PDC Warehousing.</li>
      <li>With Bank - Only Acctg can set this.  This should be set when the bank has acknowledged that the PDC is with them.  The `Send To Bank` option must be set BEFORE a check can be set as `With Bank`.</li>
      <li>AR must not keep the checks. All postdated checks that are sure to clear must be with the bank in PDC Warehousing. All other checks will be safeguarded by Branch (until pick up by office personnel) and Acctg.</li>
    </ol>
    </div> 
HTML;
  break;

case 'UBPGenerate':
  //if generateUBP is set, it will show the UBP Table.
  if(allowedToOpen(5509, '1rtc')){
    include('../backendphp/layout/displayastablewithcheckbox.php');
  if(isset($_GET['m'])){
    switch($_GET['m']){
      case 0:
        echo "<br><br><h2 style=\"color: #FF0000;\">Please mark an entry</h2><br><br>";
    }
  }

    //Echo the style that is going to be used by the table
    echo <<<HTML
      <style type="text/css">
        #send-bank-table{
          font-size: 10pt;
          font-family: 'Helvitica', 'Arial', sans-serif;
        }

        #send-bank-table tr:nth-child(odd){
          background-color: #FFFFCC;
        }
        #send-bank-table tr:nth-child(even){
          background-color: #FFFFFF;
        }
        #send-bank-table tr:hover{
          background-color: #cccccc;
        }
      </style>
HTML;

    //Same SQL as the ones from UndepositedPDCs, maybe we should change this into a function?
    $sql = generateBranchPDCTable($currentyr, $canDoShowAll, $canShowPerBranch);
SQL;
    $link->exec($sql);
    $sql = <<<SQL
      SELECT CONCAT(b.BranchNo, b.CRNo) AS ID, b.*
      FROM branchpdc as b
      WHERE SendToBank=0 
        AND b.AcctgAcceptedByNo IS NOT NULL
        AND b.ClientCheckBankAccountNo IS NOT NULL
        AND b.DateofPDC > DATE_ADD(NOW(), INTERVAL 7 DAY)
      ORDER BY b.ClientNo, b.DateofPDC;
SQL;

    $columnnames=array('ID', 'CRNo','DateofPDC','ClientName','PDCBank', 'ClientCheckBankAccountNo','PDCNo','PDCBRSTN','Branch','Cash','PDC','ARRemarks','AcctgRemarks');
    $stmt = $link->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    createTableWithCheckBoxOnlyWithMatchingHeaders($columnnames, $data, 
      "UBPUtilities.php?w=Checkhouse", "POST", 
      "Generate UBP Checkhouse", "Note: Only clients with check account number are displayed", 
      "ID", "ID[]", "send-bank-table", "display");
  }
  else {
    header('Location:/'.$url_folder.'/index.php?denied=true');
    exit;
  }
  break;

case 'PDCTurnoverList': 
    if (!allowedToOpen(547,'1rtc')) { echo 'No permission'; exit; }
$title='PDC Turnover List'; $formdesc=$_SESSION['@brn'].' as of '.date('Y-m-d').'<br><br>'; $showbranches=true; 

$sql0='CREATE TEMPORARY TABLE `list` AS SELECT `DateofPDC`,c.`ClientName`,PDCBank,PDCNo,PDCBRSTN, up.ClientCheckBankAccountNo, FORMAT(`PDC`,2) AS Amount FROM acctg_undepositedclientpdcs up
JOIN `1clients` c ON c.ClientNo=up.ClientNo JOIN `acctg_2collectmain` om ON up.PDCNo=om.`CheckNo` AND up.PDCBank=om.`CheckBank`
    WHERE up.`BranchNo`='.$_SESSION['bnum'].' AND om.AtOffice=0 AND `PDC`<>0  ORDER BY `DateofPDC`,c.`ClientName`,`PDCNo`;';
$stmt0=$link->prepare($sql0); $stmt0->execute();
// $sql='SELECT  @curRow := @curRow + 1 AS `No.`, `DateofPDC`,`ClientName`,PDCBank,PDCNo,PDCBRSTN, ClientCheckBankAccountNo, Amount, "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;" AS ReceivedBy FROM `list` JOIN (SELECT @curRow := 0) r';
$sql='SELECT  @curRow := @curRow + 1 AS `No.`, `DateofPDC`,`ClientName`,PDCBank,PDCNo,PDCBRSTN, ClientCheckBankAccountNo, Amount, " " AS ReceivedBy FROM `list` JOIN (SELECT @curRow := 0) r';
    $columnnames=array('No.','DateofPDC','ClientName','PDCBank','PDCNo','PDCBRSTN', 'ClientCheckBankAccountNo','Amount'); $hidecount=true; 
 if (!isset($_REQUEST['print'])) { 
     $formdesc=$formdesc.'&nbsp &nbsp <form method="post" action="lookupacctgAR.php?w=PDCTurnoverList"><input name="print" TYPE="submit" value="Print Receiving Copy"></form>';
     include('../backendphp/layout/displayastable.php'); } 
 else { $columnnames[]='ReceivedBy'; 
 $formdesc=$formdesc.'</i></h3><p font-size=small>Initialize each box to indicate receipt.</p><i><h3>';
 $totalstext='Printed on '.date('Y-m-d h:i:s l').' by IDNo '.$_SESSION['(ak0)']
         .'<br><br><br>Total number of checks received: _______ <p style="float:right">Received By: __________________________<font size="1"><br>'
         . str_repeat('&nbsp;',30).'Signature above printed name</font></p>'; 
 include('../backendphp/layout/printdisplayastable.php');}
break;

case 'SendTo': 
    if (!allowedToOpen(5506,'1rtc')) { echo 'No permission'; exit; }
$title='PDC Warehousing Receiving List'; $formdesc=date('Y-m-d').'<br><br>';  

$sql0='CREATE TEMPORARY TABLE `list` AS SELECT `DateofPDC`,DepositOnDate,c.`ClientName`,up.CRNo,PDCBank,PDCNo,PDCBRSTN, up.ClientCheckBankAccountNo,FORMAT(SUM(`PDC`),2) AS Amount,CompanyNo FROM acctg_undepositedclientpdcs up
JOIN `1clients` c ON c.ClientNo=up.ClientNo JOIN `acctg_2collectmain` om ON up.CRNo=om.`CollectNo` and up.PDCNo=om.`CheckNo` and up.PDCBank=IFNULL(om.`CheckBank`,"")
    JOIN `1branches` b ON b.BranchNo=up.BranchNo
    WHERE up.SendToBank=1 AND up.AtOffice=1 AND up.WithBank=0 AND `PDC`<>0 AND DATEDIFF(DateofPDC,CURDATE())>5 GROUP BY `DateofPDC`,up.`ClientNo`,`PDCNo` ORDER BY `DateofPDC`,c.`ClientName`,`PDCNo`;';
$stmt0=$link->prepare($sql0); $stmt0->execute();

$sqlco='Select c.CompanyNo, c.Company,c.CompanyName from `1companies` c join `list` r on c.CompanyNo=r.CompanyNo group by c.CompanyNo';
$stmt=$link->query($sqlco); $resultco=$stmt->fetchAll();

$columnnames=array('No.','DateofPDC','DepositOnDate','ClientName','CRNo','PDCBank','PDCNo','PDCBRSTN', 'ClientCheckBankAccountNo','Amount'); $hidecount=true; 

if (!isset($_REQUEST['print'])) { 
     $formdesc=$formdesc.'&nbsp &nbsp <form method="post" action="lookupacctgAR.php?w=SendTo" style="display:inline;"><input name="print" TYPE="submit" value="Print Receiving Copy"></form>';
     $formdesc=$formdesc.'&nbsp &nbsp <form method="post" action="exportpdc.php" style="display:inline;"><input name="print" TYPE="submit" value="Prepare PDC Data for Download"></form>';
     }
 else { $columnnames[]='ReceivedBy'; 
 $formdesc=$formdesc.'</i></h3><style>
</style> <p font-size=small>Initialize each box to indicate receipt.</p><i><h3>';
 $sql0='SELECT CONCAT(FirstName," ",Surname) AS FullName FROM `1employees` e WHERE IDNo='.$_SESSION['(ak0)'];
 $stmt=$link->query($sql0); $resultname=$stmt->fetch();
 $totalstext='Printed on '.date('Y-m-d h:i:s l').' by '.$resultname['FullName'].'.'
         .'<br><br><br>Total number of checks received: _______ <p style="float:right">Received By: __________________________<font size="1"><br>'
         . str_repeat('&nbsp;',30).'Signature above printed name</font></p><div class="break"></div>';
  
 }

foreach ($resultco as $company){
$letter='<br><br>'
        . '<center><img src="../generalinfo/logo/'.$company['Company'].'.png"></center><br>'
        . '<center>Accounting Office: (02) 808-1569  (02) 808-1574    (0917) 571-2535<br><br></center>';
$sql='SELECT  @curRow := @curRow + 1 AS `No.`, `DateofPDC`,`ClientName`, CRNo, PDCBank, `PDCNo`, PDCBRSTN, ClientCheckBankAccountNo, Amount,DepositOnDate, "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;" AS ReceivedBy FROM `list` l JOIN (SELECT @curRow := 0) r WHERE l.CompanyNo='.$company['CompanyNo'];

if (!isset($_REQUEST['print'])) { include('../backendphp/layout/displayastable.php'); unset($formdesc); $title='';} 
 else { echo $letter; include('../backendphp/layout/printdisplayastable.php'); }
 
}

break;
case 'Paid':
    if (!allowedToOpen(548,'1rtc')) { echo 'No permission'; exit; }
	$title='Paid Scanned Invoices';
	$method='GET'; 
	$showbranches=true;
	case 'PaidClients':
	$namefield='c.ClientName';
	
	$dirname = "../../acrossyrs/unpaidarinv/";
	  $images = glob($dirname."*.jpg");
	  $newimg='';
	  foreach($images as $image) {
		$img=str_replace("../../acrossyrs/unpaidarinv/","",str_replace(".jpg","",$image));
		$newimg=$newimg.'"'.$img.'"'.',';
	  }
	  $newimg1 = substr($newimg, 0, -1);
	  
      $otherfields='ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit,';
      $condition='join `1clients` c on c.ClientNo=r.ClientNo  ';
      $agencycondition=' ';
      $orderby=' WHO, Date ';
      $branchcondition=' and  r.ClientNo in (Select cb.ClientNo from `acctg_1clientsperbranch` cb where cb.BranchNo=' . $_SESSION['bnum'].')
      union all
SELECT ifnull(up.SaleDate,up.DateofPDC) as Date, up.ClientNo, c.`ClientName`,CONCAT("CRNo",CRNo,\' \',`PDCBank`,\' \',`PDCNo`,\' \',`DateofPDC`),0,0,`PDC`,DateDiff(curdate(),ifnull(up.SaleDate,up.DateofPDC)) as Age, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit , up.`BranchNo`,b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold  FROM acctg_undepositedclientpdcs up
join `1branches` b on b.BranchNo=up.BranchNo
join `1clients` c on c.ClientNo=up.ClientNo
left join `acctg_34holdstatus` ch on ch.ClientNo=up.ClientNo
where  up.`PDCNo` in (SELECT `PDCNo` FROM acctg_undepositedclientpdcs ups  where ups.BranchNo=' . $_SESSION['bnum'].') ';

$sql0='CREATE TEMPORARY TABLE paid (
Date DATE NOT NULL,
ClientNo SMALLINT NOT NULL,
ClientName VARCHAR(100)  NULL,
INVNO VARCHAR(150) NOT NULL,
SaleAmount DOUBLE NOT NULL,
RcdAmount DOUBLE NULL,
InvBalance DOUBLE NOT NULL,
Age smallint(5) NOT NULL,
Terms smallint(3) NOT NULL,
CreditLimit double NOT NULL,
BranchNo smallint(6) NOT NULL,
Branch varchar(25) NOT NULL,
Hold varchar(10) null
)

SELECT r.Date,
r.ClientNo, '.$namefield.' as WHO,
r.Particulars AS INVNO,
round(r.SaleAmount,2) as SaleAmount,
round(r.RcdAmount,2) as RcdAmount,
round(r.InvBalance,2) as InvBalance,
DateDiff(curdate(),r.Date) as Age, '.$otherfields.' r.BranchNo,
b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold FROM `acctg_33qrybalperrecpt` as r '.$condition.'
join `1branches` b on b.BranchNo=r.BranchNo
left join `acctg_34holdstatus` ch on ch.ClientNo=r.ClientNo 
WHERE (InvBalance<=0 AND (InvBalance=0.00 OR InvBalance=0.00)) '.$branchcondition.$agencycondition.' 
;';
$stmt=$link->prepare($sql0); $stmt->execute();
//}   $sql1='SELECT r.CompanyNo, CONCAT(r.CompanyNo, " - ", Company) AS Company FROM paid r JOIN `1companies` c ON c.CompanyNo=r.CompanyNo '
       $sql1='SELECT WHO,concat("Terms: ",Terms," days") as Terms,concat("Credit Limit: ",CreditLimit) as CreditLimit, Hold FROM `paid` GROUP BY WHO ORDER BY WHO ';
    $sql2='SELECT r.*,CONCAT(Branch,"_",INVNO,"_",ClientNo) AS TxnID FROM paid r ';

    $coltototal='InvBalance';
    $groupby='WHO';
    $orderby=' HAVING TxnID IN ('.$newimg1.') ORDER BY WHO, Date, INVNO';
    $columnnames1=array('WHO','Terms','CreditLimit','Hold');
    $columnnames2=array('Date','INVNO','Age','Branch');
    $showtotals=true; $runtotal=true;
    $showgrandtotal=true; 
	$txnid='TxnID';
	$addprocess='lookupacctgAR.php?w=LookupPic&TxnID='; $addprocesslabel='Lookup';
	$delprocess='lookupacctgAR.php?w=DeletePic&TxnID='; $delprocesslabel='Delete';
	
	
    include('../backendphp/layout/displayastablewithsub.php');
	
   break;

case 'Receivables':
    if (!allowedToOpen(548,'1rtc')) { echo 'No permission'; exit; }
$title='Unpaid Client Invoices';
$method='GET'; 
$showbranches=true;

if (allowedToOpen(array(5481,5482),'1rtc')) {
	
?><br><br><?php echo str_repeat('&nbsp',10); 
include_once('../backendphp/layout/linkstyle.php');
if (allowedToOpen(5481,'1rtc')){
?>
<div>
<font size=4 face='sans-serif'>
<a id="link" href='lookupacctgAR.php?w=Receivables&for=Clients'>Clients Per Branch</a><?php echo str_repeat('&nbsp',10); ?>
<a id="link" href='lookupacctgAR.php?w=Receivables&for=AllClients'>All Clients</a><?php echo str_repeat('&nbsp',10); ?>
<a id="link" href='lookupacctgAR.php?w=Receivables&for=Suppliers'>Suppliers</a><?php echo str_repeat('&nbsp',10); ?>
<a id="link" href='lookupacctgAR.php?w=Paid&for=PaidClients'>Paid Scanned Invoices</a><?php echo str_repeat('&nbsp',10); ?>
<a id="link" href='lookupacctgAR.php?w=Receivables&for=OldAccounts'>Old Accounts</a><?php echo str_repeat('&nbsp',10); ?><?php echo '<br><br>'; ?>
<a id="link" href='lookupacctgAR.php?w=Receivables&for=Term1CL1'>Dated Check Required</a><?php echo str_repeat('&nbsp',10); ?>
<a id="link" href='lookupacctgAR.php?w=Receivables&for=CreditLine'>With CL </a><?php echo str_repeat('&nbsp',10); ?>
<a id="link" href='lookupacctgAR.php?w=Receivables&for=1DAYAR'>AR1</a><?php echo str_repeat('&nbsp',10); ?>
   <?php } ?>
<a id="link" href='lookupacctgAR.php?w=Receivables&for=Employees'>Employees</a><?php echo str_repeat('&nbsp',10); ?>
<a id="link" href='lookupacctgAR.php?w=Receivables&for=Agency'>Agency Employees</a><?php echo str_repeat('&nbsp',10); ?>

</font></div><br><br>
<?php
} else {
   $for='Clients';
}
if (isset($_GET['for'])){ $for=$_GET['for'];} else {   $for='Clients';}

switch ($for){
	
	case 'CreditLine':
	if (allowedToOpen(5481,'1rtc')){
	$all = 'ShowAll';
		$perbranch= 'PerBranch';
		if (!isset($_POST['ShowAll'])){$_POST['ShowAll']='';}
		if (isset($_POST['ShowAll'])){
		if ($_POST['ShowAll']==$all){
			$_SESSION['all']=1;
		}else{unset($_SESSION['all']);} 
		}
	$formdesc='</br><form method="POST" action="lookupacctgAR.php?w=Receivables&for=CreditLine">
				<input type="submit" name="ShowAll" value='.(isset($_SESSION['all'])?$perbranch:$all).'>
	</form></br>';}
	
	$namefield='c.ClientName';
      $otherfields='ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit,';
      $condition='join `1clients` c on c.ClientNo=r.ClientNo  ';
	  
      $agencycondition=' ';
      $orderby=' WHO, Date ';
	  if($_POST['ShowAll']==$all){
		   $branchcondition=' AND CreditLimit<>0 union all
SELECT ifnull(up.SaleDate,up.DateofPDC) as Date, up.ClientNo, c.`ClientName`,concat("CRNo",CRNo,\' \',`PDCBank`,\' \',`PDCNo`,\' \',`DateofPDC`),0,0,`PDC`,DateDiff(curdate(),ifnull(up.SaleDate,up.DateofPDC)) as Age, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit , up.`BranchNo`,b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold,\'\' AS PONo FROM acctg_undepositedclientpdcs up
join `1branches` b on b.BranchNo=up.BranchNo
join `1clients` c on c.ClientNo=up.ClientNo
left join `acctg_34holdstatus` ch on ch.ClientNo=up.ClientNo WHERE CreditLimit<>0 ';
		  
	  }else{		
      $branchcondition=' AND CreditLimit<>0 and  r.ClientNo in (Select cb.ClientNo from `acctg_1clientsperbranch` cb where cb.BranchNo=' . $_SESSION['bnum'].')
      union all
	SELECT ifnull(up.SaleDate,up.DateofPDC) as Date, up.ClientNo, c.`ClientName`,CONCAT("CRNo",CRNo,\' \',`PDCBank`,\' \',`PDCNo`,\' \',`DateofPDC`),0,0,`PDC`,DateDiff(curdate(),ifnull(up.SaleDate,up.DateofPDC)) as Age, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit , up.`BranchNo`,b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold,\'\' AS PONo  FROM acctg_undepositedclientpdcs up
	join `1branches` b on b.BranchNo=up.BranchNo
	join `1clients` c on c.ClientNo=up.ClientNo
	left join `acctg_34holdstatus` ch on ch.ClientNo=up.ClientNo
	where c.CreditLimit<>0 and up.`PDCNo` in (SELECT `PDCNo` FROM acctg_undepositedclientpdcs ups  where ups.BranchNo=' . $_SESSION['bnum'].') ';
	  }
   break;
   
   case '1DAYAR':
	if (allowedToOpen(5481,'1rtc')){
		$all = 'ShowAll';
		$perbranch= 'PerBranch';
		if (!isset($_POST['ShowAll'])){$_POST['ShowAll']='';}
		if (isset($_POST['ShowAll'])){
		if ($_POST['ShowAll']==$all){
			$_SESSION['all']=1;
		}else{unset($_SESSION['all']);} 
		}
	$formdesc='</br><form method="POST" action="lookupacctgAR.php?w=Receivables&for=1DAYAR">
				<input type="submit" name="ShowAll" value='.(isset($_SESSION['all'])?$perbranch:$all).'>
	</form></br>';}
	$namefield='c.ClientName';
      $otherfields='ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit,';
      $condition='join `1clients` c on c.ClientNo=r.ClientNo  ';
	  
      $agencycondition=' ';
      $orderby=' WHO, Date ';
	  if($_POST['ShowAll']==$all){
		  $branchcondition=' AND CreditLimit=10000 and Terms=1 union all
SELECT ifnull(up.SaleDate,up.DateofPDC) as Date, up.ClientNo, c.`ClientName`,concat("CRNo",CRNo,\' \',`PDCBank`,\' \',`PDCNo`,\' \',`DateofPDC`),0,0,`PDC`,DateDiff(curdate(),ifnull(up.SaleDate,up.DateofPDC)) as Age, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit , up.`BranchNo`,b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold,\'\' AS PONo FROM acctg_undepositedclientpdcs up
join `1branches` b on b.BranchNo=up.BranchNo
join `1clients` c on c.ClientNo=up.ClientNo
left join `acctg_34holdstatus` ch on ch.ClientNo=up.ClientNo WHERE CreditLimit=10000 and Terms=1 ';
		} else{
      $branchcondition=' AND CreditLimit=10000 and Terms=1 and  r.ClientNo in (Select cb.ClientNo from `acctg_1clientsperbranch` cb where cb.BranchNo=' . $_SESSION['bnum'].')
      union all
	SELECT ifnull(up.SaleDate,up.DateofPDC) as Date, up.ClientNo, c.`ClientName`,CONCAT("CRNo",CRNo,\' \',`PDCBank`,\' \',`PDCNo`,\' \',`DateofPDC`),0,0,`PDC`,DateDiff(curdate(),ifnull(up.SaleDate,up.DateofPDC)) as Age, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit , up.`BranchNo`,b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold,\'\' AS PONo  FROM acctg_undepositedclientpdcs up
	join `1branches` b on b.BranchNo=up.BranchNo
	join `1clients` c on c.ClientNo=up.ClientNo
	left join `acctg_34holdstatus` ch on ch.ClientNo=up.ClientNo
	where  c.CreditLimit=10000 and c.Terms=1 and up.`PDCNo` in (SELECT `PDCNo` FROM acctg_undepositedclientpdcs ups  where ups.BranchNo=' . $_SESSION['bnum'].') ';
	  }
   break;
	
	case 'Term1CL1':
	if (allowedToOpen(5481,'1rtc')){
		$all = 'ShowAll';
		$perbranch= 'PerBranch';
		if (!isset($_POST['ShowAll'])){$_POST['ShowAll']='';}
		if (isset($_POST['ShowAll'])){
		if ($_POST['ShowAll']==$all){
			$_SESSION['all']=1;
		}else{unset($_SESSION['all']);} 
		}
	$formdesc='</br><form method="POST" action="lookupacctgAR.php?w=Receivables&for=Term1CL1">
				<input type="submit" name="ShowAll" value='.(isset($_SESSION['all'])?$perbranch:$all).'>
	</form></br>';}
	$namefield='c.ClientName';
      $otherfields='ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit,';
      $condition='join `1clients` c on c.ClientNo=r.ClientNo  ';
	  
      $agencycondition=' ';
      $orderby=' WHO, Date ';
	  if($_POST['ShowAll']==$all){
		  $branchcondition=' AND CreditLimit=1 and Terms=1 union all
SELECT ifnull(up.SaleDate,up.DateofPDC) as Date, up.ClientNo, c.`ClientName`,concat("CRNo",CRNo,\' \',`PDCBank`,\' \',`PDCNo`,\' \',`DateofPDC`),0,0,`PDC`,DateDiff(curdate(),ifnull(up.SaleDate,up.DateofPDC)) as Age, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit , up.`BranchNo`,b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold,\'\' AS PONo FROM acctg_undepositedclientpdcs up
join `1branches` b on b.BranchNo=up.BranchNo
join `1clients` c on c.ClientNo=up.ClientNo
left join `acctg_34holdstatus` ch on ch.ClientNo=up.ClientNo WHERE CreditLimit=1 and Terms=1 ';
		} else{
      $branchcondition=' AND CreditLimit=1 and Terms=1 and  r.ClientNo in (Select cb.ClientNo from `acctg_1clientsperbranch` cb where cb.BranchNo=' . $_SESSION['bnum'].')
      union all
	SELECT ifnull(up.SaleDate,up.DateofPDC) as Date, up.ClientNo, c.`ClientName`,CONCAT("CRNo",CRNo,\' \',`PDCBank`,\' \',`PDCNo`,\' \',`DateofPDC`),0,0,`PDC`,DateDiff(curdate(),ifnull(up.SaleDate,up.DateofPDC)) as Age, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit , up.`BranchNo`,b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold,\'\' AS PONo  FROM acctg_undepositedclientpdcs up
	join `1branches` b on b.BranchNo=up.BranchNo
	join `1clients` c on c.ClientNo=up.ClientNo
	left join `acctg_34holdstatus` ch on ch.ClientNo=up.ClientNo
	where  c.CreditLimit=1 and c.Terms=1 and up.`PDCNo` in (SELECT `PDCNo` FROM acctg_undepositedclientpdcs ups  where ups.BranchNo=' . $_SESSION['bnum'].') ';
	  }
   break;
	
	
	case 'OldAccounts':
	if (allowedToOpen(5481,'1rtc')){
	$all = 'ShowAll';
		$perbranch= 'PerBranch';
		if (!isset($_POST['ShowAll'])){$_POST['ShowAll']='';}
		if (isset($_POST['ShowAll'])){
		if ($_POST['ShowAll']==$all){
			$_SESSION['all']=1;
		}else{unset($_SESSION['all']);} 
		}
	$formdesc='</br><form method="POST" action="lookupacctgAR.php?w=Receivables&for=OldAccounts">
				<input type="submit" name="ShowAll" value='.(isset($_SESSION['all'])?$perbranch:$all).'>
	</form></br>';}
	$namefield='c.ClientName';
      $otherfields='ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit,';
      $condition='join `1clients` c on c.ClientNo=r.ClientNo  ';
	  
      $agencycondition=' ';
      $orderby=' WHO, Date ';
	  if($_POST['ShowAll']==$all){
		  $branchcondition=' AND Date<\'2018-01-01\' union all
SELECT ifnull(up.SaleDate,up.DateofPDC) as Date, up.ClientNo, c.`ClientName`,concat("CRNo",CRNo,\' \',`PDCBank`,\' \',`PDCNo`,\' \',`DateofPDC`),0,0,`PDC`,DateDiff(curdate(),ifnull(up.SaleDate,up.DateofPDC)) as Age, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit , up.`BranchNo`,b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold,\'\' AS PONo FROM acctg_undepositedclientpdcs up
join `1branches` b on b.BranchNo=up.BranchNo
join `1clients` c on c.ClientNo=up.ClientNo
left join `acctg_34holdstatus` ch on ch.ClientNo=up.ClientNo WHERE up.SaleDate<\'2018-01-01\' AND up.DateofPDC<\'2018-01-01\' ';
	  } else{
      $branchcondition=' AND Date<\'2018-01-01\' and  r.ClientNo in (Select cb.ClientNo from `acctg_1clientsperbranch` cb where cb.BranchNo=' . $_SESSION['bnum'].')
      union all
	SELECT ifnull(up.SaleDate,up.DateofPDC) as Date, up.ClientNo, c.`ClientName`,CONCAT("CRNo",CRNo,\' \',`PDCBank`,\' \',`PDCNo`,\' \',`DateofPDC`),0,0,`PDC`,DateDiff(curdate(),ifnull(up.SaleDate,up.DateofPDC)) as Age, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit , up.`BranchNo`,b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold,\'\' AS PONo  FROM acctg_undepositedclientpdcs up
	join `1branches` b on b.BranchNo=up.BranchNo
	join `1clients` c on c.ClientNo=up.ClientNo
	left join `acctg_34holdstatus` ch on ch.ClientNo=up.ClientNo
	where up.SaleDate<\'2018-01-01\' and up.`PDCNo` in (SELECT `PDCNo` FROM acctg_undepositedclientpdcs ups  where ups.BranchNo=' . $_SESSION['bnum'].') ';
	  }
   break;
	
	
	
   case 'Agency':
      $namefield='concat(IDNO, \' \',c.FirstName,\' \',c.Surname)';
      $otherfields='0 as Terms, 0 as CreditLimit,';
      $condition='join `1employees` c on c.IDNo=r.ClientNo';
      $agencycondition=' and c.DirectOrAgency<>0 ';
      $orderby=$namefield.', r.Date';
      $branchcondition=''; $showprint=true;
      break;
   case 'Employees':
       $sql0='CREATE TEMPORARY TABLE charges AS '
           . 'SELECT b.CompanyNo, r.BranchNo, b.Branch, r.ClientNo, '
           . 'CONCAT(IDNO, " ",c.FirstName," ",c.Surname) AS Name, Particulars, InvBalance FROM acctg_33qrybalperrecpt r '
           . 'JOIN `1branches` b ON b.BranchNo=r.BranchNo '
           . 'JOIN `1employees` c on c.IDNo=r.ClientNo '
           . 'WHERE ClientNo<9000 AND InvBalance<>0 GROUP BY r.ClientNo, Particulars '
           . ' UNION ALL
SELECT b.CompanyNo, r.BranchNo, b.Branch, c.IDNo, CONCAT(IDNO, " ",c.FirstName," ",c.Surname," RESIGNED"),Particulars,InvBalance FROM acctg_33qrybalperrecpt r
join `1branches` b on b.BranchNo=r.BranchNo
join `1_gamit`.`0idinfo` c on c.IDNo=r.ClientNo AND `Resigned?`=1 AND c.IDNo NOT IN (SELECT IDNo FROM `1employees`) AND InvBalance<>0';
       $stmt0=$link->prepare($sql0); $stmt0->execute();
       $sql1='SELECT r.CompanyNo, CONCAT(r.CompanyNo, " - ", Company) AS Company FROM charges r JOIN `1companies` c ON c.CompanyNo=r.CompanyNo '
               . 'GROUP BY r.CompanyNo';  
       $sql2='SELECT * FROM charges ';
      $groupby='CompanyNo'; $orderby='ORDER BY CompanyNo';
      $columnnames1=array('Company');
      $columnnames2=array('Name','Branch','Particulars', 'InvBalance');
      $sqlsubtotal='SELECT FORMAT(SUM(InvBalance),2) AS InvBalance FROM charges ';
            $colsubtotals=array('InvBalance');
      include('../backendphp/layout/displayastablewithsub.php'); goto noform;
      break;
   case 'Suppliers':
      $namefield='c.SupplierName';
      $otherfields='0 as Terms, 0 as CreditLimit,';
      $condition='join `1suppliers` c on c.SupplierNo=r.ClientNo';
      $agencycondition=' ';
      $orderby=$namefield.', r.Date';
      $branchcondition=' ';
      break;
   case 'AllClients':
      $namefield='c.ClientName';
      $otherfields='ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit,';
      $condition='join `1clients` c on c.ClientNo=r.ClientNo';
      $agencycondition=' ';
      $orderby=' WHO, Date ';
      $branchcondition=' union all
SELECT ifnull(up.SaleDate,up.DateofPDC) as Date, up.ClientNo, c.`ClientName`,concat("CRNo",CRNo,\' \',`PDCBank`,\' \',`PDCNo`,\' \',`DateofPDC`),0,0,`PDC`,DateDiff(curdate(),ifnull(up.SaleDate,up.DateofPDC)) as Age, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit , up.`BranchNo`,b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold,\'\' AS PONo FROM acctg_undepositedclientpdcs up
join `1branches` b on b.BranchNo=up.BranchNo
join `1clients` c on c.ClientNo=up.ClientNo
left join `acctg_34holdstatus` ch on ch.ClientNo=up.ClientNo';
      break;
   default: //Clients
      $namefield='c.ClientName';
      $otherfields='ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit,';
      $condition='join `1clients` c on c.ClientNo=r.ClientNo  ';
	  
      $agencycondition=' ';
      $orderby=' WHO, Date ';
      $branchcondition=' and  r.ClientNo in (Select cb.ClientNo from `acctg_1clientsperbranch` cb where cb.BranchNo=' . $_SESSION['bnum'].')
      union all
SELECT ifnull(up.SaleDate,up.DateofPDC) as Date, up.ClientNo, c.`ClientName`,CONCAT("CRNo",CRNo,\' \',`PDCBank`,\' \',`PDCNo`,\' \',`DateofPDC`),0,0,`PDC`,DateDiff(curdate(),ifnull(up.SaleDate,up.DateofPDC)) as Age, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit , up.`BranchNo`,b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold,\'\' AS PONo  FROM acctg_undepositedclientpdcs up
join `1branches` b on b.BranchNo=up.BranchNo
join `1clients` c on c.ClientNo=up.ClientNo
left join `acctg_34holdstatus` ch on ch.ClientNo=up.ClientNo
where  up.`PDCNo` in (SELECT `PDCNo` FROM acctg_undepositedclientpdcs ups  where ups.BranchNo=' . $_SESSION['bnum'].') ';
      break;
}


    $sql0='CREATE TEMPORARY TABLE Receivables (
Date DATE NOT NULL,
ClientNo SMALLINT NOT NULL,
ClientName VARCHAR(100)  NULL,
INVNO VARCHAR(150) NOT NULL,
SaleAmount DOUBLE NOT NULL,
RcdAmount DOUBLE NULL,
InvBalance DOUBLE NOT NULL,
Age smallint(5) NOT NULL,
Terms smallint(3) NOT NULL,
CreditLimit double NOT NULL,
BranchNo smallint(6) NOT NULL,
Branch varchar(25) NOT NULL,
Hold varchar(10) null,
PONo varchar(50) NOT NULL
)

SELECT r.Date,
r.ClientNo, '.$namefield.' as WHO,
r.Particulars AS INVNO,
round(r.SaleAmount,2) as SaleAmount,
round(r.RcdAmount,2) as RcdAmount,
round(r.InvBalance,2) as InvBalance,
DateDiff(curdate(),r.Date) as Age, '.$otherfields.' r.BranchNo,
b.Branch, if((HoldonRecord+HoldfromTerms+HoldfromLimit)<>0,"HOLD","OK for AR") as Hold,r.PONo FROM `acctg_33qrybalperrecpt` as r '.$condition.'
join `1branches` b on b.BranchNo=r.BranchNo
left join `acctg_34holdstatus` ch on ch.ClientNo=r.ClientNo 
WHERE (InvBalance<>0 AND (InvBalance>0.05 OR InvBalance<-0.05)) '.$branchcondition.$agencycondition.' 
;';
// if($_SESSION['(ak0)']==1002){ echo $sql0."<br>";exit();}
// echo $sql0; 
$stmt=$link->prepare($sql0); $stmt->execute();
//}        
    $sql1='SELECT concat("PORequired: ",if(PORequired=1,"Yes","No")) as PORequired,WHO,CASE WHEN ARClientType=0 then "ARClientType: NotARClient" WHEN ARClientType=1 then "ARClientType: ARClient" WHEN ARClientType=2 then "ARClientType: PDCRequired" WHEN ARClientType=3 then "ARClientType: DCRequired" WHEN ARClientType=4 then "ARClientType: AR1" end as ARClientType,if(r.Terms=1,concat("Terms: ",r.Terms," day"),concat("Terms: ",r.Terms," days")) as Terms,concat("Credit Limit: ",r.CreditLimit) as CreditLimit,r.CreditLimit as CreditLimitValue, Hold FROM `Receivables` r left join 1clients c on c.ClientNo=r.ClientNo GROUP BY WHO ORDER BY WHO ';
    $sql2='SELECT r.*,CONCAT(Branch,"_",INVNO,"_",ClientNo) AS TxnID FROM Receivables r ';
	$stmtp=$link->query($sql1); $resultp=$stmtp->fetch();
    $coltototal='InvBalance';
	$coltototalsubtractedfrom='CreditLimitValue';
	$coltototalsubtractedfromlabel='Available Credit: ';
    $groupby='WHO';
    $orderby=' ORDER BY WHO, Date, INVNO';
    $columnnames1=array('WHO','Terms','CreditLimit','ARClientType','PORequired','Hold');
    $columnnames2=array('Date','INVNO','PONo','SaleAmount','RcdAmount','InvBalance','Age','Branch');
    $showtotals=true; $runtotal=true;
    $showgrandtotal=true; 
	$txnid='TxnID';
	$editprocess='lookupacctgAR.php?w=UploadPic&TxnID='; $editprocesslabel='Upload';
	$addprocess='lookupacctgAR.php?w=LookupPic&TxnID='; $addprocesslabel='Lookup';
	
    include('../backendphp/layout/displayastablewithsub.php');
	
   break;

case 'ARClientStatus': 
    if (!allowedToOpen(546,'1rtc')) { echo 'No permission'; exit; }

$title='Status of AR Clients';
	if(!isset($_POST['filter'])){
		$_POST['filter']=0;	
	}
	$formdesc='</br></i><b><form method="post" action="lookupacctgAR.php?w=ARClientStatus">
				    <input type="hidden" name="filter" '.(($_POST['filter']==0)?'value="1"':'value="0"').'>
		 Filtering: <input type="submit" name="submit" '.(($_POST['filter']==0)?'value="AR1"':'value="AR Clients"').'>
			   </form>';
$showbranches=true;
$lefttabletitle='<h3>AR Clients Allowed</h3>';
$sql0='CREATE TEMPORARY TABLE allowed AS Select cb.ClientNo, cb.ClientName, cb.Terms, cb.CreditLimit, CurrentBalance, UndepPDC, DaysOverdue, (ch.HoldonRecord+HoldfromTerms+HoldfromLimit) AS Hold, ch.HoldonRecord,HoldfromTerms,HoldfromLimit  FROM `acctg_1clientsperbranch` cb left join `acctg_34holdstatusfigures` bal on cb.ClientNo=bal.ClientNo
LEFT JOIN acctg_34holdstatus ch on cb.ClientNo=ch.ClientNo 
where cb.ARClientType<>0 and cb.BranchNo='.$_SESSION['bnum'].' '.(($_POST['filter'])==1?'AND cb.ARClientType=4':'').' group by cb.ClientNo, cb.BranchNo  order by cb.ClientName;
';
// echo $sql0; exit();
$stmt=$link->prepare($sql0); $stmt->execute();
$sql='Select ClientName, Terms, FORMAT(CreditLimit,0) AS CreditLimit, FORMAT(CurrentBalance,0) AS UnpaidBalance, 
    FORMAT(UndepPDC,0) AS UndepositedPDC, FORMAT((CurrentBalance+UndepPDC),0) AS TotalUnpaid, FORMAT((CreditLimit-CurrentBalance-UndepPDC),0) as Allowable, IF(DaysOverdue<=0,"",DaysOverdue) AS DaysOverdue, IF(HoldonRecord=0,"",IF(HoldonRecord=1,"HOLD","TEMP ALLOWED")) AS HoldonRecord, IF(HoldfromTerms=0,"","HOLD") AS HoldfromTerms, IF(HoldfromLimit=0,"","HOLD") AS HoldfromLimit FROM allowed ';
$sqlleft=$sql.' WHERE (Hold=0 OR Hold=2 or Hold is null) ORDER BY ClientName; ';

//echo $sqlleft; break;
$columnnamesleft=array('ClientName','Terms','DaysOverdue','CreditLimit','UnpaidBalance','UndepositedPDC','TotalUnpaid','Allowable');

$righttabletitle='<h3>Clients on Hold</h3>';
$sqlright=$sql.' WHERE (Hold<>0) ORDER BY ClientName; ';
//$sqlright='Select ClientName AS OnHoldClient, Terms, FORMAT(CreditLimit,0) AS CreditLimit, FORMAT(CurrentBalance,0) AS UnpaidBalance, 
//    FORMAT(UndepPDC,0) AS UndepositedPDC, (CurrentBalance+UndepPDC) AS TotalUnpaid, FORMAT((CreditLimit-CurrentBalance-UndepPDC),0) as Allowable, IF(DaysOverdue<=0,"",DaysOverdue) AS DaysOverdue FROM allowed WHERE (Hold<>0) ORDER BY ClientName;
//';
//$sqlright='Select ch.*, c.ClientName as OnHoldClient, c.Terms, c.CreditLimit, format(Sum(bal.InvBalance),2) as CurrentBalance, datediff(date_format(Now(),\'%Y-%m-%d\'), (Select bal.Date from `acctg_33qrybalperrecpt` bal where bal.ClientNo=ch.ClientNo and bal.InvBalance<>0 order by bal.Date desc limit 1))-ifnull(c.Terms,0) as DaysOverdue
//from `comments_clientsonhold` ch 
//join acctg_1clientsperbranch c on c.ClientNo=ch.ClientNo
//left join `acctg_33qrybalperrecpt` bal on ch.ClientNo=bal.ClientNo
//where c.BranchNo='.$_SESSION['bnum'].' and ch.Hold<>0 group by ch.ClientNo order by c.ClientName;'; 
$columnnamesright=array('ClientName','CreditLimit','UnpaidBalance','UndepositedPDC','TotalUnpaid','Terms','DaysOverdue','HoldonRecord','HoldfromTerms','HoldfromLimit');
//if(allowedToOpen(5511,'1rtc')){ array_push($columnnamesright,'HoldonRecord','HoldfromTerms','HoldfromLimit');}

include('../backendphp/layout/twotablessidebyside.php');

break;   

case 'SLPerClient':
    if (!allowedToOpen(549,'1rtc')) { echo 'No permission'; exit; } 

$title='AR-SL Per Client'; echo '<h3>'.$title.'</h3>';
$fieldname='Client'; 

$list=allowedToOpen(5502,'1rtc')?'employees':'clientsemployees';
//echo $list;
include_once('../generalinfo/lists.inc'); 
renderlist($list);

$monthfrom=(isset($_REQUEST['Month1'])?$_REQUEST['Month1']:date('m'));
$monthto=(isset($_REQUEST['Month2'])?$_REQUEST['Month2']:date('m'));
   ?>
<form method="post" action="lookupacctgAR.php?w=SLPerClient" enctype="multipart/form-data">
For Client:  <input type="text" name="<?php echo $fieldname; ?>" list="<?php echo $list; ?>" value="<?php echo (!isset($_REQUEST[$fieldname])?'':$_REQUEST[$fieldname]); ?>"></input>&nbsp &nbsp &nbsp
From Month (1 - 12):  <input type="text" size=5 name="Month1" value="<?php echo $monthfrom; ?>"></input>&nbsp
To Month (1 - 12):  <input type="text" size=5 name="Month2" value="<?php echo $monthto; ?>"></input>&nbsp &nbsp &nbsp 
<input type="submit" name="lookup" value="Lookup"> </form>
<?php
if (!isset($_REQUEST[$fieldname])){
goto noform;
} else {

$showprint=true;

include('../backendphp/functions/getnumber.php');
$clientno=getNumber('ClientEmployee',addslashes($_REQUEST[$fieldname]));

include 'clientaraddlformdesc.php';

$formdesc='<h3 style="color:blue;">For COLLECTION PURPOSES ONLY</h3> <br></i><b>'.$clientno.' - '.$_REQUEST[$fieldname].'</b>'.$addlformdesc.'<br><br>  <i>for the months '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$monthfrom.'-1'))).'&nbsp to '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$monthto.'-1'))).str_repeat('&nbsp',3).'';

$acctid='(200,202)';
$acctidarray=array(200,202);
//include('../acctg/sqlphp/sqlalltxnsperaccountpermonth.php');
include('../acctg/sqlphp/createacctsched.php');
include('../acctg/sqlphp/createacctbegbal.php');


$sql0='Create temporary table slper (
Date date  null,
ControlNo varchar(150) null,
`SuppNo/ClientNo` smallint(6) null,
`Supplier/Customer/Branch` varchar(100) null,
Particulars varchar(100) null,
AccountID smallint(6) not null,
BranchNo smallint(6) not null,
Amount double null,
Entry varchar(2) not null,
w varchar(20) not null,
TxnID int(11) not null
)'.$sqlalltxns;
// echo $sql0; break;
$stmt=$link->prepare($sql0);
$stmt->execute();

$sqllastyr='SELECT "Beginning" AS ControlNo, clp.`ClientNo`  as `SuppNo/ClientNo`, clp.`ARAccount`, BranchNo, Balance as SumofAmount, "DR" as Entry FROM `acctg_3unpdclientinvlastperiod` clp WHERE clp.`ARAccount` in (200,202)';
$sql1='Create temporary table slperbegbal (
ControlNo varchar(150) null,
`SuppNo/ClientNo` smallint(6) null,
AccountID smallint(6) not null,
BranchNo smallint(6) not null,
SumofAmount double null,
Entry varchar(2) not null
)'.($sqllastmonth==''?'':($sqllastmonth.', `SuppNo/ClientNo` UNION ALL ')).$sqllastyr;
// if($_SESSION['(ak0)']==1002){ echo $sql0.'<br><br>'.$sql1; break;}
$stmt=$link->prepare($sql1);
$stmt->execute();

}
//echo $monthfrom; break;
$lastmonth=$monthfrom==1?'\''.(((substr(($currentyr),0,4))-1).'-12-31\''):'Last_Day(\''.$currentyr.'-'.($monthfrom-1).'-1\')';
$sql='SELECT '.$lastmonth.' as Date, "BegBal" as ControlNo, "Beginning Balance" as `Supplier/Customer/Branch`, "" as Particulars, b.Branch, Sum(SumofAmount) as Debit, 0 as Credit, "SLPerClient" as w, 0 as TxnID
from `1branches` b join slperbegbal beg on b.BranchNo=beg.BranchNo where beg.`SuppNo/ClientNo`='.$clientno.' 
UNION ALL
SELECT Date, ControlNo, `Supplier/Customer/Branch`, Particulars, Branch, SUM(Case when Entry="DR" then Amount end) as Debit,SUM(Case when Entry="CR" then Amount*-1 end) as Credit, w, TxnID from `1branches` b join slper sp on sp.BranchNo=b.BranchNo where sp.`SuppNo/ClientNo`='.$clientno.' group by Date, ControlNo, `SuppNo/ClientNo`, Branch, Particulars order by Date,ControlNo';
//echo $sql; break;    
$main='';
$columnnames=array();

$columnsub=array('Date', 'ControlNo', 'Branch','Particulars','Debit','Credit'); 
$sub='';


$stmt=$link->query($sql);   $result=$stmt->fetchAll();
 
   $subcol='';$runtotal=0;
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        $runtotal=$runtotal+((is_null($row['Debit']) or empty($row['Debit']))?0:($row['Debit']))-((is_null($row['Credit']) or empty($row['Credit']))?0:($row['Credit']));
        
        switch ($row['w']){
         case 'Sale':
         case 'Collect':
         case 'Prov':
         case 'Bounced':
         case 'Interbranch':
            $filetoopen='addeditclientside';
            break;
         Case 'Deposit':
            $filetoopen='addeditdep';
            break;
         Case 'Purchase':
         CASE 'CV':
         case 'JV':
         case 'Forex':
            $filetoopen='addeditsupplyside';
            break;
         default:
            $filetoopen='lookupgenacctg';
        }
        
        $sub=$sub.'<td>'.number_format($runtotal,2).'</td><td><a href="'.$filetoopen.'.php?w='.$row['w'].'&TxnID='.$row['TxnID'].'"  target=_blank>Lookup</a></tr>';
        $colorcount++;
    }
    $sub='<table><tr>'.$subcol.'<td>Running Sum</td><td>Lookup?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select ifnull(Sum(Case when s.Entry="DR" then s.Amount end),0)+(Select IFNULL(Sum(SumofAmount),0) from slperbegbal a where a.`SuppNo/ClientNo`='.$clientno.') as TotalDebit, ifnull(Sum(Case when s.Entry="CR" then s.Amount end),0) as TotalCredit from  `slper` s where s.`SuppNo/ClientNo`='.$clientno;
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='<b>Totals:'.str_repeat('&nbsp',4).'<font color="maroon">Debit:  '.number_format($result['TotalDebit'],2).str_repeat('&nbsp',7).'Credit:  '.number_format($result['TotalCredit'],2).str_repeat('&nbsp',7).'Net:  '.number_format($result['TotalDebit']+$result['TotalCredit'],2).'</font></b><br><br>';
// echo $sql; break;
    
?>
<style type="text/css">
#wrap {
   width:100%;
   margin:0 auto;
}
#left {
   float:left;
   width:50%; overflow: auto;
}
#right {
   float:right;
   width:50%;overflow: auto;
}
thead {color:darkblue;font-family:sans-serif;; font-size: small;}
tbody {color:black; font-family:sans-serif;; font-size: small;}
tfoot {color:darkblue;}
table,th,td
{
border:1px solid black;
border-collapse:collapse;
padding: 3px;
}
</style><div id="wrap">
	<div id="left"><?php include('../backendphp/layout/lookupreport.php'); ?></div>
<div id="right"><?php
$title='';
$formdesc='<h3 style="color:blue;">ACTUAL CLEARED COLLECTIONS</h3> <br></i><b>'.$clientno.' - '.$_REQUEST[$fieldname].'</b>'.$addlformdesc.'<br><br>  <i>for the months '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$monthfrom.'-1'))).'&nbsp to '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$monthto.'-1'))).str_repeat('&nbsp',3).'</i><br><br>';
$showprint=FALSE;
//$stmt1=$link->prepare("DROP TABLE IF EXISTS `ClearedPayments`;"); $stmt1->execute();
$sql1='CREATE TEMPORARY TABLE `ClearedPayments` AS SELECT CONCAT(`om`.`CollectNo`, " Inv", `os`.`ForChargeInvNo`, "/", `om`.`Type`, "/", IFNULL(`om`.`CheckNo`,"")) AS `ClearedPaymt`, `m`.`Cleared`, `om`.`CollectNo` AS CollectNo FROM `acctg_2collectmain` `om`
        JOIN `acctg_2collectsub` `os` ON `om`.`TxnID` = `os`.`TxnID`
        JOIN `acctg_2depositsub` `s` ON `s`.`CRNo` = CONCAT("C-",om.BranchSeriesNo,"-",`om`.`CollectNo`)
		AND IF((ISNULL(`om`.`CheckNo`) OR (`om`.`CheckNo` LIKE "") OR (om.Type<>2)),"1=1",((`s`.`CheckNo` = `om`.`CheckNo`) AND (`om`.`CheckBank` = `s`.`CheckDraweeBank`)))
        AND (`s`.`BranchNo` = `os`.`BranchNo`) AND`om`.`ClientNo` = `s`.`ClientNo`
        JOIN `acctg_2depositmain` `m` ON `m`.`TxnID` = `s`.`TxnID`
    WHERE
        (`os`.`ForChargeInvNo` IS NOT NULL)
            AND (`os`.`CreditAccountID` = 200)
            AND (`m`.`Cleared` IS NOT NULL)
            AND (`om`.`ClientNo`='.$clientno.')'
        ;
$stmt1=$link->prepare($sql1); $stmt1->execute();
$sql1='CREATE TEMPORARY TABLE `ClearedPaymt` AS SELECT `ClearedPaymt` FROM `ClearedPayments` GROUP BY `ClearedPaymt`;'; 
$stmt1=$link->prepare($sql1); $stmt1->execute();
$sql1='CREATE TEMPORARY TABLE `Cleared` AS SELECT `ClearedPaymt`,`Cleared` FROM `ClearedPayments` GROUP BY `ClearedPaymt`,`Cleared`;'; 
$stmt1=$link->prepare($sql1); $stmt1->execute();
$sql1='CREATE TEMPORARY TABLE `CollectNo` AS SELECT  `ClearedPaymt`, `CollectNo` FROM `ClearedPayments` GROUP BY `ClearedPaymt`,`CollectNo`;'; 
$stmt1=$link->prepare($sql1); $stmt1->execute();

$sql='SELECT '.$lastmonth.' as Date, "BegBal" as ControlNo, "Beginning Balance" as `Supplier/Customer/Branch`, "" as Particulars, b.Branch, Sum(SumofAmount) as Amount,Sum(SumofAmount) as Debit, 0 as Credit, "SLPerClient" as w, 0 as TxnID
FROM `1branches` b join slperbegbal beg on b.BranchNo=beg.BranchNo where beg.`SuppNo/ClientNo`='.$clientno.' 
UNION ALL
SELECT (IF((ControlNo LIKE "Collect%") OR (ControlNo LIKE "Prov%") , (SELECT `Cleared` FROM `Cleared` WHERE Particulars like ClearedPaymt ),`Date`)) AS `Date`, `ControlNo`, `Supplier/Customer/Branch`, IF((ControlNo LIKE "Prov%"), (SELECT CONCAT(Particulars," CollectNo.",CollectNo) FROM `CollectNo` WHERE Particulars like ClearedPaymt), Particulars) AS Particulars, Branch, Amount, SUM(Case when Entry="DR" then Amount end) as Debit,SUM(Case when Entry="CR" then Amount*-1 end) as Credit, w, TxnID from `1branches` b join slper sp on sp.BranchNo=b.BranchNo where sp.`SuppNo/ClientNo`='.$clientno.' AND (IF((ControlNo LIKE "Collect%") OR (ControlNo LIKE "Prov%"), Particulars IN (SELECT `ClearedPaymt` FROM `ClearedPaymt`),TRUE)) GROUP BY Date, ControlNo, `SuppNo/ClientNo`, Branch, Particulars
 ORDER BY Date,ControlNo';
$columnnames=$columnsub; $runsum=true; $coltototal='Amount';
$sqlsum='Select ifnull(Sum(Case when s.Entry="DR" then s.Amount end),0)+(Select IFNULL(Sum(SumofAmount),0) from slperbegbal a where a.`SuppNo/ClientNo`='.$clientno.') as TotalDebit, ifnull(Sum(Case when s.Entry="CR" then s.Amount end),0) as TotalCredit from  `slper` s where s.`SuppNo/ClientNo`='.$clientno.'  AND (IF((ControlNo LIKE "Collect%") OR (ControlNo LIKE "Prov%"), Particulars IN (SELECT `ClearedPaymt` FROM `ClearedPayments`),TRUE))';
//if($_SESSION['(ak0)']==1002){ echo $sql0.'<br><br>'.$sql1.'<br><br>'.$sql.'<br><br>'.$sqlsum; }

	
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $formdesc.='<b>Totals:'.str_repeat('&nbsp',4).'<font color="maroon">Debit:  '.number_format($result['TotalDebit'],2).str_repeat('&nbsp',7).'Credit:  '.number_format($result['TotalCredit'],2).str_repeat('&nbsp',7).'Net:  '.number_format($result['TotalDebit']+$result['TotalCredit'],2).'</font></b><br><br>';
include('../backendphp/layout/displayastablenosort.php'); ?></div></div>
<?php
    $stmt1=$link->prepare("DROP TABLE IF EXISTS `ClearedPayments`;"); $stmt1->execute();
    
break;


case 'UploadPic':
$title='Upload Receipt'; 
$directory='lookupacctgAR.php?w=UploadPic&TxnID='.$_GET['TxnID'].'';
	echo '<title>'.$title.'</title>';
	if($_GET['w']){
		$txnval=$_GET['TxnID'];
		$show='hidden';
	} 
  
  echo '<br/><br/><form action="uploadreceipt.php" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="directory" value='.$directory.' size=4"> 
				<input type="'.$show.'" name="UploadID" size=4 autocomplete="off" value='.$txnval.'> <input type="hidden" name="UploadID" value="'.$_GET['TxnID'].'">'.str_repeat('&nbsp;',10).' <input type="file" name="userfile" accept="image/jpg"><input type="submit" name="submit" value="Submit"> 
                </font> </form><br/><br/>';
	
  
    break;
	
case 'LookupPic':
	
	$title='Lookup'; 
	
	echo '<title>'.$title.'</title>';

	echo '<img src="../../acrossyrs/unpaidarinv/'.$_GET['TxnID'].'.jpg"/>';
  
    break;
	
case 'DeletePic':
	
	$title='Delete'; 
	
	echo '<title>'.$title.'</title>';
	
	$path='../../acrossyrs/unpaidarinv/'.$_GET['TxnID'].'.jpg';

	if(unlink($path)) echo "Picture Deleted ";
  
    break;
	
}

	

noform:
      $link=null; $stmt=null;
?>
