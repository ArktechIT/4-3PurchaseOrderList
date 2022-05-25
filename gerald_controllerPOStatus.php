<?php
include $_SERVER['DOCUMENT_ROOT']."/version.php";
$path = $_SERVER['DOCUMENT_ROOT']."/".v."/Common Data/";
set_include_path($path);
include('PHP Modules/mysqliConnection.php');
include('PHP Modules/gerald_functions.php');
include('PHP Modules/anthony_retrieveText.php');
include("PHP Modules/anthony_wholeNumber.php");
include("PHP Modules/rose_prodfunctions.php");
ini_set("display_errors", "on");

if(isset($_REQUEST['ajaxType']))
{
	if($_REQUEST['ajaxType']=='dataTable')
	{
		$requestData= $_REQUEST;
		$requestData['start'] = (isset($requestData['start'])) ? $requestData['start'] : 0;
		$requestData['length'] = (isset($requestData['length'])) ? $requestData['length'] : 100;

		$poNumber = (isset($_POST['poNumber'])) ? $_POST['poNumber'] : '';

		$results = PurchaseOrder::poNumber($poNumber)->contents();
		$totalData = $totalFiltered = count($results);

		$counter = 0;
		$results = array_map(function($item) use(&$counter) {

			$item->counter = ++$counter;
			$item->description = $item->dataOne." ".$item->dataTwo;

			return $item;
		},$results);

		$json_data = array(
			"draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
			"recordsTotal"    => intval( $totalData ),  // total number of records
			"recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data"            => $results   // total data array
			);
		echo json_encode($json_data);  // send data as json format			
	}
	else if($_REQUEST['ajaxType']=='ezModal')
	{
		extract($_POST);//lotNumber,poContentIds,poContentId,poNumber
		
		$sql = "SELECT identifier, status FROM ppic_lotlist WHERE lotNumber LIKE '".$lotNumber."' LIMIT 1";
		$resultLotList = TableDB::fetch($sql);

		$sql = ($resultLotList->identifier == 1)
			? "SELECT lotNumber FROM ppic_lotlist WHERE poContentId LIKE '".$poContentIds."' AND poContentId !='' AND identifier = 1"
			: "SELECT lotNumber FROM ppic_lotlist WHERE poId = ".$poContentId." AND poId > 0 AND identifier = 4";
			
		$lotNumbers = TableDB::fetchAll($sql);
		$lotNumberArray = array_map(function($item){
			return $item->lotNumber;
		},$lotNumbers);

		if(count($lotNumberArray) > 0)
		{
			if($resultLotList->identifier == 1)
			{
				$sql = "
					SELECT 
						lotNumber,
						quantity,
						(SELECT issue FROM purchasing_drdetails WHERE purchasing_drdetails.drNumber = purchasing_drcustomer.drNumber) supplyDate
					FROM
						purchasing_drcustomer
					WHERE
						lotNumber IN('".implode("','",$lotNumberArray)."') AND
						updateStatus=2
					ORDER BY supplyDate
				";
				$resultDrDetails = TableDB::fetchAll($sql);
				$tBodySupplyArray = array_map(function($item){
					return "
						<td>{$item->lotNumber}</td>
						<td>{$item->supplyDate}</td>
						<td>{$item->quantity}</td>
					";
				},$resultDrDetails);
				$totalSupplyQuantity = array_reduce($resultDrDetails,function($sum,$item){
					return $sum += $item->quantity;
				});

				$tBodySupply = (count($tBodySupplyArray) > 0) ? implode("</tr><tr>",$tBodySupplyArray) : "";
			}

			$lotNoPart = explode("-",$lotNumber);
			$mainLot = $lotNoPart[0]."-".$lotNoPart[1]."-".$lotNoPart[2];

			$sql = "
				SELECT
					poContentIds,
					poNumber,
					lotNumber,
					unitPrice,
					itemQuantity,
					receiveDate,
					receiveQuantity,
					deductQuantity,
					creditQuantity,
					cutOffMonth,
					apvNumber,
					(SELECT issueDate FROM accounting_apvdetails WHERE accounting_apvdetails.apvNumber = accounting_payablesnew.apvNumber) apvIssue
				FROM
					accounting_payablesnew
				WHERE
					(lotNumber LIKE '".$mainLot."' OR lotNumber LIKE '".$mainLot."-%') AND
					payableStatus < 4 AND
					poNumber LIKE '".$poNumber."' AND
					poContentIds LIKE '".$poContentIds."' AND
					receiveQuantity > 0
			";
			$resultPayables = TableDB::fetchAll($sql);
			$tBodyReceiveArray = array_map(function($item){
				$sql = "
					SELECT
						checkVoucher, issueDate
					FROM
						accounting_checkvoucher
					WHERE
						(
							apvNumber LIKE '".$item->apvNumber."' OR
							(
								apvNumber = '' AND
								(
									multipleAPV like '".$item->apvNumber.",%' OR
									multipleAPV like '%,".$item->apvNumber.",%' OR
									multipleAPV like '%,".$item->apvNumber."'
								)
							)
						) AND status != 2
				";
				$resultCV = TableDB::fetch($sql);

				return "
					<td>{$item->lotNumber}</td>
					<td>{$item->receiveDate}</td>
					<td>{$item->receiveQuantity}</td>
					<td>{$item->deductQuantity}</td>
					<td>{$item->creditQuantity}</td>
					<td>{$item->cutOffMonth}</td>
					<td>{$item->apvNumber}</td>
					<td>{$item->apvIssue}</td>
					<td>{$resultCV->checkVoucher}</td>
					<td>{$resultCV->issueDate}</td>
				";
			},$resultPayables);

			$totalQuantityArray = array_reduce($resultPayables,function($sum,$item){
				$sum['totalReceiveQuantity'] += $item->receiveQuantity;
				$sum['totalDeductQuantity'] += $item->deductQuantity;
				$sum['totalCreditQuantity'] += $item->creditQuantity;
				return $sum;
			});

			$tBodyReceive = (count($tBodyReceiveArray) > 0) ? implode("</tr><tr>",$tBodyReceiveArray) : "";
			if(count($totalQuantityArray) > 0) extract($totalQuantityArray);
		}
	}
}