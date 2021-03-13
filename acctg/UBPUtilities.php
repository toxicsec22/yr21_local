<?php
	/**
	 * This file is for generating text files required by unionbank
	 */

	function optionalRound($num){
		$f = round($num, 2);
		return sprintf("%-s", $f);
	}

	function removeSpecialCharacters($str){
		return preg_replace('([ñÑ~`!@#$%^&*(){}\[\];:<>,?|\-=+\*])', '', $str);
	}

	function cw_generateHeader($totalCount, $totalAmount, $date, $lineEnder = "\n"){
		return sprintf("BAT|%d|%s|%s-1%s", $totalCount, optionalRound($totalAmount), $date, $lineEnder);
	}

	function cw_generateDetail(
		$clientReferenceNumber, $amount, $payeeName, $payeeZipCode, 
		$payeeTin, $payeeAddress, $checkDate, $branchRealeasingorBackToClient, 
		$branchDeliveryAddressCode, $printingBranch, $encodeEWTFields, 
		$taxPeriodFrom, $taxPeriodTo, $corpnameAndAddressAndTin, $lineEnder = "\n")
	{
			return sprintf("CHU|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s%s", $clientReferenceNumber, optionalRound($amount), trim($payeeName), $payeeZipCode, $payeeTin, $payeeAddress, $checkDate, $branchRealeasingorBackToClient, $branchDeliveryAddressCode, $printingBranch, $encodeEWTFields, $taxPeriodFrom, $taxPeriodTo, $corpnameAndAddressAndTin, $lineEnder);
	}

	function cw_generateCWT($ATCCode, $ATCDescription, $first, $second, $third, $taxwitheld, $total, $lineEnder = "\n"){
		return sprintf("CWTLN|%s|%s|%s|%s|%s|%s|%s%s", $ATCCode, $ATCDescription, optionalRound(floatval($first)), optionalRound(floatval($second)), optionalRound(floatval($third)), optionalRound(floatval($taxwitheld)), optionalRound(floatval($total)), $lineEnder);
	}


	function cw_generateVoucherHeader(string $remark, string $lineEnder = "\n") : string {
		$headerLine = "INV|";
		$result = "INV|Remarks:" . $lineEnder;		
		$result = $result . sprintf("INV|%-120.120s%s", $remark, $lineEnder);
		$result = $result . "INV|" . $lineEnder;		
		$headerLine = $headerLine . sprintf("%-12.12s", "Branch")
		. sprintf("%-60.60s", "Particulars")
		. sprintf("%-15.15s", "TIN")
		. sprintf("%-20.20s", "Debit")
		. sprintf("%-15.15s", "Amount") ;
		$result = $result . $headerLine . $lineEnder;
		$result = $result . "INV|" . sprintf("%'--130.130s", "-") . $lineEnder;
		return $result;
	}


	function cw_generateVoucherItemLine($items, bool $includeBranch = false, string $lineEnder = "\n"){
		$result = "INV|";
		if($includeBranch)
			$branchText = $items["Branch"];
		else
			$branchText = '';
		$result = $result . sprintf("%-11.11s ", trim($branchText))
		. sprintf("%-59.59s ", trim($items["Particulars"]))
		. sprintf("%-14.14s ", trim($items["TIN"]))
		. sprintf("%-19.19s ", trim($items["DebitAccount"]))
		. sprintf("%-11.2f", trim($items["Amount"]));
		return $result . $lineEnder;
	}

	function ch_generateCheckHouseLine(float $amount, $brstn, $clientBankAccountNo, $checkno, $checkdate, $typeOfCheck, $uniqueID, $ender="\n") {
		return sprintf("D,%-.3f,%s,%s,%s,%s,%s,%s%s", $amount, removeSpecialCharacters($brstn), removeSpecialCharacters($clientBankAccountNo), removeSpecialCharacters($checkno), $checkdate, $typeOfCheck, $uniqueID, $ender);
	}

	function ch_generateHead($payorCode, $ender = "\n"){
		return sprintf("P,%s%s",$payorCode,  $ender);
	}

	$path=$_SERVER['DOCUMENT_ROOT']; 
	include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	include_once($path.'/acrossyrs/dbinit/userinit.php');

	$whichqry=$_GET['w'];
	if(isset($_GET['m'])){
		$msgcode = $_GET['m'];
	}

	switch ($whichqry) {
		case 'Checkwriter':
			if(!allowedToOpen(5405, '1rtc')){
				header('Location:/'.$url_folder.'/index.php?denied=true');
				exit;
			}
			echo "<html><head><title>UBP Utilities</title></head>";

			/* IMPORTS */
			include_once('../switchboard/contents.php');

			include_once('../backendphp/functions/getuser.php');
			include_once '../backendphp/layout/displayastablewithcheckbox.php';
			if(isset($msgcode)){
				switch ($msgcode) {
					case 0:
						echo "<h3>Invalid Request</h3>";
						break;
				}
			}

			$sql=<<<SQL
				SELECT m.CheckNo, m.CVNo, FORMAT(SUM(s.Amount),2) AS Amount, 
						m.Payee, s.TIN, e.Address, m.DateOfCheck, 
						m.Remarks, m.Posted
					FROM
						acctg_2cvmain as m 
						LEFT JOIN acctg_2cvsub as s ON m.CVNo = s.CVNo
						LEFT JOIN gen_info_1tinforexpenses as e ON s.TIN = e.TIN
					WHERE m.CreditAccountID = (
							SELECT AccountID FROM banktxns_1maintaining
							WHERE OwnedByCompany = ? AND AccountID BETWEEN 141 AND 145
						) AND m.Cleared IS NULL 
						AND DateOfCheck >= CURDATE()
					--	AND CAST(m.CheckNo AS SIGNED) <= 500
						AND CAST(m.CheckNo AS SIGNED) <> 0
						AND IsNumeric(m.CheckNO)
						AND CAST(m.CheckNo AS SIGNED) IS NOT NULL
					GROUP BY m.CVNo
				UNION
					SELECT mm.CheckNo, mm.CVNo, FORMAT(SUM(ss.Amount),2) AS Amount, 
						mm.Payee, ss.TIN, ee.Address, mm.DateOfCheck, 
						mm.Remarks, mm.Posted
					FROM
						acctg_4futurecvmain as mm
						LEFT JOIN acctg_4futurecvsub as ss ON mm.CVNo = ss.CVNo
						LEFT JOIN gen_info_1tinforexpenses as ee ON ss.TIN = ee.TIN
					WHERE mm.CreditAccountID = (
							SELECT AccountID FROM banktxns_1maintaining
							WHERE OwnedByCompany = ? AND AccountID BETWEEN 141 AND 145
						)
					--	AND CAST(mm.CheckNo AS SIGNED) <= 500
						AND CAST(mm.CheckNo AS SIGNED) <> 0
						AND IsNumeric(mm.CheckNO)
						AND CAST(mm.CheckNo AS SIGNED) IS NOT NULL
					GROUP BY mm.CVNo
					ORDER BY CVNo, DateOfCheck
SQL;
			//By company
			$stmt = $link->prepare($sql);
			$stmt->bindValue(1, $_SESSION['*cnum']);
			$stmt->bindValue(2, $_SESSION['*cnum']);
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt = null;

			if(count($data) > 0){
				//tuloy
				//Headers for the table with checkbox
				include_once '../backendphp/layout/regulartablestyle.php';
				//Style
				echo <<<HTML
					<style type="text/css">
						table{
							font-size: 10pt;
							font-family: 'Helvitica', 'Arial', sans-serif;
						}

						table tr:nth-child(odd){
							background-color: #FFFFCC;
						}
						table tr:nth-child(even){
							background-color: #FFFFFF;
						}
						table tr:hover{
							background-color: #cccccc;
						}
					</style>
HTML;
				
				$headers = array(' Check Number ', ' Client Reference/Voucher No. ', ' Amount ', ' Payee ', ' TIN ', ' Address ', ' Date Of Check ', ' Remarks ', ' Posted ');
				createTableWithCheckbox($headers, $data, 
					"UBPUtilities.php?w=GenerateUBPCheckwriter", "POST", 
					"UBP Checkwriter", "", "CVNo");
			}
			else{
				echo '<h1> No data to show </h1>';
				exit;
			}
			break;
		case 'GenerateUBPCheckwriter':
			if (!allowedToOpen(5401,'1rtc')) { header('Location:/'.$url_folder.'/index.php?denied=true');}
			if(count($_POST['tablevalue']) == 0){
				header("Location: UBPUtilities.php?w=Checkwriter&m=0");
				exit;
			}
			$qmarks = str_repeat('?, ', count($_POST['tablevalue']) - 1) . '?';
			$count = 0; 
			$total = 0.0;
			//get all checked
			$CVNumbersSQL = array();
			$subsCombinationTableSQL = <<<SQL
				CREATE TEMPORARY TABLE IF NOT EXISTS subsCombination AS (
					SELECT b.Branch, s.Particulars, s.ForInvoiceNo, s.TIN, ca.ShortAcctID as DebitAccount, s.Amount, ca.AccountID, m.CVNo
					FROM acctg_2cvsub as s 
					LEFT JOIN acctg_2cvmain as m ON s.CVNo = m.CVNo
					LEFT JOIN acctg_1chartofaccounts as ca ON s.DebitAccountID = ca.AccountID
					LEFT JOIN 1branches as b ON s.BranchNo = b.BranchNo);
SQL;
			$subsInsertTableSQL = <<<SQL
			INSERT INTO subsCombination SELECT b.Branch, s.Particulars, s.ForInvoiceNo, s.TIN, ca.ShortAcctID as DebitAccount, s.Amount, ca.AccountID, m.CVNo
			FROM acctg_4futurecvsub as s
			LEFT JOIN acctg_4futurecvmain as m ON s.CVNo = m.CVNo
			LEFT JOIN acctg_1chartofaccounts as ca ON s.DebitAccountID = ca.AccountID
			LEFT JOIN 1branches as b ON s.BranchNo = b.BranchNo;
SQL;
			$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
			$stmtTemp = $link->prepare($subsCombinationTableSQL);
			$stmtTemp->execute();
			$stmtTemp = null;
			$stmtTemp2 = $link->prepare($subsInsertTableSQL);
			$stmtTemp2->execute();
			$stmtTemp2 = null;

			$sql = <<<SQL
				SELECT m.CVNo, SUM(s.Amount) AS Amount, 
						m.Payee, s.TIN, e.Address, DATE_FORMAT(m.DateOfCheck, '%m/%d/%Y') As DateOfCheck, 
						m.Remarks, m.Posted,
						DATE_FORMAT(MAKEDATE(YEAR(m.DateOfCheck), 1) + INTERVAL QUARTER(m.DateOfCheck) QUARTER - INTERVAL 1 QUARTER, '%m/%d/%Y') AS FirstDayOfQuarter,
						DATE_FORMAT(MAKEDATE(YEAR(m.DateOfCheck), 1) + INTERVAL QUARTER(m.DateOfCheck) QUARTER - INTERVAL 1 DAY, '%m/%d/%Y') as LastDayOfQuarter
					FROM
						acctg_2cvmain as m 
						LEFT JOIN acctg_2cvsub as s ON m.CVNo = s.CVNo
						LEFT JOIN gen_info_1tinforexpenses as e ON s.TIN = e.TIN
					WHERE m.CVNo IN ({$qmarks})
					GROUP BY m.CVNo
				UNION
					SELECT mm.CVNo, SUM(ss.Amount) AS Amount, 
						mm.Payee, ss.TIN, ee.Address, DATE_FORMAT(mm.DateOfCheck,'%m/%d/%Y') As DateOfCheck, 
						mm.Remarks, mm.Posted,
						DATE_FORMAT(MAKEDATE(YEAR(mm.DateOfCheck), 1) + INTERVAL QUARTER(mm.DateOfCheck) QUARTER - INTERVAL 1 QUARTER, '%m/%d/%Y') AS FirstDayOfQuarter,
						DATE_FORMAT(MAKEDATE(YEAR(mm.DateOfCheck), 1) + INTERVAL QUARTER(mm.DateOfCheck) QUARTER - INTERVAL 1 DAY, '%m/%d/%Y') as LastDayOfQuarter
					FROM
						acctg_4futurecvmain as mm
						LEFT JOIN acctg_4futurecvsub as ss ON mm.CVNo = ss.CVNo
						LEFT JOIN gen_info_1tinforexpenses as ee ON ss.TIN = ee.TIN
					WHERE mm.CVNo IN ({$qmarks})
					GROUP BY mm.CVNo
					ORDER BY CVNo, DateOfCheck
SQL;
			
			$stmt = $link->prepare($sql);
			$stmt->execute(array_merge($_POST['tablevalue'], $_POST['tablevalue']));
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt = null;
			$grandTotal = 0.0;
			$count = 0;
			$dt = new DateTime();

			//get account number of paying company
			$actNumSQL = "SELECT AcctNo FROM banktxns_1maintaining WHERE OwnedByCompany = ? AND AccountID BETWEEN 141 AND 145";
			$actNumStmt = $link->prepare($actNumSQL);
			$actNumStmt->bindValue(1, $_SESSION['*cnum']);
			$actNumStmt->execute();
			$date = $actNumStmt->fetchColumn();
			$actNumStmt = null;
			$headers = array('Branch', 'Particulars', 'For Invoice No.', 'TIN', 'Debit', 'Amount');
			$result = '';
			//Generate Per voucher
			foreach ($data as $row) {
				$count = $count + 1;

				//Get particulars first, to calculate EWTs
				$sql = <<<SQL
					SELECT Branch, Particulars, ForInvoiceNo, TIN, DebitAccount, Amount, AccountID
					FROM subsCombination
					WHERE CVNo = ?
SQL;
				$subsstmt = $link->prepare($sql);
				$subsstmt->bindValue(1, $row['CVNo']);
				$subsstmt->execute();
				$particulars = $subsstmt->fetchAll(PDO::FETCH_ASSOC);
				$subsstmt = null;
				$branchFlag = true;
				$particularText = '';
				$withHoldingTotal = 0.0;
				foreach ($particulars as $particular) {
					//If there is an EWT in particular text, count it
					if($particular['AccountID'] == 504) //Means that the account is an expanded witholding tax
						$withHoldingTotal = $withHoldingTotal + abs($particular['Amount']);
						
					//Write the item line appended to text
					$particularText = $particularText . cw_generateVoucherItemLine($particular, $branchFlag);
					$branchFlag = false;
				}

				//Get other details
				$grandTotal = $grandTotal + $row['Amount'];

				$encodeEWTFlag = $withHoldingTotal > 0;
				if($encodeEWTFlag){
					$witholdingText = optionalRound($withHoldingTotal);
					$encodeEWTFlagText = '1';
				}
				else{
					$witholdingText = '0';
					$encodeEWTFlagText = '0';
				}
				$result = $result . cw_generateDetail($row['CVNo'], $row['Amount'], $row['Payee'], '', $row['TIN'], $row['Address'], $row['DateOfCheck'], 'RET', '100496', '1', '1', $row['FirstDayOfQuarter'], $row['LastDayOfQuarter'], $encodeEWTFlagText,"\n");
				$result = $result . cw_generateCWT("", "", $row['Amount'], "0", "0", $witholdingText, $row['Amount'], "\n");
				$result = $result . cw_generateVoucherHeader($row['Remarks'], "\n");
				$result = $result . $particularText;
			}
			$result = cw_generateHeader($count, $grandTotal, $dt->format("ymdhis")) . $result;
			$today = date('ymdhis');
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header("Content-Disposition: attachment; filename=\"Checkwriter UBP CMS-OPS-{$today}.txt\""); 
			header('Content-Transfer-Encoding: binary');
			header('Connection: Keep-Alive');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . strlen($result));
			echo $result;
			$stmtTemp = null;
			break;
		case 'Checkhouse':
			if(count($_POST['ID']) < 1){
				header("Location:{$_SERVER['HTTP_REFERER']}&m=0");
				exit;
			}
			$sql = <<<SQL
			SELECT BankPayeeCode 
			FROM banktxns_1maintaining
			WHERE OwnedByCompany = ? AND AccountID BETWEEN 141 AND 145
SQL;
			//Link getting null here, for some reason.
			$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
			$stmt = $link->prepare($sql);
			$stmt->bindValue(1, $_SESSION['*cnum']);
			$stmt->execute();
			$PayeeCode = $stmt->fetchColumn(0);

			if(!allowedToOpen(5509, '1rtc')){
				header('Location:/'.$url_folder.'/index.php?denied=true');
				exit;
			}

			$qmarks = str_repeat('?, ', count($_POST['ID']) - 1) . '?';

			$sql = <<<SQL
				SELECT 
					SUM(s.Amount)-IFNULL(SUM(sd.Amount),0) AS PDC, 
					m.CheckBRSTN AS PDCBRSTN, 
					m.ClientCheckBankAccountNo, 
					m.CheckNo as PDCNo, 
					DATE_FORMAT(m.DateOfCheck, '%m/%d/%Y') As DateOfPDC
				FROM 
					acctg_2collectmain AS m
					LEFT JOIN acctg_2collectsub AS s ON m.TxnID = s.TxnID
					LEFT JOIN acctg_2collectsubdeduct AS sd ON s.TxnID = sd.TxnID
				WHERE 
					CONCAT(s.BranchNo, m.CRNo) IN ({$qmarks}) 
					AND m.ClientCheckBankAccountNo IS NOT NULL 
					AND m.SendToBank = 0
					AND m.WithBank = 0
					AND DATEDIFF(m.DateOfCheck, CURDATE()) > 7
				GROUP BY m.TxnID
			UNION
				SELECT AmountOfPDC, PDCBRSTN, ClientCheckBankAccountNo, PDCNo, 
				DATE_FORMAT(DateOfPDC, '%m/%d/%Y') As DateOfPDC
				FROM acctg_3undepositedpdcfromlastperiod
				WHERE CONCAT(BranchNo, CRNo) IN ({$qmarks}) AND ClientCheckBankAccountNo IS NOT NULL;
SQL;
			$result = '';
			$stmt = $link->prepare($sql);
			$stmt->execute(array_merge($_POST['ID'], $_POST['ID']));
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo $sql.'<br>';

print_r($_POST['ID']); exit();
			//Code to generate CSV
			$result .= ch_generateHead($PayeeCode);
			for($i = 0; $i < count($data); $i++){
				$uniqueID = date('ymdhis') . $i+1;
				$result .= ch_generateCheckHouseLine($data[$i]['PDC'], $data[$i]['PDCBRSTN'], 
							$data[$i]['ClientCheckBankAccountNo'], $data[$i]['PDCNo'], 
							$data[$i]['DateOfPDC'], 'Local', $uniqueID);
			}

			
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header("Content-Disposition: attachment; filename=\"{$PayeeCode}.csv\""); 
			header('Content-Transfer-Encoding: binary');
			header('Connection: Keep-Alive');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . strlen($result));			
			echo $result;
			break;
		default:
			header('Location:/'.$url_folder.'/index.php?denied=true');
			break;
	}
