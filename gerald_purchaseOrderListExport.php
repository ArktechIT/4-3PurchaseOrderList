<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<?php
	include $_SERVER['DOCUMENT_ROOT']."/version.php";
	$path = $_SERVER['DOCUMENT_ROOT']."/".v."/Common Data/";
	set_include_path($path);
	include('PHP Modules/mysqliConnection.php');
	include('PHP Modules/anthony_retrieveText.php');
	$queryLimit = 50;
	
	if($_SESSION['idNumber']!='0346')
	{
		//~ echo "TIME FIRST";
		//~ exit(0);
		$filename = "API PO Summary (".date('Y-m-d').").xls";
		header('Content-type: application/ms-excel');
		header('Content-Disposition: attachment; filename='.$filename);
	}

	$sqlFilter = $_POST['sqlFilter'];
	$exportType = (isset($_POST['export'])) ? $_POST['export'] : 'exportAll';
		
	$totalRecords = 0;
	$sql = "SELECT poNumber, supplierId, supplierType, poStatus, poCurrency, poInputDateTime FROM purchasing_podetailsnew ".$sqlFilter."";
	$tableContent = '';
	$poNumberArray = $supplierNameArray = $poInputDateTimeArray = array();
	// ----------------------------------------------------- Execute Query --------------------------------------------------
	$query = $db->query($sql);
	if($query->num_rows > 0)
	{
		while($result = $query->fetch_array())
		{
			$poNumber = $result['poNumber'];
			$supplierId = $result['supplierId'];
			$supplierType = $result['supplierType'];
			$poStatus = $result['poStatus'];
			$poCurrency = $result['poCurrency'];
			$poInputDateTime = $result['poInputDateTime'];
			
			$supplierName = '';
			$sql = "SELECT supplierName FROM purchasing_supplier WHERE supplierId = ".$supplierId." LIMIT 1";
			if($supplierType == 2) $sql = "SELECT subconName FROM purchasing_subcon WHERE subconId = ".$supplierId." LIMIT 1";
			$querySupplier = $db->query($sql);
			if($querySupplier->num_rows > 0)
			{
				$resultSupplier = $querySupplier->fetch_row();
				$supplierName = $resultSupplier[0];
			}
			
			$poNumberArray[] = $poNumber;
			$supplierNameArray[$poNumber] = $supplierName;
			$poStatusArray[$poNumber] = $poStatus;
			$poCurrencyArray[$poNumber] = $poCurrency;
			$poInputDateTimeArray[$poNumber] = $poInputDateTime;
		}
	}
	
	echo "
	<table cellpadding=0 cellspacing=0 border='1'>
		<thead>
			<tr>
				<th></th>
				<th>PO<br>".displayText('L855')."</th>
				<th>NO.</th>
				<th>".displayText('L367')."</th>
				<th>".displayText('L516')."</th>
				<th>".displayText('L342')."</th>
				<th>".displayText('L132')."</th>
				<th>".displayText('L246')."</th>
				<th>".displayText('L247')."</th>
				<th>".displayText('L31')."</th>
				<th>".displayText('L112')."</th>
				<th>".displayText('L32')."</th>
				<th>".displayText('L743')."</th>
				<th>".displayText('L172')."</th>
				<th>".displayText('L1698')."</th>
			</tr>
		</thead>
	";
	// echo "
	// <table cellpadding=0 cellspacing=0 border='1'>
	// 	<thead>
	// 		<tr>
	// 			<th></th>
	// 			<th>PO<br>".displayText('L855')."</th>
	// 			<th>".displayText('L367')."</th>
	// 			<th>".displayText('L342')."</th>
	// 			<th>".displayText('L132')."</th>
	// 			<th>".displayText('L246')."</th>
	// 			<th>".displayText('L247')."</th>
	// 			<th>".displayText('L31')."</th>
	// 			<th>".displayText('L112')."</th>
	// 			<th>".displayText('L32')."</th>
	// 			<th>".displayText('L743')."</th>
	// 			<th>".displayText('L172')."</th>
	// 			<th></th>
	// 		</tr>
	// 	</thead>
	// ";
	echo "<tbody>";
	
	$prevPoNumber = '';
	$tableContent = "";	
	$count = 0;
	$sql = "SELECT GROUP_CONCAT(poContentId ORDER BY poContentId SEPARATOR ',') as poContentIds, `poNumber`, `lotNumber`, `productId`, `itemName`, `itemDescription`, `itemQuantity`, SUM(itemPrice) as totalItemPrice, supplierAlias, issueDate, receivingDate FROM `purchasing_pocontents` WHERE `poNumber` IN('".implode("','",$poNumberArray)."') AND itemStatus != 2 GROUP BY poNumber, lotNumber ORDER BY poNumber, receivingDate, dataOne, lotNumber";
	$querySupplyPo = $db->query($sql);
	if($querySupplyPo->num_rows > 0)
	{
		while($resultSupplyPo = $querySupplyPo->fetch_array())
		{
			$poContentIds = $resultSupplyPo['poContentIds'];
			$poNumber = $resultSupplyPo['poNumber'];
			$lotNumber = $resultSupplyPo['lotNumber'];
			$productId = $resultSupplyPo['productId'];
			$itemQuantity = $resultSupplyPo['itemQuantity'];
			$totalItemPrice = $resultSupplyPo['totalItemPrice'];
			$supplierAlias = $resultSupplyPo['supplierAlias'];
			$issueDate = $resultSupplyPo['issueDate'];
			$receivingDate = $resultSupplyPo['receivingDate'];
			$itemName = $resultSupplyPo['itemName'];
			$itemDescription = $resultSupplyPo['itemDescription'];
			
			$poCurrency = $poCurrencyArray[$poNumber];
			$supplierName = $supplierNameArray[$poNumber];
			$poInputDateTime = $poInputDateTimeArray[$poNumber];
			$poStatus = $poStatusArray[$poNumber];

			if($poStatus==0)		$status = 'Ongoing';
			else if($poStatus==1)	$status = 'For Email';
			else if($poStatus==2)	$status = 'Canceled';
			else if($poStatus==3)	$status = 'Closed';
			else if($poStatus==4)	$status = 'Finished';
			
			if($poCurrency==1)		$sign = 'Dollar';
			else if($poCurrency==2)	$sign = 'Peso';
			else if($poCurrency==3)	$sign = 'Yen';
			
			$identifier = $partId = '';
			$sql = "SELECT partId, identifier FROM ppic_lotlist WHERE lotNumber LIKE '".$lotNumber."' LIMIT 1";
			$queryLotList = $db->query($sql);
			if($queryLotList AND $queryLotList->num_rows > 0)
			{
				$resultLotList = $queryLotList->fetch_assoc();
				$identifier = $resultLotList['identifier'];
				$partId = $resultLotList['partId'];
			}			
			
			$lotNumberArray = array();
			if($identifier==1)
			{
				$itemName = $itemDescription;
				$itemDescription = '';
				
				$sql = "SELECT partName FROM cadcam_parts WHERE partId = ".$partId." LIMIT 1";
				$queryParts = $db->query($sql);
				if($queryParts AND $queryParts->num_rows > 0)
				{
					$resultParts = $queryParts->fetch_assoc();
					$itemDescription = $resultParts['partName'];
				}
				
				$sql = "SELECT lotNumber FROM ppic_lotlist WHERE poContentId IN(".$poContentIds.") AND identifier = 1";
				$queryLotList = $db->query($sql);
				if($queryLotList AND $queryLotList->num_rows > 0)
				{
					while($resultLotList = $queryLotList->fetch_assoc())
					{
						$lotNumberArray[] = $resultLotList['lotNumber'];
					}
				}
			}
			else
			{
				$sql = "SELECT lotNumber FROM ppic_lotlist WHERE poId IN(".$poContentIds.") AND identifier = 4";
				$queryLotList = $db->query($sql);
				if($queryLotList AND $queryLotList->num_rows > 0)
				{
					while($resultLotList = $queryLotList->fetch_assoc())
					{
						$lotNumberArray[] = $resultLotList['lotNumber'];
					}
				}
			}
			
			$actualFinish = '0000-00-00';
			$sql = "SELECT actualFinish FROM ppic_workschedule WHERE lotNumber IN('".implode("','",$lotNumberArray)."') AND status = 1 AND processCode = 137 ORDER BY actualFinish DESC LIMIT 1";
			$queryWorkSchedule = $db->query($sql);
			if($queryWorkSchedule AND $queryWorkSchedule->num_rows > 0)
			{
				$resultWorkSchedule = $queryWorkSchedule->fetch_assoc();
				$actualFinish = $resultWorkSchedule['actualFinish'];
			}
			
			if($actualFinish=='0000-00-00') $actualFinish = '-';
			
			$totalPrice = $totalItemPrice * $itemQuantity;
			
			$priceInFormat = ($totalItemPrice > 0) ? number_format($totalItemPrice, 4, '.', ',') : '';
			$totalPriceInFormat = ($totalPrice > 0) ? number_format(($totalPrice), 2, '.', ',') : '';
			
			if($prevPoNumber!=$poNumber)
			{
				$no = 0;
				$prevPoNumber = $poNumber;
			}
			
			$tableContent .= "
				<tr>
					<td>".++$count."</td>
					<td>".$poNumber."</td>
					<td>".++$no."</td>
					<td>".$supplierAlias."</td>
					<td>".$supplierName."</td>
					<td>".$issueDate."</td>
					<td>".$receivingDate."</td>
					<td>".$itemName."</td>
					<td>".$itemDescription."</td>
					<td>".$itemQuantity."</td>
					<td>".$sign."</td>
					<td>".$priceInFormat."</td>
					<td>".$totalPriceInFormat."</td>
					<td>".$status."</td>
					<td>".$actualFinish."</td>
					<td>".$lotNumber."</td>
				</tr>
			";			
			// $tableContent .= "
			// 	<tr>
			// 		<td>".++$count."</td>
			// 		<td>".$poNumber."</td>
			// 		<td>".$supplierAlias."</td>
			// 		<td>".$issueDate."</td>
			// 		<td>".$receivingDate."</td>
			// 		<td>".$itemName."</td>
			// 		<td>".$itemDescription."</td>
			// 		<td>".$itemQuantity."</td>
			// 		<td>".$sign."</td>
			// 		<td>".$priceInFormat."</td>
			// 		<td>".$totalPriceInFormat."</td>
			// 		<td>".$status."</td>
			// 		<td>".$lotNumber."</td>
			// 	</tr>
			// ";			
		}
	}
	
	echo $tableContent;
	echo "</tbody>";
	echo "</table>";	
	
?>
