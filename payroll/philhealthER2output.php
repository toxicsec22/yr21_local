<?php
$path=$_SERVER['DOCUMENT_ROOT']; 
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(9014,'1rtc')) { echo 'No permission'; exit; }


use setasign\Fpdi\Fpdi;

require_once($path.'/acrossyrs/commonfunctions/pdfeditphp/fpdf183/fpdf.php');
require_once($path.'/acrossyrs/commonfunctions/pdfeditphp/fpdi/src/autoload.php');


// initiate FPDI
$pdf = new FPDI('L','mm', array(279,216));
// 1

if (isset($_REQUEST['IDNo'])){
    $idnos='0';
    foreach ($_REQUEST['IDNo'] AS $IDNo){
        $idnos.=','.$IDNo;
    }

}

// echo $idnos; exit();

$conditionidno=' WHERE id.IDNo IN ('.$idnos.')';

$sqlmain='select PHICNo, CONCAT(id.SurName,", ",id.FirstName," ",id.MiddleName) AS FullName,IF (Position LIKE "% -%",LEFT(Position,LOCATE(" -",Position)-1),Position) AS Position,FORMAT(BasicMonthly,2) AS Salary,DATE_FORMAT(id.DateHired,"%m/%d/%Y") AS DateHired FROM 1_gamit.0idinfo id JOIN attend_30currentpositions cp ON id.IDNo=cp.IDNo JOIN payroll_21dailyandmonthly dm ON id.IDNo=dm.IDNo '.$conditionidno.' ORDER BY id.SurName,id.FirstName,id.MiddleName';
// echo $sqlmain;

$sqlcnt='select CEILING(COUNT(id.IDNo)/10) AS trcount,COUNT(id.IDNo) as totalidno from 1_gamit.0idinfo id JOIN attend_30currentpositions cp ON id.IDNo=cp.IDNo '.$conditionidno.' ORDER BY id.SurName,id.FirstName,id.MiddleName;';
$stmtcnt=$link->query($sqlcnt); $rescnt=$stmtcnt->fetch();

//header details
$sqlheader='select GovtName,PHICNo,RegisteredAddress FROM 1companies WHERE CompanyNo='.$_GET['CompanyNo'];
$stmtheader=$link->query($sqlheader); $resheader=$stmtheader->fetch();


$starttr=1;
$startoffset=0; $yval=50;

$pdf->SetAutoPageBreak(true, 1);


while($starttr<=$rescnt['trcount']){  
    $pdf->AddPage();
    //header

    $pdf->SetFont('Arial','', 11);
    $pdf->SetXY(64, 36.5);
    $pdf->Write(0, $resheader['GovtName']);

    $pdf->SetFont('Arial','', 11);
    $pdf->SetXY(230, 37.5);
    $pdf->Write(0, $resheader['PHICNo']);

    $pdf->SetFont('Arial','', 11);
    $pdf->SetXY(28, 45);
    $pdf->Write(0, $resheader['RegisteredAddress']);

    $pdf->SetFont('Arial','', 11);
    $pdf->SetXY(190, 45);
    $pdf->Write(0, 'hrd@1rotary.com.ph');
    
    // set the source file
    $pdf->setSourceFile('PHILHEALTHER2.pdf');
    // import page 1
    $tplIdx = $pdf->importPage(1);
    // use the imported page and place it at position 10,10 with a width of 100 mm
    $pdf->useTemplate($tplIdx, 0, 0, 279);

    $sqlm=$sqlmain.' LIMIT 10 OFFSET '.$startoffset;
    
    $sql=$sqlm;
    
    $stmtm=$link->query($sql); $resm=$stmtm->fetchAll();

    $pdf->SetTextColor(0, 0, 0);

    $totperpage=0;
    $pdf->SetFont('Arial','', 9);
    foreach($resm AS $res){
        
        $pdf->SetXY(5, ($yval+20));
        $pdf->Write(0, $res['PHICNo']);

        $pdf->SetXY(42, ($yval+20));
        $pdf->Write(0, $res['FullName']);

        $pdf->SetXY(106, ($yval+20));
        $pdf->Write(0, $res['Position']);

        $pdf->SetXY(148, ($yval+20));
        $pdf->Write(0, $res['Salary']);

        $pdf->SetXY(173, ($yval+20));
        $pdf->Write(0, $res['DateHired']);

        $yval=$yval+12;
        $totperpage++;
    }
    
    //other
    $pdf->SetFont('Arial','B', 16);
    $pdf->SetXY(68, 196);
    $pdf->Write(0, $totperpage);

    $pdf->SetFont('Arial','B', 10);
    $pdf->SetXY(147, 202);
    $pdf->Write(0, $starttr);

    $pdf->SetFont('Arial','B', 10);
    $pdf->SetXY(158, 202);
    $pdf->Write(0, $rescnt['trcount']);

    $yval=50;
    $starttr++;
    $startoffset=$startoffset+10;
    $totperpage=0;
}


$pdf->Output('I', 'generated.pdf');

