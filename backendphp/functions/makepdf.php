<?php
//PDF USING MULTIPLE PAGES
//FILE CREATED BY: Carlos Jos� V�squez S�ez
//YOU CAN CONTACT ME: carlos@magallaneslibre.com
//FROM PUNTA ARENAS, MAGALLANES
//INOVO GROUP - http://www.inovo.cl

define('FPDF_FONTPATH', '../../fpdf/font/');
require('../../fpdf/fpdf.php');

//Connect to your database

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
echo 'PLEASE INFORM JYE WHAT IS BEING OPENED.' ;

//Create new pdf file
$pdf=new FPDF();

//Open file
$pdf->Open();

//Disable automatic page break
$pdf->SetAutoPageBreak(false);

//Add first page
$pdf->AddPage();

//set initial y axis position per page
$y_axis_initial = 80;

//print column titles for the actual page
$pdf->SetFillColor(255 , 255, 255);
$pdf->SetFont('Arial', '', 9);
$pdf->SetY($y_axis_initial);
$pdf->SetX(25);
$pdf->Cell(20, 6, 'QtySent', 1, 0, 'L', 1);
$pdf->Cell(20, 6, 'Unit', 1, 0, 'L', 1);
$pdf->Cell(100, 6, 'Description', 1, 0, 'L', 1);
$pdf->Cell(20, 6, 'UnitPrice', 1, 0, 'R', 1);

$row_height=10;
$y_axis=0;
$y_axis = $y_axis + $row_height;

//Select the Products you want to show in your PDF file
$sqlsub='Select  s.UnitPrice,s.QtySent,concat(s.ItemCode,\' \',c.Category,\' \', i.ItemDesc,\' \',s.SerialNo) as Description, i.Unit, s.UnitPrice*s.QtySent as AmountSent from invty_2transfersub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo where TxnID='.$txnid;
    $stmt=$link->query($sqlsub);
    $resultsub=$stmt->fetchAll();
    
//initialize counter
$i = 0;

//Set maximum rows per page
$max = 20;

//Set Row Height
$row_height = 6;

foreach($resultsub as $row)
{
    //If the current row is the last one, create new page and print column title
    if ($i == $max)
    {
        $pdf->AddPage();

        //print column titles for the current page
        $pdf->SetY($y_axis_initial);
        $pdf->SetX(25);
        $pdf->Cell(20, 6, 'QtySent', 1, 0, 'L', 1);
        $pdf->Cell(20, 6, 'Unit', 1, 0, 'L', 1);
        $pdf->Cell(100, 6, 'Description', 1, 0, 'L', 1);
        $pdf->Cell(20, 6, 'UnitPrice', 1, 0, 'R', 1);
        
        //Go to next row
        $y_axis = $y_axis + $row_height;
        
        //Set $i variable to 0 (first row)
        $i = 0;
    }

    $code = $row['QtySent'];
    $price = $row['UnitPrice'];
    $name = $row['Description'];

    $pdf->SetY($y_axis);
    $pdf->SetX(25);
    $pdf->Cell(20, 6, $code, 1, 0, 'L', 1);
    $pdf->Cell(20, 6, $code, 1, 0, 'L', 1);
    $pdf->Cell(100, 6, $name, 1, 0, 'L', 1);
    $pdf->Cell(20, 6, $price, 1, 0, 'R', 1);

    //Go to next row
    $y_axis = $y_axis + $row_height;
    $i = $i + 1;
}


//Create file
$pdf->Output();
?>