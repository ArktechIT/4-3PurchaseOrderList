<?php
include $_SERVER['DOCUMENT_ROOT']."/version.php";
$path = $_SERVER['DOCUMENT_ROOT']."/".v."/Common Data/";
set_include_path($path);  
include('PHP Modules/mysqliConnection.php');
require_once('Libraries/PHP/TCPDF-master/tcpdf.php');
ini_set("display_errors", "on");

	$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);//210, 297
	
	$paperWidth = 210;
	$paperLength = 297;
	$left = 5;
	$top = 5;
	$cols = 2;
	$rows = 3;
	
	$boxWidth = ($paperWidth / $cols);
	$boxLength = ($paperLength / $rows);
	
	$poNumber = $_GET['poNumber'];

	$style = array('align' => 'C','stretch' => false,'text' => true,'font' => 'helvetica','fontsize' => 8,'stretchtext' => 0);
	
	$arkLogo = '/'.v.'/Common Data/Templates/images/arkLogo.jpg';

	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	
	$pdf->SetLineStyle(array('dash' => 0));
	$pdf->SetFont('Helvetica','',9);
	$pdf->SetAutoPageBreak(false, 0);
	
	$y = $top;
	$w = $boxWidth - ($top * 2);
	$h = 7;
	echo "Asd"; 
	$index = 0;
	$sql = "SELECT poContentId, itemQuantity, lotNumber, dataOne, dataTwo, dataThree, dataFour FROM purchasing_pocontents WHERE poNumber LIKE '".$poNumber."'";
	$queryOpenPoList = $db->query($sql);
	if($queryOpenPoList->num_rows > 0)
	{
		while($resultOpenPoList = $queryOpenPoList->fetch_array())
		{
			$poContentId = $resultOpenPoList['poContentId'];
			$poQuantity = $resultOpenPoList['itemQuantity'];
			$lotNumber = $resultOpenPoList['lotNumber'];
			
			$productionTag = $partId = $identifier = $lotStatus = $poContentIds = '';
			$sql = "SELECT productionTag, partId, identifier, status, poContentId FROM ppic_lotlist WHERE lotNumber LIKE '".$lotNumber."' LIMIT 1";
			$queryLotList = $db->query($sql);
			if($queryLotList AND $queryLotList->num_rows > 0)
			{
				$resultLotList = $queryLotList->fetch_assoc();
				$productionTag = $resultLotList['productionTag'];
				$partId = $resultLotList['partId'];
				$identifier = $resultLotList['identifier'];
				$lotStatus = $resultLotList['status'];
				$poContentIds = $resultLotList['poContentId'];
			}
			
			$supplierId = $supplierType = '';
			$sql = "SELECT supplierId, supplierType FROM purchasing_podetailsnew WHERE poNumber LIKE '".$poNumber."' LIMIT 1";
			$queryPodetailsNew = $db->query($sql);
			if($queryPodetailsNew AND $queryPodetailsNew->num_rows > 0)
			{
				$resultPodetailsNew = $queryPodetailsNew->fetch_assoc();
				$supplierId = $resultPodetailsNew['supplierId'];
				$supplierType = $resultPodetailsNew['supplierType'];
			}
			
			$type = $lotStatus;
			$supplyId = $partId;
			
			if($identifier==1)	$type = 2;
			
			$supplierAlias = '';
			if($supplierType!=2)
			{
				$sql = "SELECT supplierAlias FROM purchasing_supplier WHERE supplierId = ".$supplierId." LIMIT 1";
			}
			else
			{
				$sql = "SELECT subconAlias FROM purchasing_subcon WHERE subconId = ".$supplierId." LIMIT 1";
			}
			$querySupplier = $db->query($sql);
			if($querySupplier AND $querySupplier->num_rows > 0)
			{
				$resultSupplier = $querySupplier->fetch_row();
				$supplierAlias = $resultSupplier[0];
			}			
			
			$firstColumn = $secondColumn = array();
			if ($type == 1)
			{
				$poType = 'Material';
				$firstColumn[] = 'Supplier';
				$firstColumn[] = 'Material Type';
				$firstColumn[] = 'Thickness';
				$firstColumn[] = 'Length';
				$firstColumn[] = 'Width';
				
				$secondColumn[] = $supplierAlias;
				$secondColumn[] = $resultOpenPoList['dataOne'];
				$secondColumn[] = $resultOpenPoList['dataTwo'];
				$secondColumn[] = $resultOpenPoList['dataThree'];
				$secondColumn[] = $resultOpenPoList['dataFour'];
			}
			else if ($type == 2 OR $type == 5)
			{
				$productionTag = ($productionTag=='') ? $lotNumber : $productionTag;
				
				$customerId = '';
				$sql = "SELECT customerId FROM cadcam_parts WHERE partId = ".$partId." LIMIT 1";
				$queryParts = $db->query($sql);
				if($queryParts->num_rows > 0)
				{
					$resultParts = $queryParts->fetch_array();
					$customerId = $resultParts['customerId'];
				}
				
				$treatmentNameArray = array();
				$sql = "SELECT dataThree FROM purchasing_pocontents WHERE poNumber LIKE '".$poNumber."' AND poContentId IN(".$poContentIds.")";
				$queryTreatment = $db->query($sql);
				if($queryTreatment->num_rows > 0)
				{
					while($resultTreatment = $queryTreatment->fetch_array())
					{
						$treatmentName = $resultTreatment['dataThree'];
						if(in_array($customerId,array('45','49')) AND $treatmentName=='Passivation')	$treatmentName = 'JPS-3403AA TYPE vi';
						$treatmentNameArray[] = $treatmentName;
					}
					$dataThree = implode(", ",$treatmentNameArray);
				}
				
				$poType = 'Subcon';
				$firstColumn[] = 'Supplier';
				$firstColumn[] = 'Part Number';
				$firstColumn[] = 'Revision';
				$firstColumn[] = 'Treatment';
				$firstColumn[] = 'Lot Number';
				
				$secondColumn[] = $supplierAlias;
				$secondColumn[] = $resultOpenPoList['dataOne'];
				$secondColumn[] = $resultOpenPoList['dataTwo'];
				$secondColumn[] = $dataThree;
				$secondColumn[] = $lotNumber;
			}
			else if ($type == 3)
			{
				$poType = 'Supplies';
				$firstColumn[] = 'Supplier';
				$firstColumn[] = 'Item Name';
				$firstColumn[] = 'Description';
				$firstColumn[] = '';
				$firstColumn[] = '';
				
				$secondColumn[] = $supplierAlias;
				$secondColumn[] = $resultOpenPoList['dataOne'];
				$secondColumn[] = $resultOpenPoList['dataTwo'];
				$secondColumn[] = '';
				$secondColumn[] = '';		
			}
			else if ($type == 4)
			{
				$poType = 'Accessory';
				$firstColumn[] = 'Supplier';
				$firstColumn[] = 'Item Name';
				$firstColumn[] = 'Description';
				$firstColumn[] = '';
				$firstColumn[] = '';
				
				// ----- Remove accessoryNumber requested by Sir Demer 2016-06-23 ----- //
				if($resultOpenPoList['dataOne']=='7FD8010A48R01')
				{
					$secondColumn[] = $supplierAlias;
					$secondColumn[] = $resultOpenPoList['dataTwo'];
					$secondColumn[] = '-';
					$secondColumn[] = '';
					$secondColumn[] = '';
				}
				// ----- Remove accessoryNumber requested by Sir Demer 2016-06-23 ----- //
				else
				{
					$secondColumn[] = $supplierAlias;
					$secondColumn[] = $resultOpenPoList['dataOne'];
					$secondColumn[] = $resultOpenPoList['dataTwo'];
					$secondColumn[] = '';
					$secondColumn[] = '';
				}		
			}
			
			if(($index%6) == 0)
			{
				$pdf->AddPage();
				$pdf->SetLineStyle(array('dash' => '2'));
				// - - - - - - - - - - - - - - - - - - - - Creating Dash Lines - - - - - - - - - - - - - - - - - - - - //
				$counter = $boxWidth;
				while($counter < $paperWidth)
				{
					$pdf->Line($counter,0,$counter,$paperLength); //Horizontals
					$counter += $boxWidth;
				}
				
				$counter = $boxLength;
				while($counter < $paperLength)
				{
					$pdf->Line(0,$counter,$paperWidth,$counter); //Horizontals
					$counter += $boxLength;
				}
				// - - - - - - - - - - - - - - - - - - - End Creating Dash Lines - - - - - - - - - - - - - - - - - - - //
				$pdf->SetLineStyle(array('dash' => 0));
				$y = $top;
				$plusY = 0;
			}
			
			if(($index%2) == 0)
			{
				$x = $left;
				$y += $plusY;
				$w = $boxWidth - ($top * 2);
				
				$logoX = 65;
				$logoY = 16;
				
				$plusY = $boxLength;
			}
			else
			{
				$x = $left + $boxWidth;
			}
			
			// - - - - - - - - - - - - - - - - - ITEM TAG - - - - - - - - - - - - - - - //
			$pdf->Image($arkLogo,$x+$logoX,$y,($w)/3,'');//Logo
			
			$pdf->SetXY($x,$y);
			$pdf->SetFont('Helvetica','B',18);
			$pdf->Cell(25,5,$poNumber,0,0,'C');
			
			$pdf->SetXY($x,$y);
			$pdf->SetFont('Helvetica','B',21);
			$pdf->Cell($w,8,'ITEM TAG  ',0,0,'C');$pdf->Ln(11);
			$pdf->write1DBarcode($lotNumber, 'C39', $x, '', $w, 10, 0.4, $style, 'T');
			
			$pdf->SetFont('Helvetica','',12);
			$pdf->SetXY($x,$y+23);	$pdf->Cell(($w/4),$h,'Item Type','LTB',0,'L');		$pdf->Cell(($w/1.334),$h,$poType,'LTBR',0,'C'); 	$pdf->SetFont('Helvetica','',12); $pdf->ln();
			for ($i = 0; $i < 5 ; $i++)
			{
				$pdf->SetX($x);
				$pdf->Cell(($w/4),$h,$firstColumn[$i],'LB',0,'L');
				$pdf->MultiCell(($w/1.334),$h,$secondColumn[$i],1,'C',false,0,'','',true,0,false,true,$h,'M',true);$pdf->ln();
			}
			
			if($poNumber=='0008201')
			{
				$poQuantity = '50 rolls/box';
				//~ $unitName = '';
			}			
			
			$getY= $pdf->GetY();
			$pdf->SetXY($x,$getY+5);
			$pdf->SetFont('Helvetica','',10);
			$pdf->Cell(($w/3.3),$h,'PO Quantity',1,0,'C');
			$pdf->Cell(($w/3.3),$h,'Actual Quantity',1,0,'C');
			$pdf->ln();$pdf->SetX($x);
			$pdf->SetFont('Helvetica','',20);
			$pdf->Cell(($w/3.3),($h-1)*2,$poQuantity,1,0,'C');
			$pdf->Cell(($w/3.3),($h-1)*2,'',1,0,'C');
			
			$pdf->SetXY($x+66,$getY+5);
			$pdf->SetFont('Helvetica','',10);
			$pdf->Cell(($w/3.3),$h,'Quality Stamp',1,0,'C');
			$pdf->ln();$pdf->SetX($x+66);
			$pdf->SetFont('Helvetica','',20);
			$pdf->Cell(($w/3.3),($h-1)*2,'',1,0,'C');
			// - - - - - - - - - - - - - - - - - END ITEM TAG - - - - - - - - - - - - - - - //
			
			$index++;
		}
	}
	
	$pdf->Output();	
		
?>
