<form method='post' action='/<?php echo $url_folder;?>/backendphp/layout/postunpost.php'  style='display:inline;'>
    <input type='hidden' name='TxnID' value='<?php echo $txnid;?>'>
    <input type='hidden' name='TxnIDName' value='<?php echo (!isset($txnidname))?'TxnID':$txnidname;?>'>
    <input type='hidden' name='Table' value='<?php echo $table;?>'>
    <input type='hidden' name='Posted' value='<?php echo $postfield;?>'>
    <input type='hidden' name='Post' value='<?php echo $postvalue;?>'>
    <input type='hidden' name='txntype' value='<?php echo (!isset($txntype)?0:$txntype);?>'>
    <input type='hidden' name='DateField' value='<?php echo (!isset($datefield)?'Date':$datefield);?>'>
    <input type='submit' name='submit' value='Post/Unpost'>
</form>&nbsp &nbsp &nbsp &nbsp