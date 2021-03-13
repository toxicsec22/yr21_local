<?php
switch ($which){
         case 'Sale': 
            $filetoopen='addeditclientside'; $which='Sale'; $txnid='TxnID'; break;
         case 'Collect':
             $filetoopen='addeditclientside'; $which='Collect'; $txnid='TxnID'; break;
        case 'Bounced': 
             $filetoopen='addeditclientside'; $which='Bounced'; $txnid='TxnID'; break;
         case 'Interbranch': 
         case 'InterbranchAdj': 
         case 'InterbranchPaymt': 
             $filetoopen='addeditclientside'; $which='Interbranch'; $txnid='TxnID'; break;
         case 'Deposit': 
             $filetoopen='addeditdep';  $txnid='TxnID'; break;
         case 'Purchase': 
            $filetoopen='addeditsupplyside'; $txnid='TxnID'; break;
         case 'CV': 
            $filetoopen='addeditsupplyside'; $txnid='CVNo'; break;
         case 'JV': 
             $filetoopen='addeditsupplyside'; $txnid='JVNo'; break;
         case 'AssetandDepr': $filetoopen='assetanddepr'; $txnid='DeprID'; break;
         case 'PrepaidExpense': $filetoopen='prepaidandamort'; $txnid='AmortID'; break;
         default: $filetoopen='lookupgenacctg'; $txnid='TxnID'; break;
        }
?>