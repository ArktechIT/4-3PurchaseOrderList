<?php
include $_SERVER['DOCUMENT_ROOT']."/version.php";
$path = $_SERVER['DOCUMENT_ROOT']."/".v."/Common Data/";
set_include_path($path);  
include('PHP Modules/mysqliConnection.php');
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=file.csv");
$ponumber = $_GET['poNumber'];
	//header('Content-Disposition: filename='.$drNumber.'.csv');
	
	$supplierType = '';
	$sql = "SELECT supplierType FROM purchasing_podetailsnew WHERE poNumber LIKE '".$ponumber."' LIMIT 1";
	$queryPodetailsNew = $db->query($sql);
	if($queryPodetailsNew AND $queryPodetailsNew->num_rows > 0)
	{
		$resultPodetailsNew = $queryPodetailsNew->fetch_assoc();
		$supplierType = $resultPodetailsNew['supplierType'];
	}
	
	if($supplierType==1)
	{
?>
PO #, Name, Description, Lot Number, Quantity, Unit Price, Amount, Receive Date
<?php 	
	
	$sql = "SELECT poNumber, itemName, itemDescription, lotNumber, itemQuantity, itemPrice, receivingDate FROM purchasing_pocontents WHERE poNumber = '".$ponumber."'"; 
	$queryCrud = $db->query($sql); 
	while($result = $queryCrud->fetch_assoc())
	{
		$poNumber = $result['poNumber'];
		$itemName = $result['itemName'];
		$itemDescription = $result['itemDescription'];
		$lotNumber = $result['lotNumber'];
		$itemQuantity = $result['itemQuantity'];
		$itemPrice = $result['itemPrice'];
		$receivingDate = $result['receivingDate'];
		
		echo '"'.$poNumber.'"'.",";
		echo '"'.$itemName.'"'.",";
		echo '"'.$itemDescription.'"'.",";
		echo '"'.$lotNumber.'"'.",";
		echo '"'.$itemQuantity.'"'.",";
		echo '"'.$itemPrice.'"'.",";
		$amount = $itemPrice * $itemQuantity;
		echo '"'.$amount.'"'.",";
		echo '"'.$receivingDate.'"'."\n";
	}	
	
	}
	else
	{
?>
PO #, Part Number, Lot Number, Quantity, Material Type, Subcon Process, Unit Price, Amount, Sending Date, Receive Date, Dm2
<?php 
	$sql = "SELECT poNumber, lotNumber, itemQuantity, dataThree, itemPrice, sendingDate, receivingDate FROM purchasing_pocontents WHERE poNumber = '".$ponumber."'"; 
	$queryCrud = $db->query($sql); 
	while($result = $queryCrud->fetch_assoc())
	{
		echo '"'.$poNumber = $result['poNumber'].'"'.",";

		$lotNumber = $result['lotNumber'];
		$sqlGetPartId = "SELECT partId FROM ppic_lotlist WHERE lotNumber = '".$lotNumber."'";
		$queryGetPartId = $db->query($sqlGetPartId);
		$resultGetPartId = $queryGetPartId->fetch_assoc();
		$partId = $resultGetPartId['partId'];

		$sqlGetPartNumber = "SELECT partNumber FROM cadcam_parts WHERE partId = '".$partId."'";
		$queryGetPartNumber = $db->query($sqlGetPartNumber);
		$resultGetPartId = $queryGetPartNumber->fetch_assoc();
		$partNumber = $resultGetPartId['partNumber'];

		echo '"'.$partNumber.'"'.",";
		echo '"'.$lotNumber.'"'.",";
		$itemQuantity = $result['itemQuantity'];
		echo '"'.$itemQuantity.'"'.",";

		$sqlMaterialSpecId = "SELECT materialSpecId FROM cadcam_parts WHERE partId = '".$partId."'";
		$queryMaterialSpecId = $db->query($sqlMaterialSpecId);
		$resultMaterialSpecId = $queryMaterialSpecId->fetch_assoc();
		$materialSpecId = $resultMaterialSpecId['materialSpecId'];

		$sqlMaterialTypeId = "SELECT materialTypeId FROM cadcam_materialspecs WHERE materialSpecId = '".$materialSpecId."'";
		$queryMaterialTypeId = $db->query($sqlMaterialTypeId);
		$resultMaterialTypeId = $queryMaterialTypeId->fetch_assoc();
		$materialTypeId = $resultMaterialTypeId['materialTypeId'];

		$sqlMaterialType = "SELECT materialType FROM engineering_materialtype WHERE materialTypeId = '".$materialTypeId."'";
		$queryMaterialType = $db->query($sqlMaterialType);
		$resultMaterialType = $queryMaterialType->fetch_assoc();
		$materialType = $resultMaterialType['materialType'];

		echo '"'.$materialType.'"'.",";
		echo '"'.$result['dataThree'].'"'.",";
		$itemPrice = $result['itemPrice'];
		echo '"'.$itemPrice.'"'.",";

		$amount = $itemPrice * $itemQuantity;
		echo '"'.$amount.'"'.",";

		$sendingDate = $result['sendingDate'];
		echo '"'.$sendingDate.'"'.",";

		$receivingDate = $result['receivingDate'];
		echo '"'.$receivingDate.'"'.",";

		$sqlTreatmentId = "SELECT treatmentId FROM engineering_treatment WHERE treatmentName = '".$result['dataThree']."'";
		$queryTreatmentId = $db->query($sqlTreatmentId);
		$resultTreatmentId = $queryTreatmentId->fetch_assoc();

		$sqlDm2 = "SELECT surfaceArea FROM cadcam_subconlist WHERE partId = '".$partId."' and processCode = '".$resultTreatmentId['treatmentId']."'";
		$queryDm2 = $db->query($sqlDm2);
		$resultDm2 = $queryDm2->fetch_assoc();
		$Dm2 = $resultDm2['surfaceArea'];

		echo '"'.$Dm2.'"'.",";
	?> 
<?php	
	}
}
?>
