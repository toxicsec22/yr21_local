<datalist id="banks">
<?php  
if(allowedToOpen(62512,'1rtc')) { $sqllimitbanksdep=''; $sqllimitbanks='';} 

if(allowedToOpen(62511,'1rtc')){ 
    $sql=" UNION SELECT ca.ShortAcctID,ca.AccountID,ca.Remarks,ca.AccountID FROM `acctg_1chartofaccounts` ca WHERE ca.AccountID=705 ";
    } else {$sql=""; }
            $sql="SELECT ca.ShortAcctID,ca.AccountID,ca.Remarks,m.AcctNo FROM banktxns_1maintaining m JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=m.AccountID "
                    . $sqllimitbanks
                    . $sql. " ORDER BY ShortAcctID;";
		foreach ($link->query($sql) as $row) {
                ?>
                <option value="<?php echo $row['ShortAcctID']; ?>" label="<?php echo $row['AccountID']. " - " . $row['AcctNo']; ?>"></option>
                <?php
                } // end while
?>             
</datalist>
