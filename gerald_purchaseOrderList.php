<?php
include $_SERVER['DOCUMENT_ROOT']."/version.php";
$path = $_SERVER['DOCUMENT_ROOT']."/".v."/Common Data/";
set_include_path($path);    
include('PHP Modules/mysqliConnection.php');
include('PHP Modules/anthony_wholeNumber.php');
include('PHP Modules/anthony_retrieveText.php');
include('PHP Modules/gerald_functions.php');
include('PHP Modules/rose_prodfunctions.php');
ini_set("display_errors", "on");

$tpl = new PMSTemplates;



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
    
$sqlFilter = "";
$sqlFilterArray = $sqlFilterMaterialSpecsArray = array();

if($poNumber!='')   $sqlFilterArray[] = "poNumber LIKE '".$poNumber."'";
if($supplierAlias!='')  $sqlFilterArray[] = "supplierAlias = '".$supplierAlias."'";
if($supplierId!='') $sqlFilterArray[] = "supplierId = ".$supplierId."";
if($poCurrency!='') $sqlFilterArray[] = "poCurrency = ".$poCurrency."";
if($poTerms!='')    $sqlFilterArray[] = "poTerms LIKE '".$poTerms."'";
if($poShipmentType!='') $sqlFilterArray[] = "poShipmentType = ".$poShipmentType."";
if($poIssueDate!='')    $sqlFilterArray[] = "poIssueDate LIKE '".$poIssueDate."'";
if($poTargetReceiveDate!='')    $sqlFilterArray[] = "poTargetReceiveDate LIKE '".$poTargetReceiveDate."'";
if($poStatus!='')   $sqlFilterArray[] = "poStatus = ".$poStatus."";
if($supplierType!='')   $sqlFilterArray[] = "supplierType = ".$supplierType."";
if($dateFrom!='' AND $dateTo == '') $sqlFilterArray[] = "poIssueDate >= '".$dateFrom."'";
if($dateFrom != '' AND $dateTo != '') $sqlFilterArray[] = "poIssueDate BETWEEN '".$dateFrom."' AND '".$dateTo."'";

$sqlFilter = "";
if(count($sqlFilterArray) > 0)
{
    $sqlFilter = " WHERE ".implode(" AND ",$sqlFilterArray)." ";
}

$sql = "SELECT * FROM purchasing_podetailsnew".$sqlFilter." ORDER BY CAST(poNumber as unsigned) DESC";
$query = $db->query($sql);
if($query AND $query->num_rows > 0)
{
    $result = $query->fetch_assoc();
}   

$sqlData = $sql;
$totalRecords = $query->num_rows;

$tpl->setDataValue("L437"); // Filter
$tpl->setAttribute("id","filterData");
$tpl->setAttribute("type","button");
$buttonFilter = $tpl->createButton();

$tpl->setDataValue("L436"); // Refresh
$tpl->setAttribute("onclick","location.href=''");
$tpl->setAttribute("type","button");
$buttonRefresh = $tpl->createButton();

$tpl->setDataValue("L3659"); // Export Open
$tpl->setAttribute("type","submit");
$tpl->setAttribute("name","export");
$tpl->setAttribute("value","exportOpen");
$tpl->setAttribute("form","exportFormId");
$buttonExportOpen = $tpl->createButton();

$tpl->setDataValue("L487"); // Export
$tpl->setAttribute("type","submit");
$tpl->setAttribute("name","export");
$tpl->setAttribute("value","exportAll");
$tpl->setAttribute("form","exportFormId");
$buttonExport = $tpl->createButton();


$title = displayText('4-3', 'utf8', 0, 1);
PMSTemplates::includeHeader($title);
?>
<style>
    .dropdown 
    {
        position: relative;
        display: inline-block;
    }

    .dropdown-content, .dropdown-content-filter 
    {
        display: none;
        position: absolute;
        background-color:white;
        z-index: 9999999;
    }

    .dropdown:hover .dropdown-content 
    {
        display: block;
    }
</style>
<?php 
$displayId = "4-3"; // Purchase Order List
$version = "";
$previousLink = "/".v."/4-14 Purchasing Software/raymond_purchasingSoftware.php";
createHeader($displayId, $version, $previousLink);
?>
<form action='<?php echo ($_GET['country']==1) ? 'gerald_purchaseOrderListExportOld.php' : 'gerald_purchaseOrderListExport.php';?>' method='post' target='_blank' id='exportFormId'></form>
<input type='hidden' name='sqlFilter' value="<?php echo $sqlFilter;?>" form='exportFormId'>
<form action='' method='POST' id='formFilter'></form>
<div class="container-fluid">
    <div class="row w3-padding-top">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <!-- <button type='submit' name='export' value='exportOpen' class='api-btn api-btn-excel' style='width:10%' data-api-title='<?php echo displayText('L3659'); ?>' form='exportFormId'><?php echo displayText('L3659'); ?></button> 
            <button type='submit' name='export' value='exportAll' class='api-btn api-btn-excel' style='width:10%' data-api-title='<?php echo displayText('L487'); ?>' form='exportFormId'><?php echo displayText('L487'); ?></button> -->
            <?php 
                echo $buttonExportOpen." ".$buttonExport; 
            ?>
            <!--TAMANG-->
            <div class="w3-right">

                <a href="http://192.168.254.163/V4/4-4%20Purchase%20Order%20Monitoring/jhon_purchaseOrderMonitoringV2.php?valueType=1"><button type="submit" class="w3-btn w3-round w3-indigo" name="btn"><i class="fa fa-list"></i><b> API ORDER LIST</b></button></a>
            <!--TAMANG-->
            <?php 
                echo $buttonAdd." ".$buttonFilter." ".$buttonRefresh;
            ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 w3-padding-top">
            <?php
            echo "<label>".displayText('L41','utf8',0,0,1)." : ".$totalRecords."</label>";
            ?>
            <table class='table table-bordered table-condensed table-striped' id="mainTableId">
                <thead class='w3-indigo thead' style='text-transform:uppercase;'>
                    <th style='vertical-align:middle;' class='w3-center'></th>
                    <th style='vertical-align:middle;' class='w3-center'><?php echo displayText('L224'); ?></th> <!-- PO Number -->
                    <th style='vertical-align:middle;' class='w3-center'><?php echo displayText('L367'); ?></th> <!-- Supplier --> 
                    <th style='vertical-align:middle;' class='w3-center'><?php echo displayText('L112'); ?></th> <!-- Currency --> 
                    <th style='vertical-align:middle;' class='w3-center'><?php echo displayText('L635'); ?></th> <!-- Terms--> 
                    <th style='vertical-align:middle;' class='w3-center'><?php echo displayText('L614'); ?></th> <!-- Shipment--> 
                    <th style='vertical-align:middle;' class='w3-center'><?php echo displayText('L342'); ?></th> <!-- Issue Date --> 
                    <th style='vertical-align:middle;' class='w3-center'><?php echo displayText('L149'); ?></th> <!-- Received Date --> 
                    <th style='vertical-align:middle;' class='w3-center'><?php echo displayText('L172'); ?></th> <!-- Status --> 
                    <th style='vertical-align:middle;' class='w3-center'><?php echo displayText(''); ?></th> <!-- L188 View -->
                </thead>
                <tbody class='w3-center tbody'>
                
                </tbody>
                <tfoot class='w3-indigo thead'>
                    <th style='vertical-align:middle;' class='w3-center'></th>
                    <th style='vertical-align:middle;' class='w3-center'></th>
                    <th style='vertical-align:middle;' class='w3-center'></th>
                    <th style='vertical-align:middle;' class='w3-center'></th>
                    <th style='vertical-align:middle;' class='w3-center'></th>
                    <th style='vertical-align:middle;' class='w3-center'></th>
                    <th style='vertical-align:middle;' class='w3-center'></th>
                    <th style='vertical-align:middle;' class='w3-center'></th>
                    <th style='vertical-align:middle;' class='w3-center'></th>
                    <th style='vertical-align:middle;' class='w3-center'></th>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div id='modal-izi'><span class='izimodal-content'></span></div>
<div id='modal-izi-add'><span class='izimodal-content-add'></span></div>
<?php
PMSTemplates::includeFooter();
?>
<script type="text/javascript">

function viewData(link)
{
    $("#modal-izi").iziModal({
        title                   : '<i class="fa fa-eye"></i> <?php echo strtoupper(displayText("L188")); ?>', // View
        headerColor             : '#1F4788',
        subtitle                : '<b><?php echo strtoupper(date('F d, Y'));?></b>',
        width                   : 600,
        fullscreen              : false,
        iframe                  : true,
        iframeURL               : link,
        iframHeight             : 500,
        transitionIn            : 'comingIn',
        transitionOut           : 'comingOut',
        padding                 : 20,
        radius                  : 0,
        top                     : 100,
        restoreDefaultContent   : true,
        closeOnEscape           : true,
        closeButton             : true,
        overlayClose            : false,
        onOpening               : function(modal){
                                    
                                },
            onClosed            : function(modal){
                                    $("#modal-izi").iziModal("destroy");
                    }
    });

    $("#modal-izi").iziModal("open");
}

function deleteData(link)
{
    $("#modal-izi").iziModal({
        title                   : '<i class="fa fa-times"></i> <?php echo strtoupper(displayText("L1539")); ?>', // Cancel
        headerColor             : '#1F4788',
        subtitle                : '<b><?php echo strtoupper(date('F d, Y'));?></b>',
        width                   : 1200,
        fullscreen              : false,
        iframe                  : true,
        iframeURL               : link,
        iframHeight             : 500,
        transitionIn            : 'comingIn',
        transitionOut           : 'comingOut',
        padding                 : 20,
        radius                  : 0,
        top                     : 100,
        restoreDefaultContent   : true,
        closeOnEscape           : true,
        closeButton             : true,
        overlayClose            : false,
        onOpening               : function(modal){
                                    
                                },
            onClosed            : function(modal){
                                    $("#modal-izi").iziModal("destroy");
                    }
    });

    $("#modal-izi").iziModal("open");
}

function downloadData(link)
{
    $("#modal-izi").iziModal({
        title                   : '<i class="fa fa-times"></i> <?php echo strtoupper(displayText("L1202")); ?>', // Download
        headerColor             : '#1F4788',
        subtitle                : '<b><?php echo strtoupper(date('F d, Y'));?></b>',
        width                   : 1200,
        fullscreen              : false,
        iframe                  : true,
        iframeURL               : link,
        iframHeight             : 500,
        transitionIn            : 'comingIn',
        transitionOut           : 'comingOut',
        padding                 : 20,
        radius                  : 0,
        top                     : 100,
        restoreDefaultContent   : true,
        closeOnEscape           : true,
        closeButton             : true,
        overlayClose            : false,
        onOpening               : function(modal){

                                },
            onClosed            : function(modal){
                                    $("#modal-izi").iziModal("destroy");
                    }
    });

    $("#modal-izi").iziModal("open");
}

function viewPDF(link)
{
    //alert(link);
    $("#modal-izi").iziModal({
        title                   : '<i class="fa fa-file"></i> PDF',
        headerColor             : '#1F4788',
        subtitle                : '<b><?php echo strtoupper(date('F d, Y'));?></b>',
        width                   : 1200,
        fullscreen              : false,
        iframe                  : true,
        iframeURL               : link,
        iframeHeight            : 500,
        transitionIn            : 'comingIn',
        transitionOut           : 'comingOut',
        padding                 : 20,
        radius                  : 0,
        top                     : 100,
        restoreDefaultContent   : true,
        closeOnEscape           : true,
        closeButton             : true,
        overlayClose            : false,
        onOpening               : function(modal){

                                },
            onClosed            : function(modal){
                                    $("#modal-izi").iziModal("destroy");
                    }
    });

    $("#modal-izi").iziModal("open");
}

$(document).ready(function(event){
    var sql = "<?php echo $sqlData; ?>";
    var totalRecords = "<?php echo $totalRecords; ?>";
    var dataTable = $('#mainTableId').DataTable({
        "processing"    : true,
        "ordering"      : false,
        "serverSide"    : true,
        "bInfo"         : false,
        "ajax"          :{
            url     : "gerald_purchaseOrderListAJAX.php", // json datasource
            type    : "POST",  // method  , by default get
            data    : {
                        "sqlData"                   : sql,
                        "totalRecords"              : totalRecords,
                      },
            error   : function(){  // error handling
                $(".mainTableId-error").html("");
                $("#mainTableId").append('<tbody class="mainTableId-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                $("#mainTableId_processing").css("display","none");
            }
        },
        "createdRow": function( row, data, index ) {
            $(row).addClass("w3-hover-deep-orange");
        },
        "initComplete": function(settings, json) {
            $('body').find('.dataTables_scrollBody').addClass("scrollbar");
        },
        "columnDefs": [
                        // {
                        //     "targets"       : [ 1, 2, 3, hiddenIndex, packHidden],
                        //     "visible"       : false,
                        //     "searchable"    : true
                        // }
                        // {
                        //     targets: -1,
                        //     className: 'dt-body-right'
                        // }
                        {
                            "targets" 		: [ 0 ],
                            "width"			: "1%"
                        }
        ],
        language    : {
                    processing  : ""
        },
        fixedColumns:   {
                leftColumns: 0
        },
        scrollX         : false,
        scrollY         : 570,
        scrollCollapse  : false,
        scroller        : {
            loadingIndicator    : true
        },
        stateSave       : false
    });
    

    $("#filterData").click(function(){
        $("#modal-izi-add").iziModal({
            title                   : '<i class="fa fa-filter"></i> <?php echo displayText("B7","utf8",0,0,1);?>',
            headerColor             : '#1F4788',
            subtitle                : '<b><?php echo strtoupper(date('F d, Y'));?></b>',
            width                   : 1200,
            fullscreen              : false,
            transitionIn            : 'comingIn',
            transitionOut           : 'comingOut',
            padding                 : 20,
            radius                  : 0,
            top                     : 100,
            restoreDefaultContent   : true,
            closeOnEscape           : true,
            closeButton             : true,
            overlayClose            : false,
            onOpening               : function(modal){
                                        modal.startLoading();
                                        // alert(assignedTo);
                                        $.ajax({
                                            url         : 'carlo_purchaseOrderListFilter.php',
                                            type        : 'POST',
                                            data        : {
                                                            sqlData      : sql,
                                                            postVariable : "<?php echo str_replace('"',"'",json_encode($_POST));?>"
                                            },
                                            success     : function(data){
                                                            $( ".izimodal-content-add" ).html(data);
                                                            modal.stopLoading();
                                            }
                                        });
                                    },
            onClosed                : function(modal){
                                        $("#modal-izi-add").iziModal("destroy");
                        } 
        });

        $("#modal-izi-add").iziModal("open");
    });
});

</script>
