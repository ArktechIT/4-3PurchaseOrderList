<?php
include $_SERVER['DOCUMENT_ROOT']."/version.php";
$path = $_SERVER['DOCUMENT_ROOT']."/".v."/Common Data/";
set_include_path($path);    
include('PHP Modules/mysqliConnection.php');
include('PHP Modules/anthony_wholeNumber.php');
include('PHP Modules/anthony_retrieveText.php');
include('PHP Modules/gerald_functions.php');
ini_set("display_errors", "on");

$obj = new PMSDatabase;
$tpl = new PMSTemplates;

$requestData= $_REQUEST;
$sqlData = isset($requestData['sqlData']) ? $requestData['sqlData'] : '';
$totalRecords = isset($requestData['totalRecords']) ? $requestData['totalRecords'] : '';
$die = isset($requestData['die']) ? $requestData['die'] : '';

$totalData = $totalRecords;
$totalFiltered = $totalRecords;

$data = array();
$sql = $sqlData;
$sql.=" LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
$query = $db->query($sql);
$x = $requestData['start'];
if($query AND $query->num_rows > 0)
{
    while($result = $query->fetch_assoc()) 
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
        
        if($poCurrency == 1)        $currency = 'Dollar';
        else if($poCurrency == 2)   $currency = 'Peso';
        else if($poCurrency == 3)   $currency = 'Yen';
        
        if($poShipmentType==1)      $shipping='Land';
        else if($poShipmentType==2) $shipping='Air';
        else if($poShipmentType==3) $shipping='Sea';
        
        if($poStatus==0)        $status = 'Ongoing';
        else if($poStatus==1)   $status = 'For Email';
        else if($poStatus==2)   $status = 'Canceled';
        else if($poStatus==3)   $status = 'Closed';
        else if($poStatus==4)   $status = 'Finished';
        
        /*if($poType==1)                      $tinyBoxUrl = "openTinyBox('800','500','','','/".v."/Purchasing Management System/Material PO Management Software/gerald_materialPoConverter.php?poNumber=".$poNumber."&preview=1');";
        else if($poType==2 OR $poType==5)   $tinyBoxUrl = "openTinyBox('800','500','','','/".v."/Purchasing Management System/Subcon PO Management Software/gerald_subconPoConverter.php?poNumber=".$poNumber."&preview=1');";
        else if($poType==3)                 $tinyBoxUrl = "openTinyBox('800','500','','','/".v."/Purchasing Management System/Supply PO Management Software/gerald_supplyPoConverter.php?poNumber=".$poNumber."&preview=1');";
        else if($poType==4)                 $tinyBoxUrl = "openTinyBox('800','500','','','/".v."/Purchasing Management System/Accessory PO Management Software/gerald_accessoryPoConverter.php?poNumber=".$poNumber."&preview=1');";*/

        if($poType==1)                     $modalUrl = "/".v."/Purchasing Management System/Material PO Management Software/gerald_materialPoConverter.php?poNumber=".$poNumber."&preview=1";
        else if($poType==2 OR $poType==5)  $modalUrl = "/".v."/Purchasing Management System/Subcon PO Management Software/gerald_subconPoConverter.php?poNumber=".$poNumber."&preview=1";
        else if($poType==3)                $modalUrl = "/".v."/Purchasing Management System/Supply PO Management Software/gerald_supplyPoConverter.php?poNumber=".$poNumber."&preview=1";
        else if($poType==4)                $modalUrl = "/".v."/Purchasing Management System/Accessory PO Management Software/gerald_accessoryPoConverter.php?poNumber=".$poNumber."&preview=1";

        $emailButton = ($poStatus==1) ? "<a href='#'><img src='/".v."/Common Data/Templates/images/email.png' height='15' title='Send Email'></a>" : "";
            
        $cancelButton = '';
        if($poStatus!=3 AND $poStatus!=2 AND in_array($_SESSION['idNumber'],array('0346','0407')))
        {
            $cancelButton = "
                <form action='gerald_cancelPo.php' method='post' onsubmit=\" confirmCancel(event); \" id='cancelForm".$poNumber."'></form>
                <input type='hidden' name='poNumber' value='".$poNumber."' form='cancelForm".$poNumber."'>
                <input type='image' src='/".v."/Common Data/Templates/images/close1.png' height='15' title='Cancel PO' form='cancelForm".$poNumber."'>
            ";
            
            /*$cancelButton = "<img onclick= \" openTinyBox('','','gerald_apiPOSummary.php?poType=".$poType."','type=cancelPO&poNumber=".$poNumber."') \" src='/".v."/Common Data/Templates/images/close1.png' height='15'>";*/
            $cancelButton = "'gerald_apiPOSummary.php?poType=".$poType."','type=cancelPO&poNumber=".$poNumber."'";

            $tpl->setDataValue("L609"); // Delete
            $tpl->setAttribute("type","button");
            $tpl->setAttribute("onclick", "deleteData('".$result['poNumber']."');");
            $cancelButton = $tpl->createButton(1);
        }
        
        $sql = "SELECT poContentId FROM purchasing_pocontents WHERE poNumber LIKE '".$poNumber."' AND itemStatus != 0 LIMIT 1";
        $queryClosePoList = $db->query($sql);
        if($queryClosePoList->num_rows > 0) $cancelButton = '';
        
        $itemTagButton = '';
        
        $pathFileName = $_SERVER['DOCUMENT_ROOT']."/".v."/Purchasing Management System/Purchase Order/".$poNumber."/".$poNumber.".pdf";
        if(file_exists($pathFileName))
        {
            //$tinyBoxUrl = "openTinyBox('800','500','','','/V2/0 Garbage Bin/Purchasing Management System/Integrated PO Management Software/gerald_viewDocument.php?poNumber=".$poNumber."');";
            $modalUrl = "/V2/0 Garbage Bin/Purchasing Management System/Integrated PO Management Software/gerald_viewDocument.php?poNumber=".$poNumber."";
        }
        else
        {
            /*$itemTagButton = "<img onclick= \" openTinyBox('800','500','','','gerald_invoiceDetailsConverter.php?poNumber=".$poNumber."') \" src='/".v."/Common Data/Templates/images/view1.png' height='15'>";*/
            $viewData = "gerald_invoiceDetailsConverter.php?poNumber=".$poNumber;

            $tpl->setDataValue("L188"); // View
            $tpl->setAttribute("type","button");
            $tpl->setAttribute("onclick", "viewData('".$viewData."')");
            $itemTagButton = $tpl->createButton(1);

            //$tinyBoxUrl = "openTinyBox('800','500','','','/".v."/Purchasing Management System/Purchase Order Summary/gerald_poConverter.php?poNumber=".$poNumber."&preview=1');";
            $modalURL = "/".v."/Purchasing Management System/Purchase Order Summary/gerald_poConverter.php?poNumber=".$poNumber."&preview=1";
        }
            
        if($poInputDateTime!='0000-00-00 00:00:00')
        {
            /*$itemTagButton = "<img onclick= \" openTinyBox('800','500','','','/".v."/4-9 Purchase Order Making Software/gerald_invoiceDetailsConverter.php?poNumber=".$poNumber."&choicesFlag=1') \" src='/".v."/Common Data/Templates/images/view1.png' height='15'>";*/
            $viewData = "/".v."/4-9 Purchase Order Making Software/gerald_invoiceDetailsConverter.php?poNumber=".$poNumber."&choicesFlag=1";

            $tpl->setDataValue("L188"); // View
            $tpl->setAttribute("type","button");
            $tpl->setAttribute("onclick", "viewData('".$viewData."')");
            $itemTagButton = $tpl->createButton(1);
            
            if(strtotime($poInputDateTime) >= strtotime('2021-03-25 12:12:00'))
            {
				$modalURL = "/".v."/4-9 Purchase Order Making Software/gerald_purchaseOrderConverterV2.php?poNumber=".$poNumber."";
			}
			else
			{
				//$tinyBoxUrl = "openTinyBox('800','500','','','/".v."/4-9 Purchase Order Making Software/gerald_purchaseOrderConverter.php?poNumber=".$poNumber."');";
				$modalURL = "/".v."/4-9 Purchase Order Making Software/gerald_purchaseOrderConverter.php?poNumber=".$poNumber."";
			}
            
            if($poNumber=='0015707')
            {
				$modalURL = "/".v."/4-9 Purchase Order Making Software/gerald_purchaseOrderConverterV2.php?poNumber=".$poNumber."";
			}
            
            if($_GET['country']==2)
            {
                //$tinyBoxUrl = "openTinyBox('800','500','','','/".v."/4-9 Purchase Order Making Software/gerald_purchaseOrderConverterJapan.php?poNumber=".$poNumber."');";
                $modalURL = "/".v."/4-9 Purchase Order Making Software/gerald_purchaseOrderConverterJapan.php?poNumber=".$poNumber;
				if($poInputDateTime>'2020-07-30 00:00:00')
				{
					//$tinyBoxUrl = "openTinyBox('800','500','','','/V3/4-9 Purchase Order Making Software/V2.1/rose_purchaseOrderConverterJapan.php?poNumber=".$poNumber."');";
					$modalURL = "/".v."/4-9 Purchase Order Making Software/V2.1/rose_purchaseOrderConverterJapan.php?poNumber=".$poNumber;
				}                
            }
        }

        $tpl->setDataValue("L1202"); // Download
        $tpl->setAttribute("type","button");
        $drawingButton = $tpl->createButton(1);

        $drawingButton = (($poType==2 OR $poType==5 OR $supplierType == 2) AND (($supplierId==11 OR $supplierId==13) AND $poStatus!=2)) ? "<a href='/".v."/Purchasing Management System/gerald_exportDrawing.php?poNumber=".$poNumber."'>".$drawingButton."</a>" : "";

        
        /*$drawingButton = '';
        if (($poType == 2 OR $poType == 5 OR $supplierType == 2) AND ($supplierId == 11 OR $supplierId == 13) AND $poStatus !=2) {
            $drawingButton = "../Purchasing Management System/gerald_exportDrawing.php?poNumber=".$poNumber."";
            $class = (($count%2)==0) ? "class='odd'" : "";
            
            $tpl->setDataValue("L1202"); // Download
            $tpl->setAttribute("type","button");
            $tpl->setAttribute("onclick", "downloadData('".$drawingButton."')");
            $drawingButton = $tpl->createButton(1);
        }*/
        
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

        $link = "<ul style='list-style: none; margin: 0px;'>
                    <li style='display: inline-block'><span style='cursor:pointer;text-decoration:underline;color:blue;' onclick=\"viewPDF('".$modalURL."')\">".$poNumber."</span></li>
                    <li style='display: inline-block; float: right;'>
                        <a href='darwin_purchaseOrderCSV.php?poNumber=".$poNumber."'><img src='/".v."/Common Data/Templates/images/excel.png' height='15' title='Download CSV'></a>
                    </li>
                </ul>";

        $nestedData=array(); 
		$nestedData[] = ++$x;
		$nestedData[] = $link;
        $nestedData[] = $supplierAlias;
        $nestedData[] = $currency;
        $nestedData[] = $poTerms;
        $nestedData[] = $shipping;
        $nestedData[] = $poIssueDate;
        $nestedData[] = $poTargetReceiveDate;
        $nestedData[] = $status;
        $nestedData[] = $buttons;
        //$nestedData[] = $emailButton2." ".$itemTagButton2." ".$cancelButton2." ".$drawingButton2;
		$data[] = $nestedData;
	}
}

$json_data = array(
            "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
            "recordsTotal"    => intval( $totalData ),  // total number of records
            "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
            );

echo json_encode($json_data);  // send data as json format
?>

