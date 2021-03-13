</center><title>General Practices</title>
<div id="content"><div style="position: relative; left: 20%;  width:80%; ">
        <style>
        ul, ol {  list-style-position: outside;   padding-left: 2em;}
         li td { font-size: small;}
</style>
<h3><font style="color:maroon; " >Asset Depreciation and Amortization of Prepaid Expenses:</font></h3>
<ol type='i'>
    <li>Depreciation/amortization will start as follows:<br>
        &nbsp; &nbsp; &nbsp; If purchased on the 1st to 15th of the month, CURRENT month.<br>
        &nbsp; &nbsp; &nbsp; If purchased on the 16th to the last day of the month, NEXT month.<br></li>
    <li>Life of prepaid expenses span only up to the time the prepaid become actual expenses.</li>
    <li>General lifespans of assets are as follows, except when specified:<br>
        <table>
            <tr><td>Asset Description</td><td>Account</td><td>Months</td></tr>
            <tr><td>Desktop, Laptop, Aircon, Fax Machine, other office equipment</td><td>Office Equipment</td><td>48</td></tr>
            <tr><td>Cellphone (>P2k), Electric Fans, Fire Extinguishers (tanks only; succeeding refills will be Supplies)</td><td>Office Equipment</td><td>24</td></tr>
            <tr><td>Cellphone (less than P2k)</td><td>Office Equipment</td><td>12</td></tr>
            <tr><td>CCTV cameras and paraphernalia</td><td>Office Equipment</td><td>24</td></tr>
            <tr><td>Crimping Machine, Gasket Maker, Weighing Scale</td><td>Tools & Equipment</td><td>36</td></tr>
            <tr><td>Cars, Trucks</td><td>Transportation Equipment</td><td>60</td>
            <tr><td>Motorcycle</td><td>Transportation Equipment</td><td>60 with salvage value P8k</td>
            <tr><td>Renovations, Signboards (value > 50k)</td><td>Leasehold Improvement</td><td>60</td></tr>
            <tr><td>Renovations (value < 50k)</td><td>Leasehold Improvement</td><td>24</td></tr>
            <tr><td>Renovations (value > 500k, own propery)</td><td>Building Improvement</td><td>84</td></tr>
            <tr><td>Renovations (value > 500k, leased propery)</td><td>Leasehold Improvement</td><td>84</td></tr>
            </tr>
        </table>
    </li>
</ol><br><br>
<h3><font style="color:maroon; " >COD Purchases:</font></h3> 
Follow APTrade process as usual.  Edit voucher to reflect the paid invoice once MRR is encoded.
<br><br>
<h3><font style="color:maroon; " >Entries for collections:</font></h3> 
<b>CASH COLLECTIONS</b>
<table border="2px" style="border-collapse:collapse;">
            <tr><td>Where</td><td>Debit</td><td>Credit</td></tr>
            <tr><td>Collection Receipt</td><td>CashOnHand</td><td>ARTrade</td></tr>
            <tr><td>Deposit</td><td>CashInBank</td><td>CashOnHand</td></tr>
</table><br><br>
<b>CHECK COLLECTIONS (Dated or Postdated)</b>
<table border="2px" style="border-collapse:collapse;">
            <tr><td>Where</td><td>Debit</td><td>Credit</td></tr>
            <tr><td>Collection Receipt</td><td>ARTradePDC</td><td>ARTrade</td></tr>
            <tr><td>Deposit</td><td>CashInBank</td><td>UnclearedPR</td></tr>
</table>
<br><br>
<b>PDC's on HOLD or STALE </b>(to put back as AR)
<table border="2px" style="border-collapse:collapse;">
            <tr><td>Where</td><td>Debit</td><td>Credit</td></tr>
            <tr><td>Deposit - indicate 'Stale' or 'Hold' in DepDetails field</td><td>AROthers</td><td>ARTradePDC</td></tr>
            <tr><td>Bounced Checks - in Remarks, indicate 'Stale' or 'Hold' and DepNo as reference</td><td>ARTrade per original invoice</td><td>AROthers</td></tr>
</table>
<br><br>
<h3><font style="color:maroon; " >Rodlink Transactions:</font></h3> 
<ol type='i'><u>Inventory</u><br>
    <li>Unit cost = Actual cost in USD x Forex rate at time of payment (average if more than one payment)</li>
    <li>After finalizing landed cost, minprice must be set as selling price for 1Rotary. (This will be the cost of 1Rotary, & in turn, 1Rotary must have its own minprice.)</li>
</ol>
<ol type='i'><u>Accounting</u><br>
    <li>Upon downpayment and full payment: &nbsp; DR &nbsp; &nbsp; Inventory in Transit <?php echo str_repeat('&nbsp;', 10); ?> CR &nbsp; &nbsp; Cash in Bank<br>
    <?php echo str_repeat('&nbsp;', 64); ?> DR &nbsp; &nbsp; Bank charges - forex <?php echo str_repeat('&nbsp;', 20); ?> CR &nbsp; &nbsp; Cash in Bank</li><br>
    <li>When accepting MRR into Acctg: <?php echo str_repeat('&nbsp;', 8); ?> DR &nbsp; &nbsp; Inventory <?php echo str_repeat('&nbsp;', 26); ?> CR &nbsp; &nbsp; Inventory in Transit</li>Inventory in Transit must be zero value for the shipment that has arrived.<br><br>
    <li>Complete payment and recording process when the shipment arrives at the warehouses:<br>
        <table border="2px" style="border-collapse:collapse;"><tr><td>Step</td><td>Rodlink</td><td>Central/CDO Warehouse</td></tr>
        <tr><td>1</td><td>Record (Invty) MRR and Purchase (Acctg) from <b>Sanden</b></td><td></td></tr>
        <tr><td>2</td><td>Make sales invoices to different companies & add to Acctg</td><td></td></tr>
        <tr><td>3</td><td></td><td>Record (Invty) MRR and Purchase (Acctg) from <b>Rodlink</b></td></tr>
        <tr><td>4</td><td></td><td>Make voucher payment to <b>Rodlink</b></td></tr>
        <tr><td>5</td><td>Record payment in deposit</td><td></td></tr>
        </table><br>
        Ending inventory of Rodlink must be zero in both Acctg & Invty data.  All receivables must also be <u>zero</u>.
    </li>
</ol><br><br>
</div>
</div>
<center>

	
	

