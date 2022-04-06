<?php
	include $_SERVER['DOCUMENT_ROOT']."/version.php";
	$path = $_SERVER['DOCUMENT_ROOT']."/".v."/Common Data/";
	set_include_path($path);
	include('PHP Modules/mysqliConnection.php');
	include('PHP Modules/anthony_retrieveText.php');
	
	// if($_SESSION['idNumber']!='0346')
	// {
		// $filename = "API PO Summary (".date('Y-m-d').").xls";
		// header('Content-type: application/ms-excel');
		// header('Content-Disposition: attachment; filename='.$filename);
	// }

	$sqlFilter = $_POST['sqlFilter'];
		
	$totalRecords = 0;
	$sql = "SELECT poNumber, supplierId, supplierType, poIssueDate, poTargetReceiveDate, poCurrency, poInputDateTime FROM purchasing_podetailsnew ".$sqlFilter."";
	$tableContent = '';
	$poNumberArray = $supplierAliasArray = $poIssueDateArray = $poTargetReceiveDateArray = $poInputDateTimeArray = array();
	// ----------------------------------------------------- Execute Query --------------------------------------------------
	$query = $db->query($sql);
	if($query->num_rows > 0)
	{
		while($result = $query->fetch_array())
		{
			$poNumber = $result['poNumber'];
			$supplierId = $result['supplierId'];
			$supplierType = $result['supplierType'];
			$poIssueDate = $result['poIssueDate'];
			$poTargetReceiveDate = $result['poTargetReceiveDate'];
			$poCurrency = $result['poCurrency'];
			$poInputDateTime = $result['poInputDateTime'];
			
			//~ if($poType == 2 OR $poType == 5)
			if($supplierType == 2)
			{
				$supplierAlias = '';
				$sql = "SELECT subconAlias FROM purchasing_subcon WHERE subconId = ".$supplierId." LIMIT 1";
				$querySubcon = $db->query($sql);
				if($querySubcon->num_rows > 0)
				{
					$resultSubcon = $querySubcon->fetch_array();
					$supplierAlias = $resultSubcon['subconAlias'];
				}
			}
			else
			{
				$supplierAlias = '';
				$sql = "SELECT supplierAlias FROM purchasing_supplier WHERE supplierId = ".$supplierId." LIMIT 1";
				$querySupplier = $db->query($sql);
				if($querySupplier->num_rows > 0)
				{
					$resultSupplier = $querySupplier->fetch_array();
					$supplierAlias = $resultSupplier['supplierAlias'];
				}
			}
			
			$poNumberArray[] = "'".$poNumber."'";
			$supplierAliasArray[$poNumber] = $supplierAlias;
			$poIssueDateArray[$poNumber] = $poIssueDate;
			$poTargetReceiveDateArray[$poNumber] = $poTargetReceiveDate;
			$poCurrencyArray[$poNumber] = $poCurrency;
			$poInputDateTimeArray[$poNumber] = $poInputDateTime;
		}
	}
	
	$count = 0;
	//~ if($poType == 2 OR $poType == 5)
	if($supplierType==2)
	{
		echo "
		<table cellpadding=0 cellspacing=0 border='1'>
			<thead>
				<tr>
					<th></th>
					<th>".displayText('L224')."</th>
					<th>".displayText('L367')."</th>
					<th>".displayText('L342')."</th>
					<th>".displayText('L3151')."</th>
					<th>".displayText('L28')."</th>
					<th>".displayText('L30')."</th>
					<th>".displayText('L1934')."</th>
					<th>".displayText('L45')."</th>
					<th>".displayText('L31')."</th>
					<th>".displayText('L112')."</th>
					<th>".displayText('L32')."</th>
					<th>".displayText('L743')."</th>
				</tr>
			</thead>
		";
		echo "<tbody>";
		$customerIdArray = $quantityArray = $totalPriceArray = array();
		$sql = "SELECT DISTINCT lotNumber, itemQuantity, poNumber FROM purchasing_pocontents WHERE poNumber IN(".implode(",",$poNumberArray).")";
		if($exportType=='exportOpen')	$sql = "SELECT DISTINCT lotNumber, itemQuantity, poNumber FROM purchasing_pocontents WHERE poNumber IN(".implode(",",$poNumberArray).") AND itemStatus = 0";
		$queryLotNo = $db->query($sql);
		if($queryLotNo->num_rows > 0)
		{
			while($resultLotNo = $queryLotNo->fetch_array())
			{
				$poNumber = $resultLotNo['poNumber'];
				$lotNumber = $resultLotNo['lotNumber'];
				$poContentQuantity = $resultLotNo['itemQuantity'];
				
				$supplierAlias = $supplierAliasArray[$poNumber];
				$poIssueDate = $poIssueDateArray[$poNumber];
				$poTargetReceiveDate = $poTargetReceiveDateArray[$poNumber];
				$poCurrency = $poCurrencyArray[$poNumber];
				$poInputDateTime = $poInputDateTimeArray[$poNumber];
				
				if($poCurrency==1)		$sign = 'Dollar';
				else if($poCurrency==2)	$sign = 'Peso';
				else if($poCurrency==3)	$sign = 'Yen';				
				
				$partId = $identifier = '';
				$sql = "SELECT partId, identifier FROM ppic_lotlist WHERE lotNumber LIKE '".$lotNumber."' LIMIT 1";
				$queryLotList = $db->query($sql);
				if($queryLotList->num_rows > 0)
				{
					$resultLotList = $queryLotList->fetch_array();
					$partId = $resultLotList['partId'];
					$identifier = $resultLotList['identifier'];
				}
				
				$partNumber = $partName = $revisionId = $customerId = '';
				$sql = "SELECT partNumber, partName, revisionId, customerId FROM cadcam_parts WHERE partId = ".$partId." LIMIT 1";
				$queryParts = $db->query($sql);
				if($queryParts->num_rows > 0)
				{
					$resultParts = $queryParts->fetch_array();
					$partNumber = $resultParts['partNumber'];
					$partName = $resultParts['partName'];
					$revisionId = $resultParts['revisionId'];
					$customerId = $resultParts['customerId'];
				}
				
				$totalSurfaceClear = 0;
				if($identifier==1)
				{
				
					
					$sql = "SELECT surfaceArea FROM cadcam_subconlist WHERE partId = ".$partId." AND processCode = 270 LIMIT 1";//Anodize
					$querySubconList = $db->query($sql);
					if($querySubconList->num_rows > 0)
					{
						$resultSubconList = $querySubconList->fetch_array();
						$totalSurfaceClear = $resultSubconList['surfaceArea'];
					}
				}
				else
				{
					$customerId = '';
				}
				
				$packingCost = 0;
				$poContentPrice = 0;
				$treatmentName = '';
				$sql = "SELECT productId, itemPrice FROM purchasing_pocontents WHERE poNumber LIKE '".$poNumber."' AND lotNumber LIKE '".$lotNumber."'";
				if($exportType=='exportOpen')	$sql = "SELECT productId, itemPrice FROM purchasing_pocontents WHERE poNumber LIKE '".$poNumber."' AND lotNumber LIKE '".$lotNumber."' AND itemStatus = 0";
				$queryPoContent = $db->query($sql);
				if($queryPoContent->num_rows > 0)
				{
					while($resultPoContent = $queryPoContent->fetch_array())
					{
						$listId = $resultPoContent['productId'];
						if($poInputDateTime=='0000-00-00 00:00:00')
						{
							$poContentPrice += $resultPoContent['itemPrice'];
						}
						else
						{
							$itemPrice = round($resultPoContent['itemPrice'],4);
							$poContentPrice += $itemPrice;
							$totalAmount += round($itemPrice * $poContentQuantity,2);
						}
						
						$resultPoContent['itemPrice'];
						
						$treatmentId = '';
						$sql = "SELECT supplyId, supplyType FROM purchasing_supplies WHERE listId = ".$listId." LIMIT 1";
						$queryTreatmentId = $db->query($sql);
						if($queryTreatmentId->num_rows > 0)
						{
							$resultTreatmentId = $queryTreatmentId->fetch_array();
							$treatmentId = $resultTreatmentId['supplyId'];
							$supplyType = $resultTreatmentId['supplyType'];
							
							if($supplyType==5)
							{
								$sql = "SELECT processCode FROM cadcam_subconlist WHERE a = ".$treatmentId." LIMIT 1";
								$queryProcessCode = $db->query($sql);
								if($queryProcessCode->num_rows > 0)
								{
									$resultProcessCode = $queryProcessCode->fetch_array();
									$treatmentId = $resultProcessCode['processCode'];
								}
							}
						}
						
						if($supplyType == 2 AND $supplierAlias == 'KAPCO')
						{
							$packingCost = (in_array($treatmentId,array(270,272))) ? ($totalSurfaceClear * 2) * 0.0031 : 0 ;
						}
					}
					//~ $treatmentName = implode(", ",$treatmentNameArray);
				}
				
				if($poInputDateTime=='0000-00-00 00:00:00')
				{
					$poContentPrice += $packingCost;
					//~ $amount = $poContentQuantity * $poContentPrice;
					$amount = round($poContentPrice,4) * $poContentQuantity;
					
					$totalAmount += round($amount,2);
				}
				
				if($customerId!='')
				{
					if(!isset($quantityArray[$customerId]))	$quantityArray[$customerId] = 0;
					if(!isset($totalPriceArray[$customerId]))	$totalPriceArray[$customerId] = 0;
					
					$quantityArray[$customerId] += $poContentQuantity;
					$totalPriceArray[$customerId] += round($amount,2);
					
					if(!in_array($customerId,$customerIdArray))
					{
						$customerIdArray[] = $customerId;
					}
				}
				
				$tableContent .= "
					<tr>
						<td>".++$count."</td>
						<td>".$poNumber."</td>
						<td>".$supplierAlias."</td>
						<td>".$poIssueDate."</td>
						<td>".$poTargetReceiveDate."</td>
						<td>".$partNumber."</td>
						<td>".$partName."</td>
						<td>".$revisionId."</td>
						<td>".$lotNumber."</td>
						<td>".$poContentQuantity."</td>
						<td>".$sign."</td>
						<td>".number_format($poContentPrice, 4, '.', ',')."</td>
						<td>".number_format(($poContentPrice * $poContentQuantity), 2, '.', ',')."</td>
					</tr>
				";
			}
		}
	}
	else
	{
		echo "
		<table cellpadding=0 cellspacing=0 border='1'>
			<thead>
				<tr>
					<th></th>
					<th>PO<br>".displayText('L855')."</th>
					<th>".displayText('L367')."</th>
					<th>".displayText('L342')."</th>
					<th>".displayText('L3151')."</th>
					<th>".displayText('L246')."</th>
					<th>".displayText('L247')."</th>
					<th>t</th>
					<th>l</th>
					<th>w</th>
					<th>".displayText('L31')."</th>
					<th>".displayText('L112')."</th>
					<th>".displayText('L32')."</th>
					<th>".displayText('L743')."</th>
				</tr>
			</thead>
		";
		echo "<tbody>";
		
		$sql = "SELECT poContentId, poNumber, lotNumber, productId, itemQuantity, itemUnit, itemPrice, itemFlag FROM purchasing_pocontents WHERE poNumber IN(".implode(",",$poNumberArray).")";
		if($exportType=='exportOpen')	$sql = "SELECT poContentId, poNumber, lotNumber, productId, itemQuantity, itemUnit, itemPrice, itemFlag FROM purchasing_pocontents WHERE poNumber IN(".implode(",",$poNumberArray).") AND itemStatus = 0";
		$querySupplyPo = $db->query($sql);
		if($querySupplyPo->num_rows > 0)
		{
			while($resultSupplyPo = $querySupplyPo->fetch_array())
			{
				$poContentId = $resultSupplyPo['poContentId'];
				$poNumber = $resultSupplyPo['poNumber'];
				$lotNumber = $resultSupplyPo['lotNumber'];
				$listId = $resultSupplyPo['productId'];
				$qty = $resultSupplyPo['itemQuantity'];
				$poContentUnit = $resultSupplyPo['itemUnit'];
				$poContentPrice = $resultSupplyPo['itemPrice'];
				$poContentFlag = $resultSupplyPo['itemFlag'];
				$price = $poContentPrice;
				
				$supplierAlias = $supplierAliasArray[$poNumber];
				$poIssueDate = $poIssueDateArray[$poNumber];
				$poTargetReceiveDate = $poTargetReceiveDateArray[$poNumber];
				$poCurrency = $poCurrencyArray[$poNumber];
				$poInputDateTime = $poInputDateTimeArray[$poNumber];
				
				if($poCurrency==1)		$sign = 'Dollar';
				else if($poCurrency==2)	$sign = 'Peso';
				else if($poCurrency==3)	$sign = 'Yen';
				
				$supplyId = '';
				$sql = "SELECT supplyId FROM purchasing_supplies WHERE listId = ".$listId." LIMIT 1";
				$querySupplyId = $db->query($sql);
				if($querySupplyId->num_rows > 0)
				{
					$resultSupplyId = $querySupplyId->fetch_array();
					$supplyId = $resultSupplyId['supplyId'];
				}
				
				$poType = '';
				$sql = "SELECT status FROM ppic_lotlist WHERE lotNumber LIKE '".$lotNumber."' LIMIT 1";
				$queryLotList = $db->query($sql);
				if($queryLotList->num_rows > 0)
				{
					$resultLotList = $queryLotList->fetch_array();
					$poType = $resultLotList['status'];
				}				
				
				
				$content = '';
				if($poType==1)
				{
					$supplyUnit = $poContentUnit;
					
					$pvc = ($poContentFlag==1) ? 'w/PVC' : '';
					
					$materialId = '';
					$sql = "SELECT materialId FROM purchasing_materialtreatment WHERE materialTreatmentId = ".$supplyId." LIMIT 1";
					$queryMaterialTreatment = $db->query($sql);
					if($queryMaterialTreatment AND $queryMaterialTreatment->num_rows > 0)
					{
						$resultMaterialTreatment = $queryMaterialTreatment->fetch_assoc();
						$materialId = $resultMaterialTreatment['materialId'];
					}
					
					$sql = "SELECT materialTypeId, thickness, length, width FROM purchasing_material WHERE materialId = ".$materialId." LIMIT 1";
					//~ $sql = "SELECT materialTypeId, thickness, length, width FROM purchasing_material WHERE materialId = ".$supplyId." LIMIT 1";
					$queryMaterial = $db->query($sql);
					if($queryMaterial->num_rows > 0)
					{
						$resultMaterial = $queryMaterial->fetch_array();
						$materialTypeId = $resultMaterial['materialTypeId'];
						$thickness = $resultMaterial['thickness'];
						$length = $resultMaterial['length'];
						$width = $resultMaterial['width'];
					}
					
					$materialType = '';
					$sql = "SELECT materialType, baseWeight, coatingWeight  FROM purchasing_materialtype WHERE suppliermaterialID = ".$materialTypeId." LIMIT 1";
					$queryMaterialType = $db->query($sql);
					if($queryMaterialType->num_rows > 0)
					{
						$resultMaterialType = $queryMaterialType->fetch_array();
						$materialType = $resultMaterialType['materialType'];
						$baseWeight = $resultMaterialType['baseWeight'];
						$coatingWeight = $resultMaterialType['coatingWeight'];
					}
					
					if($supplyUnit==2)
					{
						if($pvc=='w/PVC')
						{
							$price += ($supplierId==682) ? 0.10 : 0.15 ; //682 supplierId of Toyota Tsusho
						}

						$var1 = $var2 = $var3 = 1;
						//~ if($baseWeight!=0 AND $coatingWeight!=0)
						if($baseWeight!=0)
						{
							$var1 = (($baseWeight*$thickness)+$coatingWeight);
							$var2 = ($length/1000);
							$var3 = ($width/1000);
						}
						
						if($supplierId==3)//Mm Steel
						{
							$var1 = round($var1,4);
							$var2 = round(($length * $width) / 1000000,4);
							$ans1 = ($var1*$var2);
							
							$ans1 = (string)$ans1;
							$decimalPlaces = 0;
							$i = 0;
							$first3Significant = '';
							$finalAns = '';
							while(strlen($first3Significant) < 4)
							{
								if(strstr($finalAns,'.')) $decimalPlaces++;
								if($ans1[$i] == '0' AND $i == 0)
								{
									$finalAns .= $ans1[$i];
								}
								else
								{
									if($ans1[$i]!='.')
									{
										$first3Significant .= $ans1[$i];
									}
									$finalAns .= $ans1[$i];
								}
								$i++;
								
								if($i > strlen($ans1))	break;
							}
							$ans1 = round($finalAns,($decimalPlaces - 1));
						}
						else
						{
							$ans1 = ($var1*$var2*$var3);
						}
						
						$unitPrice = ($ans1*$price);
						
						$unitName = 'sheets';
					}
					else if($supplyUnit==4 OR $supplyUnit==1)
					{
						$unitPrice = $price;
					}
					
					$content = $thickness." x ".$length." x ".$width." ".$pvc;
					
					if($supplyId==65)
					{
						$content = "dia ".$thickness." x ".$length." ".$pvc;
					}
					
					if($poInputDateTime=='0000-00-00 00:00:00')
					{
						$totalPrice = (round($unitPrice,4) * $qty);
						
						$totalAmount += round($totalPrice,2);
					}
					else
					{
						$unitPrice = $poContentPrice;
						
						$totalPrice = (round($unitPrice,4) * $qty);
						
						$totalAmount += round($totalPrice,2);
					}
				}
				else if($poType==3)
				{
					$unitPrice = $price;
					
					$totalPrice = round($price,4) * $qty;
					
					$totalAmount += round($totalPrice,2);
					
					$itemName = $itemDescription = '';
					$sql = "SELECT itemName, itemDescription FROM purchasing_items WHERE itemId = ".$supplyId." LIMIT 1";
					$queryItems = $db->query($sql);
					if($queryItems->num_rows > 0)
					{
						$resultItems = $queryItems->fetch_array();
						$itemName = $resultItems['itemName'];
						$itemDescription = $resultItems['itemDescription'];
					}
					$content = $itemName;
				}
				else if($poType==4)
				{
					$unitPrice = $price;
					
					$totalPrice = round($price,4) * $qty;
					
					$totalAmount += round($totalPrice,2);
					
					$accessoryNumber = $itemDescription = '';
					$sql = "SELECT accessoryNumber, accessoryName FROM cadcam_accessories WHERE accessoryId = ".$supplyId." LIMIT 1";
					$queryAccessories = $db->query($sql);
					if($queryAccessories->num_rows > 0)
					{
						$resultAccessories = $queryAccessories->fetch_array();
						$accessoryNumber = $resultAccessories['accessoryNumber'];
						$itemDescription = $resultAccessories['accessoryName'];
					}
					
					// ----- Remove accessoryNumber requested by Sir Demer 2016-06-23 ----- //
					if($supplyId==1575)
					{
						$accessoryNumber = $itemDescription;
						$itemDescription = '-';
					}
					// ----- Remove accessoryNumber requested by Sir Demer 2016-06-23 ----- //
					
					$content = $accessoryNumber;
				}
				
				$priceInFormat = ($price > 0) ? number_format($unitPrice, 4, '.', ',') : '';
				$totalPriceInFormat = ($totalPrice > 0) ? number_format(($totalPrice), 2, '.', ',') : '';				
				
				//~ $amount = $poContentQuantity * $poContentPrice;
				//~ $amount = round($poContentPrice,4) * $poContentQuantity;
				
				//~ $totalAmount += round($amount,2);
				
				$tableContent .= "
					<tr>
						<td>".++$count."</td>
						<td>".$poNumber."</td>
						<td>".$supplierAlias."</td>
						<td>".$poIssueDate."</td>
						<td>".$poTargetReceiveDate."</td>";
						
						if($poType==1)
						{
							$tableContent .= "
								<td>".$materialType."</td>
								<td>".$content."</td>
								<td>".$thickness."</td>
								<td>".$length."</td>
								<td>".$width."</td>
							";
						}
						else
						{
							$tableContent .= "
								<td>".$content."</td>
								<td>".$itemDescription."</td>
							";
						}
						
				$tableContent .= "
						<td>".$qty."</td>
						<td>".$sign."</td>
						<td>".$priceInFormat."</td>
						<td>".$totalPriceInFormat."</td>
					</tr>
				";
			}
		}
	}
	
	echo $tableContent;
	echo "</tbody>";
	echo "</table>";	
	
	if($_SESSION['idNumber']=='0346')
	{
		echo $totalAmount;
		
		if(count($customerIdArray) > 0)
		{
			foreach($customerIdArray as $customerId)
			{
				$quantity = $quantityArray[$customerId];
				$totalPrice = $totalPriceArray[$customerId];
				
				$customerAlias = '';
				$sql = "SELECT customerAlias FROM sales_customer WHERE customerId = ".$customerId." LIMIT 1";
				$queryCustomer = $db->query($sql);
				if($queryCustomer AND $queryCustomer->num_rows > 0)
				{
					$resultCustomer = $queryCustomer->fetch_assoc();
					$customerAlias = $resultCustomer['customerAlias'];
				}
				
				echo "<hr>".$customerAlias;
				echo "<br>".$quantity;
				echo "<br>".$totalPrice;
			}
		}
	}
?>
