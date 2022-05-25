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

$title = "";
PMSTemplates::includeHeader($title, 0);
$displayId = ""; # RO LIST
$version = "";
$prevousLink = "";
createHeader($displayId, $version, $prevousLink);
?>
<form action="" method="post" id='formFilter'></form>
<div class="container-fluid">
	<div class="row w3-padding-top">
		<div class="col-md-12">
			<input type="text" name="poNumber" id="poNumber" form='formFilter' value='<?php echo (isset($_REQUEST['poNumber'])) ? $_REQUEST['poNumber'] : '0016943';?>'>
			<button id='reload'>Submit</button>
		</div>
	</div>
	<div class="row w3-padding-top">
		<div class='col-md-12'>
			<table id='mainTableId' style='' class="table table-bordered table-striped table-condensed" data-counter='-1' data-detail-type='left'>
				<thead class='w3-indigo' style='text-transform:uppercase;'>
					<tr>
						<th>#</th>
						<th>Lot Number</th>
						<th>Description</th>
						<th>Price</th>
						<th>Quantity</th>
						<th>Amount</th>
						<th></th>
					</tr>
				</thead>
				<tbody></tbody>
				<tfoot class='w3-indigo' style='text-transform: uppercase;'>
					<tr>
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
	</div>
</div>
<div id='modal-izi'><span class='izimodal-content'></span></div>
<?php
PMSTemplates::includeFooter();
?>
<script>
	const openItemDetails = (data) => {
		$("#modal-izi").iziModal({
			title                   : '<i class="fa fa-flash"></i> VIEW',
			headerColor             : '#1F4788',
			subtitle                : '<b><?php echo strtoupper(date('F d, Y'));?></b>',
			width                   : 1200,
			fullscreen              : false,
			transitionIn            : 'comingIn',
			transitionOut           : 'comingOut',
			padding                 : 20,
			radius                  : 0,
			top                     : 10,
			restoreDefaultContent   : true,
			closeOnEscape           : true,
			closeButton             : true,
			overlayClose            : false,
			onOpening               : function(modal){
										modal.startLoading();
										$.ajax({
											url			:'gerald_viewPOStatusModal.php',
											type        : 'POST',
											data        : {
															ajaxType:'ezModal',
															...data
											},
											success     : function(data){
															$( ".izimodal-content" ).html(data);
															modal.stopLoading();
											}
										});
									},
				onClosed            : function(modal){
										$("#modal-izi").iziModal("destroy");
						}
		});

		$("#modal-izi").iziModal("open");		
	}

	$(document).ready(function() {
		const dataTable = $('#mainTableId').DataTable( {
			bInfo : false,
			// serverSide: true,
			ordering: false,
			searching: false,
			createdRow: function( row, data, index ) {
				$(row).find('.viewClass').click(function(){
					openItemDetails(data)
				})
			},
			ajax: {
				url:'gerald_controllerPOStatus.php',
				type:'post',
				data: function (d) {
					return $.extend({},d,{
						ajaxType: 'dataTable',
						poNumber: $("#poNumber").val()
					});
				}
			},
			columns: [
				{ data: 'counter' },
				{ data: 'lotNumber' },
				{ data: 'description' },
				{ data: 'itemPrice' },
				{ data: 'itemQuantity' },
				{ data: 'amount' },
				{
					data: null,
					defaultContent: `<img class='viewClass' src='/<?php echo v;?>/Common Data/Templates/images/view1.png' width='20' height='20' alt='VIEW' title='VIEW'>`
				}
			],
			scrollY: '80vh',
			scroller: {
				loadingIndicator: true,
			},
		} );
		// console.log(dataTable);

		$("#reload").click(function(){
			dataTable.ajax.reload();
		})
	} );	
</script>
</body>
</html>