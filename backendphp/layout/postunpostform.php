<form method='post' action='/<?php echo $url_folder;?>/backendphp/layout/postunpost.php'  style='display:inline;'>
    <input type='hidden' name='TxnID' value='<?php echo $txnid;?>'>
    <input type='hidden' name='Table' value='<?php echo $table;?>'>
    <input type='hidden' name='Post' value='<?php echo $post;?>'>
    <input type='hidden' name='txntype' value='<?php echo (!isset($txntype)?0:$txntype);?>'>
    <input type='submit' name='submit' value='Post/Unpost'>
</form>&nbsp &nbsp &nbsp &nbsp