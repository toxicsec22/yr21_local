<style>
page  
{ 
   /* size: auto;   /* auto is the initial value */ 

    /* this affects the margin in the printer settings */ 
    margin: 15mm 15mm 15mm 15mm; 
}
body  
{ 
    /* this affects the margin on the content before sending to printer */ 
    margin: 15mm 15mm 15mm 15mm; 
    font-size: 9pt;
    font-family: Courier;
}
thead {color:black;font-family:sans-serif; font-weight: bold; background-color: white; }
table {  border-collapse:collapse;}
td {
        border:1px solid black;
padding: 3px;
    font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
  page-break-inside: avoid;
    }
    tr,td    { page-break-inside:avoid; page-break-after:auto }
 .keeptog  { page-break-inside:avoid; page-break-after:auto }
 
@media print  
{
     table {  border-collapse:collapse;
              font-weight: 300; page-break-inside: auto;
    }
    tr,td    { border:1px solid black; padding: 1px; page-break-inside:avoid; page-break-after:auto; font-size: 9pt;
    font-family: Courier;}
    thead {color:black;font-family:sans-serif; font-weight: 600; background-color: white; }
}
a:link {
    color: darkblue;
    text-decoration: none;
}
</style>