<?php
$editok=!allowedToOpen(7000,'1rtc')?false:editOk('invty_2sale',$txnid,$link,$txntype);
    if ($editok){
        $editsub=true;
        $columnsubdistri=array('ChargeTo','ChargeAmount','EncodedBy');
    } else {
        $editsub=false;
        $columnsubdistri=array('ChargeTo','ChargeAmount','EncodedBy', 'TimeStamp');
    }

$sqlsubdistri='Select s.*, concat(e1.Nickname, " ", e1.Surname) as ChargeTo, e.Nickname as EncodedBy from invty_2salesubauditdistri s
left join `1employees` as e1 on s.ChargeToIDNo=e1.IDNo
left join `1employees` as e on s.EncodedByNo=e.IDNo where TxnID='.$txnid;
    $stmt=$link->query($sqlsubdistri);
    $resultsubdistri=$stmt->fetchAll();
    if ($stmt->rowCount()==0){goto noform;} else {
        if ($editok){
        ?><br><form style='display:inline' method=post action='prauditdistri.php?w=add<?php echo '&TxnID='.$txnid.'&txntype='.$txntype; ?>'>
        <input type='text' name='ChargeToIDNo' size=5 autocomplete=false>
        <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" />
        <input type="hidden" name='Date' value='<?php echo $result['Date']?>'>
    <input type="hidden" name='BranchNo' value='<?php echo $result['BranchNo']?>'>
        <input type=submit name=submit value='Add ChargeTo IDNo'>
        </form>
        &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp<form style='display:inline' method=post action='prauditdistri.php?w=send<?php echo '&TxnID='.$txnid.'&txntype='.$txntype; ?>'>
    <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" />
    <input type="hidden" name='Date' value='<?php echo $result['Date']?>'>
    <input type="hidden" name='BranchNo' value='<?php echo $result['BranchNo']?>'>
    <input type=submit name=submit value='Send to Acctg'>
</form><br><br>
<?php
    }
    }
    $sub='';$subcol=''; $totaldistri=0;
    foreach ($columnsubdistri as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($resultsubdistri as $row){
        foreach ($columnsubdistri as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        $sub='<tr>'.$sub.($editsub?'<td><a href="editinvspecifics.php?edit=2&w=SaleSubDistriEdit&txntype='.$txntype.'&TxnSubId='.$row['TxnSubId'].'&TxnID='.$row['TxnID'].'">Edit</a>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp<a href=praddsale.php?TxnID='.$txnid.'&TxnSubId='.$row['TxnSubId'].'&action_token='.$_SESSION['action_token'].'&w=SaleSubDistriDel&txntype='.$txntype.' OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
        $totaldistri=$totaldistri+$row['ChargeAmount'];
    }
    $sub='<a href="../invty/addeditsale.php?txntype=InvtyChargesDistri" target="_blank">Guide to distribution of charges</a><br><br>Distribution of Charges:<br><table><tr>'.$subcol.'<td>Edit?</td></tr><tbody><tr>'.$sub.'</tr></tbody></table><br>
    Distribution Total: '.number_format($totaldistri,2);
    
    echo $sub .'<br><br>We acknowledge that we are accountable for these missing items, and that this total amount will be deducted from our salaries.<br><br><br><br>__________________________________<br>Signature above printed names';
    noform:
?>