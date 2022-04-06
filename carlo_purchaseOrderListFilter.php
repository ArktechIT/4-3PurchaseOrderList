<?php
include $_SERVER['DOCUMENT_ROOT']."/version.php";
$path = $_SERVER['DOCUMENT_ROOT']."/".v."/Common Data/";
set_include_path($path);    
include('PHP Modules/mysqliConnection.php');
include('PHP Modules/anthony_wholeNumber.php');
include('PHP Modules/anthony_retrieveText.php');
include('PHP Modules/gerald_functions.php');
ini_set("display_errors", "on");

$tpl = new PMSTemplates;

$tpl->setDataValue("B5"); // Search
$tpl->setAttribute("type","submit");
$tpl->setAttribute("form","formFilter");
$buttonSearch = $tpl->createButton();

$sqlData = (isset($_POST['sqlData'])) ? $_POST['sqlData'] : '';
$postVariable = (isset($_POST['postVariable'])) ? $_POST['postVariable'] : '';
if($postVariable!='')
{
    $postVariable = str_replace("'",'"',$postVariable);
    $_POST = json_decode($postVariable,true);
}

$fromSql = strstr($sqlData,'FROM');

$dateFrom = (isset($_POST['dateFrom'])) ? $_POST['dateFrom'] : '';
$dateTo = (isset($_POST['dateTo'])) ? $_POST['dateTo'] : '';
$poNumber = (isset($_POST['poNumber'])) ? $_POST['poNumber'] : '';
$supplierAlias = (isset($_POST['supplierAlias'])) ? $_POST['supplierAlias'] : '';
$supplierId = (isset($_POST['supplierId'])) ? $_POST['supplierId'] : '';
$poCurrency = (isset($_POST['poCurrency'])) ? $_POST['poCurrency'] : '';
$poTerms = (isset($_POST['poTerms'])) ? $_POST['poTerms'] : '';
$poShipmentType = (isset($_POST['poShipmentType'])) ? $_POST['poShipmentType'] : '';
$poIssueDate = (isset($_POST['poIssueDate'])) ? $_POST['poIssueDate'] : '';
$poTargetReceiveDate = (isset($_POST['poTargetReceiveDate'])) ? $_POST['poTargetReceiveDate'] : '';
$poStatus = (isset($_POST['poStatus'])) ? $_POST['poStatus'] : '';
$supplierType = (isset($_POST['supplierType'])) ? $_POST['supplierType'] : '';
        
echo "<div class='row'>";
    echo "<div class='col-md-1'>";
    echo "</div>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".displayText('L224')."</label>"; // PO Number
        echo "<input list='poNumbers' class='w3-input w3-border' name='poNumber' id='poNumber' form='formFilter' autocomplete='off'>";
        echo "<datalist id='poNumbers'>";
            echo "<option></option>";
            $sql = "SELECT DISTINCT poNumber FROM purchasing_podetailsnew ORDER BY poNumber";
            $query = $db->query($sql);
            if($query AND $query->num_rows > 0)
            {
                while($result = $query->fetch_assoc())
                {
                    $selected = ($poNumber==$result['poNumber']) ? 'selected' : '';
                    echo "<option value='".$result['poNumber']."' ".$selected.">".$result['poNumber']."</option>";;
                }
            }
        echo "</datalist>";
    echo "</div>";
    echo "<span id='showSizes'>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".displayText('L367')."</label>"; // Supplier
        echo "<select class='w3-input w3-border' name='supplierId' id='supplierId' form='formFilter' autocomplete='off'>";
            echo "<option></option>";
            $sql = "SELECT DISTINCT supplierId, supplierAlias FROM purchasing_podetailsnew ORDER BY supplierAlias";
            $query = $db->query($sql);
            if($query AND $query->num_rows > 0)
            {
                while($result = $query->fetch_assoc())
                {   
                    $selected = ($supplierId==$result['supplierId']) ? 'selected' : '';
                    echo "<option value='".$result['supplierId']."' ".$selected.">".$result['supplierAlias']."</option>";;
                }
            }
        echo "</select>";
    echo "</div>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".displayText('L112')."</label>"; // Currency
        echo "<select class='w3-input w3-border' name='poCurrency' id='poCurrency' form='formFilter' autocomplete='off'>";
            echo "<option></option>";
            $sql = "SELECT DISTINCT poCurrency FROM purchasing_podetailsnew ORDER BY poCurrency";
            $query = $db->query($sql);
            if($query AND $query->num_rows > 0)
            {
                while($result = $query->fetch_assoc())
                {
                    if ($result['poCurrency'] == 1) // Dollar
                    {
                        $currency = displayText('L786');
                    }
                    elseif ($result['poCurrency'] == 2) 
                    {
                        $currency = displayText('L787'); // Peso
                    }
                    elseif ($result['poCurrency'] == 3)
                    {
                        $currency = displayText('L788'); // Yen
                    }
                    $selected = ($poCurrency==$result['poCurrency']) ? 'selected' : '';
                    echo "<option value='".$result['poCurrency']."' ".$selected.">".$currency."</option>";;
                }
            }
        echo "</select>";
    echo "</div>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".displayText('L635')."</label>"; // Terms 
        echo "<select class='w3-input w3-border' name='poTerms' id='poTerms' form='formFilter' autocomplete='off'>";
            echo "<option></option>";
            $sql = "SELECT DISTINCT poTerms FROM purchasing_podetailsnew ORDER BY poTerms";
            $query = $db->query($sql);
            if($query AND $query->num_rows > 0)
            {
                while($result = $query->fetch_assoc())
                {
                    $selected = ($poTerms==$result['poTerms']) ? 'selected' : '';
                    echo "<option value='".$result['poTerms']."' ".$selected.">".$result['poTerms']."</option>";;
                }
            }
        echo "</select>";
    echo "</div>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".displayText('L614')."</label>"; // Shipment 
        echo "<select class='w3-input w3-border' name='poShipmentType' id='poShipmentType' form='formFilter' autocomplete='off'>";
            echo "<option></option>";
            $sql = "SELECT DISTINCT poShipmentType FROM purchasing_podetailsnew ORDER BY poShipmentType";
            $query = $db->query($sql);
            if($query AND $query->num_rows > 0)
            {
                while($result = $query->fetch_assoc())
                {
                    if ($result['poShipmentType'] == 1) 
                    {
                        $shipment = "Land";
                    }
                    elseif ($result['poShipmentType'] == 2) 
                    {
                        $shipment = "Air";
                    }
                    elseif ($result['poShipmentType'] == 3) 
                    {
                        $shipment = "Sea";
                    }
                    $selected = ($poShipmentType==$result['poShipmentType']) ? 'selected' : '';
                    echo "<option value='".$result['poShipmentType']."' ".$selected.">".$shipment."</option>";;
                }
            }
        echo "</select>";
    echo "</div>";
    echo "<div class='col-md-1'>";
    echo "</div>";
echo "</div>";

echo "<div class='w3-padding-top'></div>";
echo "<div class='row'>";
    echo "<div class='col-md-1'>";
    echo "</div>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".displayText('L34')."</label>"; // R.D
        echo "<select class='w3-input w3-border' name='poTargetReceiveDate' id='poTargetReceiveDate' form='formFilter' autocomplete='off'>";
            echo "<option></option>";
            $sql = "SELECT DISTINCT poTargetReceiveDate FROM purchasing_podetailsnew ORDER BY poTargetReceiveDate";
            $query = $db->query($sql);
            if($query AND $query->num_rows > 0)
            {
                while($result = $query->fetch_assoc())
                {
                    $selected = ($poTargetReceiveDate==$result['poTargetReceiveDate']) ? 'selected' : '';
                    echo "<option value='".$result['poTargetReceiveDate']."' ".$selected.">".$result['poTargetReceiveDate']."</option>";;
                }
            }
        echo "</select>";
    echo "</div>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".displayText('L172')."</label>"; // Status
        echo "<select class='w3-input w3-border' name='poStatus' id='poStatus' form='formFilter' autocomplete='off'>";
            echo "<option></option>";
            $sql = "SELECT DISTINCT poStatus FROM purchasing_podetailsnew ORDER BY poStatus";
            $query = $db->query($sql);
            if($query AND $query->num_rows > 0)
            {
                while($result = $query->fetch_assoc())
                {
                    if ($result['poStatus'] == 0) 
                    {
                        $status = "Ongoing";
                    }
                    elseif ($result['poStatus'] == 1) 
                    {
                        $status = "For Email";
                    }
                    elseif ($result['poStatus'] == 2) 
                    {
                        $status = "Cancelled";
                    }
                    elseif ($result['poStatus'] == 3) 
                    {
                        $status = "Closed";
                    }
                    elseif ($result['poStatus'] == 4) 
                    {
                        $status = "Finished";
                    }
                    $selected = ($poStatus==$result['poStatus']) ? 'selected' : '';
                    echo "<option value='".$result['poStatus']."' ".$selected.">".$status."</option>";;
                }
            }
        echo "</select>";
    echo "</div>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".displayText('L111')."</label>"; // Type
        echo "<select class='w3-input w3-border' name='supplierType' id='supplierType' form='formFilter' autocomplete='off'>";
            echo "<option></option>";
            $sql = "SELECT DISTINCT supplierType FROM purchasing_podetailsnew ORDER BY supplierType";
            $query = $db->query($sql);
            if($query AND $query->num_rows > 0)
            {
                while($result = $query->fetch_assoc())
                {
                    if ($result['supplierType'] == 1) 
                    {
                        $supplierType = displayText('L367'); // Supplier 
                    }
                    elseif ($result['supplierType'] == 2) 
                    {
                        $supplierType = displayText('L91'); // Subcon
                    }
                    $selected = ($supplierType==$result['supplierType']) ? 'selected' : '';
                    echo "<option value='".$result['supplierType']."' ".$selected.">".$supplierType."</option>";;
                }
            }
        echo "</select>";
    echo "</div>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".displayText('L3673')."</label>"; // Issue Date From 
        echo "<input type='date' name='dateFrom' class='w3-input w3-border' value='".$dateFrom."' form='formFilter'>";
    echo "</div>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".displayText('L3674')."</label>"; // Issue Date To
        echo "<input type='date' name='dateTo' class='w3-input w3-border' value='".$dateTo."' form='formFilter'>";
    echo "</div>";
    echo "<div class='col-md-1'>";
    echo "</div>";
echo "</div>";

echo "<div class='w3-padding-top'></div>";
echo "<div class='row w3-padding'>";
    echo "<div class='col-md-12 w3-center'>";
        echo $buttonSearch;
    echo "</div>";
echo "</div>";
?>
