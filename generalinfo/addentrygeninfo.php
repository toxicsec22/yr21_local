<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 
$showbranches=false;
include_once('../switchboard/contents.php');

$allowed=array(1,2,3,4,5);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check


$user=$_SESSION['(ak0)'];
$method='POST';
$whichqry=$_GET['w'];
     switch ($whichqry){
        case 'Client':
            $title='Add New Client';
	    if (!allowedToOpen(2,'1rtc')) { header('addentrygeninfo.php?w=Client&done=2'); }
	    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	    echo comboBox($link,'SELECT `VatTypeNo`, `VatType` FROM `gen_info_1vattype`','VatTypeNo','VatType','vattype');
		 echo comboBox($link,'SELECT `ARClientTypeID`, `ARClientDesc` FROM `gen_info_0arclienttype`','ARClientTypeID','ARClientDesc','ARClientLists');
		  echo comboBox($link,'SELECT `ClientTypeID`, `ClientDesc` FROM `gen_info_0clienttype`','ClientTypeID','ClientDesc','ClientLists');
	    $columnnames=array(
                       array('field'=>'ClientName','type'=>'text','size'=>20,'required'=>true,'list'=>null),
                       array('field'=>'ContactPerson','type'=>'text','size'=>20,'required'=>true,'list'=>null), 
					   array('field'=>'EmailAddress','type'=>'text','size'=>20,'required'=>true,'list'=>null),
                       array('field'=>'StreetAddress', 'type'=>'text','size'=>20,'required'=>true,'list'=>null),
                       array('field'=>'Barangay', 'type'=>'text','size'=>20,'required'=>true,'list'=>null),
                       array('field'=>'TownOrCity','type'=>'text','size'=>20,'list'=>null, 'required'=>true),
                       array('field'=>'Province', 'type'=>'text','size'=>20,'required'=>true, 'list'=>null),
		       array('field'=>'TelNo1','type'=>'text','size'=>20,'list'=>null, 'required'=>true),
                       array('field'=>'TelNo2', 'type'=>'text','size'=>20,'required'=>false, 'list'=>null),
		       array('field'=>'Mobile','caption'=>'Mobile (11 digits - 09123456789)','type'=>'text','size'=>20,'list'=>false, 'required'=>true),
                       array('field'=>'Terms', 'type'=>'text','size'=>10,'value'=>0,'required'=>true, 'list'=>null),
		       array('field'=>'CreditLimit','type'=>'text','size'=>20,'value'=>0,'required'=>true,'list'=>null), 
                       array('field'=>'Remarks', 'type'=>'text','size'=>20,'required'=>true,'list'=>null),
                       //array('field'=>'Inactive', 'type'=>'hidden','size'=>2,'value'=>0,'list'=>null,'required'=>true),
                       array('field'=>'PORequired', 'type'=>'text','size'=>2, 'list'=>'yesno', 'required'=>true),
		       array('field'=>'ARClientType', 'type'=>'text','size'=>10, 'list'=>'ARClientLists', 'required'=>true),
			   array('field'=>'ClientType', 'type'=>'text','size'=>10, 'list'=>'ClientLists', 'required'=>true),
		       // array('field'=>'PDCRequired', 'type'=>'text','size'=>2, 'list'=>'yesno', 'required'=>true),
		       array('field'=>'VatType', 'type'=>'text','size'=>10, 'list'=>'vattype', 'required'=>true),
		       array('field'=>'TIN', 'type'=>'text','size'=>20, 'list'=>null, 'required'=>false),
		       
		      );
    
    $action='praddentrygeninfo.php?w=Client';
    
    $liststoshow=array('yesno');
   include('../backendphp/layout/inputform.php');
	  break;
        
	case 'ClientBranch':
            $title='Client to Branch';
	    

		 if (!allowedToOpen(3,'1rtc')) { header('addentrygeninfo.php?w=ClientBranch&done=2'); }
		
	    $listname='allclients';
           $listcaption='Client';
	   $listvalue='Left(ClientName,20)';
	   $fieldname='ClientNo';
	   $table='1clients';
	   $txnidname='BranchClientID';
	   $sql='SELECT j.BranchClientID,b.Branch, j.BranchNo, j.ClientNo FROM gen_info_1branchesclientsjxn as j join 1branches b on b.BranchNo=j.BranchNo ';
	   $orderby='b.Branch';
	   $lookupprocess='addentrygeninfo.php?w=ClientBranch&edit=0';
	   $columnnames=array('Branch');
	   $addlcondition=' and b.Active<>0 '; $datatoshow=0;
	    include('../backendphp/layout/headerwithclientorsuppcondition.php');
	   
	   include_once('../generalinfo/lists.inc');
	    renderlist('branches');
	    
		
		$title='Add Client To Branch';
		
            include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
            include_once('../generalinfo/lists.inc'); $liststoshow=array('branches','allclients');
             $columnnames=array(
                       array('field'=>'ClientName','type'=>'text','size'=>20,'required'=>true,'list'=>'allclients'),
                       array('field'=>'BranchNo','type'=>'text','size'=>20,'required'=>true,'list'=>'branches')
                 );
             $action='praddentrygeninfo.php?w=ClientBranch'; $fieldsinrow=4;
            include('../backendphp/layout/inputmainform.php');
 ?>
	    </body>
	    </html>
	    <?php
	  break;
      
      case 'Supplier':
            $title='Add New Supplier';
	    if (!allowedToOpen(4,'1rtc')) { header('addentrygeninfo.php?w=Supplier&done=2');}
	    $columnnames=array(
                       array('field'=>'SupplierNo','caption'=>"Supplier Number (must not be existing)",'type'=>'text','size'=>5,'required'=>true,'list'=>'suppliers'),
                       array('field'=>'SupplierName','type'=>'text','size'=>20,'required'=>true,'list'=>null),
                       array('field'=>'ContactPerson','type'=>'text','size'=>20,'required'=>true,'list'=>null),
		       array('field'=>'TIN', 'type'=>'text','size'=>20, 'list'=>null, 'required'=>true),
                       array('field'=>'Address', 'type'=>'textarea','size'=>40,'required'=>true,'list'=>null),
		       array('field'=>'TelNo1','type'=>'text','size'=>20,'list'=>null, 'required'=>true),
                       array('field'=>'TelNo2', 'type'=>'text','size'=>20,'required'=>false, 'list'=>null),
                       array('field'=>'Terms', 'type'=>'text','size'=>10,'value'=>0,'required'=>true, 'list'=>null),
		       
		       array('field'=>'SupplierSince','type'=>'date','size'=>20,'value'=>date('Y-m-d'),'required'=>true,'list'=>null), 
                       array('field'=>'NameonCheck', 'type'=>'text','size'=>20,'required'=>true,'list'=>null),
		       array('field'=>'InvtySupplier', 'caption'=>"1 - Invty, 0 - Supplies, 2 - Auto Repair Shops, 3 - Utility Bills, 4 - Govt, 5 - Lessors",'type'=>'text','size'=>3,'value'=>1,'required'=>true,'list'=>null),
                       array('field'=>'Inactive', 'type'=>'hidden','size'=>2,'value'=>0,'list'=>null,'required'=>false)
		      );
    $action='praddentrygeninfo.php?w=Supplier';
    
    $liststoshow=array('yesno','suppliers');
   include('../backendphp/layout/inputform.php');
   
   $title='Encoded Suppliers Today';
   $sql='SELECT * FROM 1suppliers WHERE DATE(`TimeStamp`)=CURDATE()';
   $columnnames=array('SupplierNo', 'SupplierName', 'ContactPerson', 'TelNo1', 'TelNo2', 'TIN','Address','Terms','InvtySupplier','Inactive','NameonCheck');
   include '../backendphp/layout/displayastable.php';
	  break;
        default:
            break;
	 
      case 'SupplierBranch':
            $title='Add Supplier to Branch';
	    
	    if (!allowedToOpen(5,'1rtc')) { header('addentrygeninfo.php?w=SupplierBranch&done=2'); }
	    $listname='suppliers';
           $listcaption='Supplier';
	   $listvalue='SupplierName';
	   $fieldname='SupplierNo';
	   $table='1suppliers';
	   $txnidname='BranchSupplierID';
	   $sql='SELECT j.BranchSupplierID,b.Branch, j.BranchNo, j.SupplierNo FROM gen_info_1branchessuppliersjxn as j join 1branches b on b.BranchNo=j.BranchNo ';
	   $orderby='b.Branch';
	   $lookupprocess='addentrygeninfo.php?w=SupplierBranch&edit=0';
	   $columnnames=array('Branch');
	   $addlcondition=' and b.BranchNo not in (1,12) ';
	    include('../backendphp/layout/headerwithclientorsuppcondition.php');
	   ?>
	   <form action='praddentrygeninfo.php?w=SupplierBranch' method='post'>
	    <input type='text' name='BranchNo' list='branches' autocomplete=off>
	    <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" />
	    <input type="hidden" name="SupplierNo" value="<?php echo $value; ?>" />
	    <input type="submit" name="submit" value="Add to Branch">
	   </form>
	   <?php
	   include_once('../generalinfo/lists.inc');
	    renderlist('branches');
	    ?>
	    </body>
	    </html>
	    <?php
	  break;	 
	 
     }  
  $link=null; $stmt=null;
?>
</body>
</html>