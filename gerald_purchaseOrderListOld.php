<?php
	include $_SERVER['DOCUMENT_ROOT']."/version.php";
	$path = $_SERVER['DOCUMENT_ROOT']."/".v."/Common Data/";
	set_include_path($path);
	include('PHP Modules/mysqliConnection.php');
	include('PHP Modules/gerald_functions.php');
	include('PHP Modules/anthony_retrieveText.php');
	ini_set("display_errors","on");
	
	function createFilterInput($sqlFilter,$column,$value)
	{
		include('PHP Modules/mysqliConnection.php');
		
		$return = "<option value=''>".displayText('L490')." </option>";
		$sql = "SELECT DISTINCT ".$column." FROM purchasing_podetailsnew ".$sqlFilter." ORDER BY ".$column."";
		if($column=='supplierId')
		{
			$supplierIdArray = array();
			$sql = "SELECT DISTINCT supplierId FROM purchasing_podetailsnew ".$sqlFilter."";
			$query = $db->query($sql);
			if($query->num_rows > 0)
			{
				while($result = $query->fetch_array())
				{
					$supplierIdArray[] = $result['supplierId'];
				}
			}
			
			if(count($supplierIdArray) > 0)
			{
				$sql = "SELECT supplierId, supplierAlias FROM purchasing_supplier WHERE supplierId IN(".implode(",",$supplierIdArray).") ORDER BY supplierAlias";
			}
			
			if(count($supplierIdArray) > 0)
			{
				if(strstr($sqlFilter,'supplierType = 2')!==FALSE)
				{
					$supplierAliasType = 'subconAlias';
					$supplierIdType = 'subconId';
					$sql = "SELECT subconId, subconAlias FROM purchasing_subcon WHERE subconId IN(".implode(",",$supplierIdArray).") ORDER BY subconAlias";
				}
				else
				{
					$supplierAliasType = 'supplierAlias';
					$supplierIdType = 'supplierId';
					$sql = "SELECT supplierId, supplierAlias FROM purchasing_supplier WHERE supplierId IN(".implode(",",$supplierIdArray).") ORDER BY supplierAlias";
				}
			}
		}		
		$query = $db->query($sql);
		if($query->num_rows > 0)
		{
			while($result = $query->fetch_array())
			{
				$valueColumn = $valueCaption = $result[$column];
				
				$selected = ($value==$result[$column]) ? 'selected' : '';
				
				if($column=='supplierId')	
				{
					$valueColumn = $result[0];
					$valueCaption = $result[1];
					$selected = ($value==$result[0]) ? 'selected' : '';
				}
				else if($column=='poStatus')
				{
					if($valueColumn==0)		$valueCaption = 'Ongoing';
					else if($valueColumn==1)	$valueCaption = 'For Email';
					else if($valueColumn==2)	$valueCaption = 'Canceled';
					else if($valueColumn==3)	$valueCaption = 'Closed';
					else if($valueColumn==4)	$valueCaption = 'Finished';
				}
				else if($column=='supplierType')
				{
					if($valueColumn==1)	$valueCaption = 'Supplier';
					else if($valueColumn==2)	$valueCaption = 'Subcon';
				}
				else if($column=='poCurrency')
				{
					if($valueColumn==1)	$valueCaption = 'Dollar';
					else if($valueColumn==2)	$valueCaption = 'Peso';
					else if($valueColumn==3)	$valueCaption = 'Yen';
				}
				else if($column=='poShipmentType')
				{
					if($valueColumn==1)	$valueCaption = 'Land';
					else if($valueColumn==2)	$valueCaption = 'Air';
					else if($valueColumn==3)	$valueCaption = 'Sea';
				}
				
				$return .= "<option value='".$valueColumn."' ".$selected.">".$valueCaption."</option>";
			}
		}
		return $return;
	}
	
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
	
	if($poNumber!='')	$sqlFilterArray[] = "poNumber LIKE '".$poNumber."'";
	if($supplierAlias!='')	$sqlFilterArray[] = "supplierAlias = '".$supplierAlias."'";
	if($supplierId!='')	$sqlFilterArray[] = "supplierId = ".$supplierId."";
	if($poCurrency!='')	$sqlFilterArray[] = "poCurrency = ".$poCurrency."";
	if($poTerms!='')	$sqlFilterArray[] = "poTerms LIKE '".$poTerms."'";
	if($poShipmentType!='')	$sqlFilterArray[] = "poShipmentType = ".$poShipmentType."";
	if($poIssueDate!='')	$sqlFilterArray[] = "poIssueDate LIKE '".$poIssueDate."'";
	if($poTargetReceiveDate!='')	$sqlFilterArray[] = "poTargetReceiveDate LIKE '".$poTargetReceiveDate."'";
	if($poStatus!='')	$sqlFilterArray[] = "poStatus = ".$poStatus."";
	if($supplierType!='')	$sqlFilterArray[] = "supplierType = ".$supplierType."";
	if($dateFrom!='' AND $dateTo == '')	$sqlFilterArray[] = "poIssueDate >= '".$dateFrom."'";
	if($dateFrom != '' AND $dateTo != '') $sqlFilterArray[] = "poIssueDate BETWEEN '".$dateFrom."' AND '".$dateTo."'";
	
	$sqlFilter = "";
	if(count($sqlFilterArray) > 0)
	{
		$sqlFilter = " WHERE ".implode(" AND ",$sqlFilterArray)." ";
	}
	
	$sql = "SELECT poNumber FROM purchasing_podetailsnew ".$sqlFilter;
	$queryParts = $db->query($sql);
	$totalRecords = ($queryParts AND $queryParts->num_rows > 0) ? $queryParts->num_rows : 0;
?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo displayText('4-3','utf8',0,1,1)?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="/<?php echo v; ?>/Common Data/Templates/api.css">
	<script src="/<?php echo v; ?>/Common Data/Templates/api.js"></script>
	<style>
		.dropdown {
			position: relative;
			display: inline-block;
		}

		.dropdown-content {
			display: none;
			position: absolute;
			z-index: 1;
		}

		.dropdown:hover .dropdown-content {
			display: block;
		}
		
		
	</style>
</head>
<?php
		//echo "<a href=\"#\" onclick= \"window.open('rose_purchaseOrderListview.php?sqlFilter=".$sqlFilter."','tet','left=50,screenX=900,screenY=200,resizable,scrollbars,status,width=700,height=500'); return false;\">try</a>".$sqlFilter;
?>
<body class='api-loading'>
<?php
	createHeader('4-3');
?>
	<form action='gerald_purchaseOrderListExport.php' method='post' id='exportFormId'></form>
	<input type='hidden' name='sqlFilter' value="<?php echo $sqlFilter;?>" form='exportFormId'>
	<div class="api-row">
		<div class="api-top api-col api-left-buttons" style='width:30%'>
<!--
			<button class='api-btn api-btn-home' onclick="location.href='/V3/dashboard.php';" data-api-title='<?php echo displayText('L434');?>' <?php echo toolTip('L434');?>></button>
-->
		</div>
		
		<div class="api-top api-col api-title" style='width:40%;'>
<!--
			<h2><?php echo displayText('4-3','utf8',0,1,1)?></h2>
-->
		</div>
		<div class="api-top api-col api-right-buttons" style='width:30%'>
			<form action='rose_purchaseOrderListview.php' method='post'>
				<input type='hidden' name='sqlFilter' value="<?php echo $sqlFilter;?>">
				<input type='submit' name='test' value="1">
			</form>	
			<input type='hidden' name='defaultCheck' value='1' form='formFilter'>
			<button type='submit' name='export' value='exportOpen' class='api-btn api-btn-excel' style='width:33%' data-api-title='<?php echo displayText('L3659'); ?>' form='exportFormId'></button>
			<button type='submit' name='export' value='exportAll' class='api-btn api-btn-excel' style='width:33%' data-api-title='<?php echo displayText('L487'); ?>' form='exportFormId'></button>
			<button class='api-btn api-btn-refresh' onclick="location.href='';" style='width:33%' data-api-title='<?php echo displayText('L436');?>' <?php echo toolTip('L436');?>></button>
		</div>
		
		<div class="api-col" style='width:100%;height:92vh;'>
			<!-------------------- Filters -------------------->
			<form action='' method='post' id='formFilter' autocomplete="off"></form>	
			<table cellpadding="0" cellspacing="0" border="0" style='width:100%;'>
				<tr style='font-size:12px;'>
					<td style='width:10%' align='center' ><?php echo displayText('L224'); ?>		<input type='image' onclick='this.form.submit()' src='/<?php echo v; ?>/Common Data/Templates/images/submitBtn.png' width=15 title='Filter' form='formFilter' style='border:1px solid blue;<?php if($poNumber!='') echo 'background-color:red';?>'></td>
					<td style='width:10%' align='center' ><?php echo displayText('L367'); ?>		<input type='image' onclick='this.form.submit()' src='/<?php echo v; ?>/Common Data/Templates/images/submitBtn.png' width=15 title='Filter' form='formFilter' style='border:1px solid blue;<?php if($supplierAlias!='') echo 'background-color:red';?>'></td>
					<td style='width:10%' align='center' ><?php echo displayText('L112'); ?>		<input type='image' onclick='this.form.submit()' src='/<?php echo v; ?>/Common Data/Templates/images/submitBtn.png' width=15 title='Filter' form='formFilter' style='border:1px solid blue;<?php if($poCurrency!='') echo 'background-color:red';?>'></td>
					<td style='width:10%' align='center' ><?php echo displayText('L635'); ?>		<input type='image' onclick='this.form.submit()' src='/<?php echo v; ?>/Common Data/Templates/images/submitBtn.png' width=15 title='Filter' form='formFilter' style='border:1px solid blue;<?php if($poTerms!='') echo 'background-color:red';?>'></td>
					<td style='width:10%' align='center' ><?php echo displayText('L614'); ?>		<input type='image' onclick='this.form.submit()' src='/<?php echo v; ?>/Common Data/Templates/images/submitBtn.png' width=15 title='Filter' form='formFilter' style='border:1px solid blue;<?php if($poShipmentType!='') echo 'background-color:red';?>'></td>
					<td style='width:10%' align='center' ><?php echo displayText('L34'); ?>		<input type='image' onclick='this.form.submit()' src='/<?php echo v; ?>/Common Data/Templates/images/submitBtn.png' width=15 title='Filter' form='formFilter' style='border:1px solid blue;<?php if($poTargetReceiveDate!='') echo 'background-color:red';?>'></td>
					<td style='width:10%' align='center' ><?php echo displayText('L172'); ?>		<input type='image' onclick='this.form.submit()' src='/<?php echo v; ?>/Common Data/Templates/images/submitBtn.png' width=15 title='Filter' form='formFilter' style='border:1px solid blue;<?php if($poStatus!='') echo 'background-color:red';?>'></td>
					<td style='width:10%' align='center' ><?php echo displayText('L111'); ?>		<input type='image' onclick='this.form.submit()' src='/<?php echo v; ?>/Common Data/Templates/images/submitBtn.png' width=15 title='Filter' form='formFilter' style='border:1px solid blue;<?php if($supplierType!='') echo 'background-color:red';?>'></td>
					<td style='width:10%' align='center' ><?php echo displayText('L342')." ".displayText('L134'); ?>
					<td style='width:10%' align='center' ><?php echo displayText('L342')." ".displayText('L135'); ?>		
					<td align='left' style=''></td>
					
				</tr>
				<tr>
					<td><input list='poNumber' name='poNumber' class='api-form' value='<?php echo $poNumber;?>' form='formFilter'><datalist id='poNumber' class='classDataList'><?php echo createFilterInput($sqlFilter,'poNumber',$poNumber);?></datalist></td>
<!--
					<td><select name='supplierId' class='api-form' value='<?php echo $supplierId;?>' form='formFilter'><?php echo createFilterInput($sqlFilter,'supplierId',$supplierId);?></select></td>
-->
					<td><select name='supplierAlias' class='api-form' value='<?php echo $supplierAlias;?>' form='formFilter'><?php echo createFilterInput($sqlFilter,'supplierAlias',$supplierAlias);?></select></td>
					<td><select name='poCurrency' class='api-form' value='<?php echo $poCurrency;?>' form='formFilter'><?php echo createFilterInput($sqlFilter,'poCurrency',$poCurrency);?></select></td>
					<td><select name='poTerms' class='api-form' value='<?php echo $poTerms;?>' form='formFilter'><?php echo createFilterInput($sqlFilter,'poTerms',$poTerms);?></select></td>
					<td><select name='poShipmentType' class='api-form' value='<?php echo $poShipmentType;?>' form='formFilter'><?php echo createFilterInput($sqlFilter,'poShipmentType',$poShipmentType);?></select></td>
					<td><select name='poTargetReceiveDate' class='api-form' value='<?php echo $poTargetReceiveDate;?>' form='formFilter'><?php echo createFilterInput($sqlFilter,'poTargetReceiveDate',$poTargetReceiveDate);?></select></td>
					<td><select name='poStatus' class='api-form' value='<?php echo $poStatus;?>' form='formFilter'><?php echo createFilterInput($sqlFilter,'poStatus',$poStatus);?></select></td>
					<td><select name='supplierType' class='api-form' value='<?php echo $supplierType;?>' form='formFilter'><?php echo createFilterInput($sqlFilter,'supplierType',$supplierType);?></select></td>
					<td><input type='date' name='dateFrom' class='api-form' value='<?php echo $dateFrom;?>' form='formFilter'></td>
					<td><input type='date' name='dateTo' class='api-form' value='<?php echo $dateTo;?>' form='formFilter'></td>
					<td><button type='submit' class='api-btn' onclick="location.href='';" data-api-title='<?php echo displayText('B7');?>' <?php echo toolTip('L437');?> form='formFilter'></button></td>
				</tr>
			</table>
			<!------------------ End Filters ------------------>
			
			<!-------------------- Contents -------------------->
			
			<?php echo displayText('L41'); ?> : <span><?php echo $totalRecords; ?></span>
			<div style='height: 89%;'><!-- Adjust height if browser had a vertical scroll -->
				<table id='mainTableId' class="api-table-fixedheader api-table-design2" data-counter='-1' data-detail-type='left'>
					<thead>
						<tr>
							<th ></th>
							<th ><?php echo displayText('L224'); ?></th>
							<th ><?php echo displayText('L367'); ?></th>
							<th ><?php echo displayText('L112'); ?></th>
							<th ><?php echo displayText('L635'); ?></th>
							<th ><?php echo displayText('L614'); ?></th>
							<th ><?php echo displayText('L342'); ?></th>
							<th ><?php echo displayText('L149'); ?></th>
							<th ><?php echo displayText('L172'); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						
					</tbody>
					<tfoot>
						<tr>
							<th><input type='checkbox' name='checkall' id='chkAll'></th>
							<th><label for='chkAll'><?php echo displayText('L326'); ?></label></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
						</tr>
					</tfoot>
				</table>
			</div>
			<!------------------ End Contents ------------------>			
			
		</div>
	</div>
</body>
<!-- <script src="/<?php echo v; ?>/Common Data/Templates/jquery.js"></script> -->
<script src="/<?php echo v; ?>/Common Data/Templates/api.jquery.js"></script>
<script>
	function colorThis(obj)
	{
		$("td.tempClass").css('background-color','');
		$("td").removeClass("tempClass");
		$(obj).parents("td").prop('class','tempClass');
		$("td.tempClass").css('background-color','orange');
	}
	
	$(function(){
		$("#mainTableId").apiQuickTable({
			url:'gerald_purchaseOrderListAjaxOld.php',
			filterSql:"<?php echo $sqlFilter." ".$sqlSort; ?>",
			recordCount:parseFloat("<?php echo $totalRecords/50;?>"),
			customFunction:function(){
				
			}
		});
		
		$("select.api-form").change(function(){
			if($(this).val()=='')	this.form.submit();
		});
		
		$('body').removeClass('api-loading');
		$(window).bind('beforeunload',function(){
			$('body').addClass('api-loading');
		});
	});
	
	//  -------------------------------------------------- For Modal Box Javascript Code -------------------------------------------------- //
	function jsFunctions(){
		
	}	
	//  ------------------------------------------------ END For Modal Box Javascript Code ------------------------------------------------ //
</script>
<!-- -----------------------------------Tiny Box------------------------------------------------------------- -->
<script type="text/javascript" src="/<?php echo v; ?>/Common Data/Libraries/Javascript/Tiny Box/tinybox.js"></script>
<link rel="stylesheet" href="/<?php echo v; ?>/Common Data/Libraries/Javascript/Tiny Box/stylebox.css" />
<script type="text/javascript">
function openTinyBox(w,h,url,post,iframe,html,left,top)
{
	var windowWidth = $(window).width();
	var windowHeight = $(window).height();
	TINY.box.show({
		url:url,width:w,height:h,post:post,html:html,opacity:20,topsplit:6,animate:false,close:true,iframe:iframe,left:left,top:top,
		boxid:'box',
		openjs:function(){
			if($("#tableDiv").length != 0 )
			{
				var windowHeight = $(window).height() / 1.5;
				var tinyBoxHeight = $("#box").height();
				if(tinyBoxHeight > (windowHeight))
				{
					$("#tableDiv").css({'overflow-y':'scroll','overflow-x':'hidden','height':(windowHeight) + 'px'});
					$("#box").css('height',(windowHeight) +'px');
					$("#box").css('width',($("#box").width() + 20 ) +'px');
				}
			}
		}
	});
}
</script>   
<!-- -----------------------------------END SMALL BOX----------------------------------------------------------------> 
</html>
