<?php
	include $_SERVER['DOCUMENT_ROOT']."/version.php";
	$path = $_SERVER['DOCUMENT_ROOT']."/".v."/Common Data/";
	set_include_path($path);
	include('PHP Modules/mysqliConnection.php');
	include('PHP Modules/anthony_retrieveText.php');
	ini_set("display_errors","on");
	
	$groupNo = $_POST['groupNo'];
	$sqlFilter = $_POST['sqlFilter'];
	$queryLimit = 50;
	$queryPosition = ($groupNo * $queryLimit);
	
	$count = $queryPosition;
	$sql = "SELECT poNumber, supplierId, supplierType, poTerms, poShipmentType, poIncharge, poIssueDate, poTargetReceiveDate, poType, poStatus, poCurrency, poInputDateTime FROM purchasing_podetailsnew ".$sqlFilter." ORDER BY poIssueDate DESC, poNumber DESC LIMIT ".$queryPosition.", ".$queryLimit;
	$sqlMain = $sql;
	$query = $db->query($sql);
	if($query->num_rows > 0)
	{
		//~ $tableContent = "<tr><td colspan='14'>".$sqlMain."</td></tr>";
		while($result = $query->fetch_array())
		{
			$poNumber = $result['poNumber'];
			$supplierId = $result['supplierId'];
			$supplierType = $result['supplierType'];
			$poTerms = $result['poTerms'];
			$poShipmentType = $result['poShipmentType'];
			$poIncharge = $result['poIncharge'];
			$poIssueDate = $result['poIssueDate'];
			$poTargetReceiveDate = $result['poTargetReceiveDate'];
			$poType = $result['poType'];
			$poStatus = $result['poStatus'];
			$poCurrency = $result['poCurrency'];
			$poInputDateTime = $result['poInputDateTime'];
			
			if($poType == 2 OR $poType == 5 OR $supplierType == 2)
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
			
			if($poCurrency == 1) 		$currency = 'Dollar';
			else if($poCurrency == 2) 	$currency = 'Peso';
			else if($poCurrency == 3) 	$currency = 'Yen';
			
			if($poShipmentType==1)		$shipping='Land';
			else if($poShipmentType==2)	$shipping='Air';
			else if($poShipmentType==3)	$shipping='Sea';
			
			if($poStatus==0)		$status = 'Ongoing';
			else if($poStatus==1)	$status = 'For Email';
			else if($poStatus==2)	$status = 'Canceled';
			else if($poStatus==3)	$status = 'Closed';
			else if($poStatus==4)	$status = 'Finished';
			
			if($poType==1)						$tinyBoxUrl = "openTinyBox('800','500','','','/".v."/Purchasing Management System/Material PO Management Software/gerald_materialPoConverter.php?poNumber=".$poNumber."&preview=1');";
			else if($poType==2 OR $poType==5)	$tinyBoxUrl = "openTinyBox('800','500','','','/".v."/Purchasing Management System/Subcon PO Management Software/gerald_subconPoConverter.php?poNumber=".$poNumber."&preview=1');";
			else if($poType==3)					$tinyBoxUrl = "openTinyBox('800','500','','','/".v."/Purchasing Management System/Supply PO Management Software/gerald_supplyPoConverter.php?poNumber=".$poNumber."&preview=1');";
			else if($poType==4)					$tinyBoxUrl = "openTinyBox('800','500','','','/".v."/Purchasing Management System/Accessory PO Management Software/gerald_accessoryPoConverter.php?poNumber=".$poNumber."&preview=1');";
			
			$emailButton = ($poStatus==1) ? "<a href='#'><img src='/".v."/Common Data/Templates/images/email.png' height='15' title='Send Email'></a>" : "";
			
			$cancelButton = '';
			if($poStatus!=3 AND $poStatus!=2 AND in_array($_SESSION['idNumber'],array('0346','0407')))
			{
				$cancelButton = "
					<form action='gerald_cancelPo.php' method='post' onsubmit=\" confirmCancel(event); \" id='cancelForm".$poNumber."'></form>
					<input type='hidden' name='poNumber' value='".$poNumber."' form='cancelForm".$poNumber."'>
					<input type='image' src='/".v."/Common Data/Templates/images/close1.png' height='15' title='Cancel PO' form='cancelForm".$poNumber."'>
				";
				
				$cancelButton = "<img onclick= \" openTinyBox('','','gerald_apiPOSummary.php?poType=".$poType."','type=cancelPO&poNumber=".$poNumber."') \" src='/".v."/Common Data/Templates/images/close1.png' height='15'>";
			}
			
			$sql = "SELECT poContentId FROM purchasing_pocontents WHERE poNumber LIKE '".$poNumber."' AND itemStatus != 0 LIMIT 1";
			$queryClosePoList = $db->query($sql);
			if($queryClosePoList->num_rows > 0)	$cancelButton = '';
			
			$itemTagButton = '';
			
			$pathFileName = $_SERVER['DOCUMENT_ROOT']."/".v."/Purchasing Management System/Purchase Order/".$poNumber."/".$poNumber.".pdf";
			if(file_exists($pathFileName))
			{
				//~ $tinyBoxUrl = "openTinyBox('800','500','','','/".v."/Purchasing Management System/Integrated PO Management Software/gerald_viewDocument.php?poNumber=".$poNumber."');";
				$tinyBoxUrl = "openTinyBox('800','500','','','/V2/0 Garbage Bin/Purchasing Management System/Integrated PO Management Software/gerald_viewDocument.php?poNumber=".$poNumber."');";
			}
			else
			{
				$itemTagButton = "<img onclick= \" openTinyBox('800','500','','','gerald_invoiceDetailsConverter.php?poNumber=".$poNumber."') \" src='/".v."/Common Data/Templates/images/view1.png' height='15'>";
				$tinyBoxUrl = "openTinyBox('800','500','','','/".v."/Purchasing Management System/Purchase Order Summary/gerald_poConverter.php?poNumber=".$poNumber."&preview=1');";
			}
			
			if($poInputDateTime!='0000-00-00 00:00:00')
			{
				//~ $itemTagButton = "<img onclick= \" openTinyBox('800','500','','','/".v."/4-9 Purchase Order Making Software/gerald_invoiceDetailsConverter.php?poNumber=".$poNumber."') \" src='/".v."/Common Data/Templates/images/view1.png' height='15'>";
				$itemTagButton = "<img onclick= \" openTinyBox('800','500','','','/".v."/4-9 Purchase Order Making Software/gerald_invoiceDetailsConverter.php?poNumber=".$poNumber."&choicesFlag=1') \" src='/".v."/Common Data/Templates/images/view1.png' height='15'>";
				$tinyBoxUrl = "openTinyBox('800','500','','','/".v."/4-9 Purchase Order Making Software/gerald_purchaseOrderConverter.php?poNumber=".$poNumber."');";
				
				if($_GET['country']==2)
				{
					$tinyBoxUrl = "openTinyBox('800','500','','','/".v."/4-9 Purchase Order Making Software/gerald_purchaseOrderConverterJapan.php?poNumber=".$poNumber."');";
				}
			}
			
			//~ $drawingButton = (($poType==2 OR $poType==5 OR $supplierType == 2) AND (($supplierId==11 OR $supplierId==13) AND $poStatus!=2)) ? "<a href='/".v."/Purchasing Management System/gerald_exportDrawing.php?poNumber=".$poNumber."'><img src='/".v."/Common Data/Templates/images/drawingIcon.png' height='15' title='Download Drawing'></a>" : "";
			$drawingButton = (($poType==2 OR $poType==5 OR $supplierType == 2) AND $poStatus!=2) ? "<a href='/".v."/Purchasing Management System/gerald_exportDrawing.php?poNumber=".$poNumber."'><img src='/".v."/Common Data/Templates/images/drawingIcon.png' height='15' title='Download Drawing'></a>" : "";//activate to all subcon by kim 2021-03-04
			$class = (($count%2)==0) ? "class='odd'" : "";
			
			$buttons = "";
			if($_GET['country']!=2)
			{
				$buttons = "
					".$itemTagButton."
					".$drawingButton."
					".$emailButton."
					".$cancelButton."
				";
			}
			else
			{
				$buttons = $itemTagButton;
			}

			// if ($_SESSION['idNumber'] == '0613') 
			// {
				$link = "<ul style='list-style: none; margin: 0px;'>
							<li style='display: inline-block'><span style='cursor:pointer;text-decoration:underline;color:blue;' onclick=\" ".$tinyBoxUrl." \">".$poNumber."</span></li>
							<li style='display: inline-block; float: right;'>
								<a href='darwin_purchaseOrderCSV.php?poNumber=".$poNumber."'><img src='/".v."/Common Data/Templates/images/excel.png' height='15' title='Download CSV'></a>
							</li>
						</ul>";
			// }
			// else
			// {
			// 	$link = "<span style='cursor:pointer;text-decoration:underline;color:blue;' onclick=\" ".$tinyBoxUrl." \">".$poNumber."</span>";
			// }
			
			$tableContent .= "
				<tr ".$class.">
					<td align = 'center' style='line-height:15px;'>".++$count."</td>
					<td>".$link."</td>
					<td>".$supplierAlias."</td>
					<td>".$currency."</td>
					<td>".$poTerms."</td> 
					<td>".$shipping."</td>
					<td>".$poIssueDate."</td>
					<td>".$poTargetReceiveDate."</td>
					<td>".$status."</td>
					<td align='center'>
						".$buttons."
					</td>
				</tr>
			
			";
		}
		echo $tableContent;
	}
					
?>
