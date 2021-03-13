<?php
include '../../fpdf/fpdf.php';
$pdffile= new FPDF();
$newquote=$pdffile->Text($letter);
$pdffile->Output($newquote,'D');
?> 