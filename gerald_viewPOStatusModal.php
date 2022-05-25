<?php
include('gerald_controllerPOStatus.php');

?>
<table class="table table-bordered table-striped table-condensed" style='<?php if($tBodySupply=='') echo "display:none;";?>'>
	<thead class='w3-indigo' style='text-transform:uppercase;'>
		<tr>
			<td>Lot Number</td>
			<td>Supply Date</td>
			<td>Supply Quantity</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<?php echo $tBodySupply;?>
		</tr>
	</tbody>
	<tfoot class='w3-indigo' style='text-transform:uppercase;'>
		<tr>
			<td></td>
			<td></td>
			<td><?php echo $totalSupplyQuantity;?></td>
		</tr>
	</tfoot>
</table>

<table class="table table-bordered table-striped table-condensed">
	<thead class='w3-indigo' style='text-transform:uppercase;'>
		<tr>
			<td>Lot Number</td>
			<td>Receive Date</td>
			<td>Receive Quantity</td>
			<td>Debit Quantity</td>
			<td>Credit Quantity</td>
			<td>Cut-Off Month</td>
			<td>APV Number</td>
			<td>APV Date</td>
			<td>CV Number</td>
			<td>CV Date</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<?php echo $tBodyReceive;?>
		</tr>
	</tbody>
	<tfoot class='w3-indigo' style='text-transform:uppercase;'>
		<tr>
			<td></td>
			<td></td>
			<td><?php echo $totalReceiveQuantity;?></td>
			<td><?php echo $totalDeductQuantity;?></td>
			<td><?php echo $totalCreditQuantity;?></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</tfoot>				
</table>