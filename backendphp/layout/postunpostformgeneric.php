<form method='post' action='#'  style='display:inline;'>
    <input type='hidden' name='<?php echo $txnidname;?>' value='<?php echo $txnid;?>'>
    <input type='hidden' name='Table' value='<?php echo $main;?>'>
    <input type='hidden' name='Posted' value='<?php echo $postfield;?>'>
    <input type='hidden' name='Post' value='<?php echo $postvalue;?>'>
    
    <input type='submit' name='post' value='Post/Unpost'>
</form>
<?php
if (isset($_POST['post'])){
    $sql='Update `'.$_POST['Table'].'` SET `'.$_POST['Posted'].'`='.$_POST['Post'].', `'.$_POST['Posted'].'ByNo`='.$_SESSION['(ak0)'].' WHERE `'.$txnidname.'`='.$_POST[$txnidname]; 
    $stmt=$link->prepare($sql); $stmt->execute();  

    if($postfield=='APVPosted'){
        $sql='SELECT CVNo FROM acctg_2cvmain WHERE CreditAccountID<>403 AND (CreditAccountID NOT IN (SELECT AccountID FROM banktxns_1maintaining) AND `'.$txnidname.'`='.$_POST[$txnidname];
        $stmt=$link->query($sql); $result=$stmt->fetch();
        if($stmt->rowCount()==0) { goto skipupdate;}
        $sql='UPDATE acctg_2cvmain SET Posted='.$_POST['Post'].' WHERE CreditAccountID<>403 AND (CreditAccountID NOT IN (SELECT AccountID FROM banktxns_1maintaining) AND `'.$txnidname.'`='.$_POST[$txnidname];  echo $sql;
        $stmt=$link->prepare($sql); $stmt->execute();
        skipupdate:
    }

header("Location:".$_SERVER['HTTP_REFERER']);
}
?>