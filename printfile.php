<?php

/**
*	\file       .php
*	\ingroup    doafip
*	\brief      PDF
*/

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once('lib/tcpdf/tcpdf.php');
require_once  DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

$action = GETPOST('action', 'aZ09');

if (! $user->rights->barcodeprod->barcodeprod->read) {
	accessforbidden();
}
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

$etiquetaproducto= GETPOST('etiquetaproducto', 'alphanohtml');
$preciotcc = GETPOST('preciotcc', 'alphanohtml');
$fkbarcodetype=GETPOST('fkbarcodetype', 'alphanohtml');
$forbarcode= GETPOST('forbarcode', 'alphanohtml');
$extratxt= GETPOST('extratxt', 'alphanohtml');
$qty = GETPOST('qty','int');
/*
* Actions
*/

try {
	if ($action == 'printbarcode' ) {
	    
		// create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);

		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $etiquetaproducto);
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		$pdf->SetFont('helvetica', '', 13);

		// define barcode style
		$styleEAN13 = array(
		'position' => '',
		'align' => 'C',
		'stretch' => false,
		'fitwidth' => true,
		'cellfitalign' => '',
		'border' => false,
		'hpadding' => 'auto',
		'vpadding' => 'auto',
		'fgcolor' => array(0,0,0),
		'bgcolor' => false, //array(255,255,255),
		'text' => true,
		'font' => 'helvetica',
		'fontsize' => 8,
		'stretchtext' => 4
		);
		
		$styleCODE128 = array(
				'position' => '',
				'align' => 'C',
				'stretch' => false,
				'fitwidth' => true,
				'cellfitalign' => '',
				'border' => false,
				'hpadding' => 'auto',
				'vpadding' => 'auto',
				'fgcolor' => array(0,0,0),
				'bgcolor' => false, //array(255,255,255),
				'text' => true,
				'font' => 'helvetica',
				'fontsize' => 8,
				'stretchtext' => 4
		);
		
		$styleCODABAR = array(
				'position'=>'S', 
				'border'=>false, 
				'padding'=>2, 
				'fgcolor'=> array(0,0,0), 
				'bgcolor'=>array(255,255,255), 
				'text'=>true, 
				'font'=>'helvetica', 
				'fontsize'=>6, 
				'stretchtext'=>4
		);
		

		

		$pdf->AddPage();
	
		$pdf->resetColumns();
		$pdf->setEqualColumns(2, 89);
		$pdf->selectColumn();
			
			/*$pdf->Cell(0, 0, $etiquetaproducto, 0, 1);
			$pdf->Cell(0, 0,"$ ".$preciotcc, 0, 1);
			$pdf->write1DBarcode( $forbarcode , 'EAN13', '', '', '', 18, 0.4, $style, 'N');
			$pdf->Ln();*/
		
		
		
		if ($fkbarcodetype=="UPC") {
		$html = '';
		for ($i = 0; $i < $qty; $i++) {
			$html.= '';
			$html.=

				$pdf->write1DBarcode( $forbarcode , 'CODABAR', '', '', '50', 28, 0.4, $styleCODABAR, 'T');

				$pdf->SetFont('', '', 8);
				$pdf->MultiCell(39, 10, $etiquetaproducto, '', 'C', false, 1, null, null, true, 0, false, true, 0, 'M', true);

				if(strlen($preciotcc) <= 7){
					$pdf->SetFont('', 'B', 24);
				}else if(strlen($preciotcc) > 7 && strlen($preciotcc) < 12 ){
					$pdf->SetFont('', 'B', 18);
				}else{
					$pdf->SetFont('', 'B', 14);
				}
				$pdf->MultiCell(0, 18,"$ ".$preciotcc, '', 'R', false, 1, null, null, true, 0, false, true, 0, 'M', false);
				
				if(!empty($extratxt)){
					$pdf->SetFont('', '', 12);
					$pdf->MultiCell(0, 0, $extratxt, 'T', 'C', false, 1, null, null, true, 0, false, true, 0, 'M', false);
				}

			$html.= $pdf->Ln();
			$html.= '';
		};
		$html.= '';	
			;
		};
		
		if ($fkbarcodetype=="EAN13") {
		$html = '';
			for ($i = 0; $i < $qty; $i++) {
				$html.= '';
				
				$html.= 
					$pdf->write1DBarcode( $forbarcode , 'EAN13', '', '', '50', 28, 0.4, $styleEAN13, 'T');
					$pdf->SetFont('', '', 8);
					$pdf->MultiCell(43, 10, $etiquetaproducto, '', 'C', false, 1, null, null, true, 0, false, true, 0, 'M', true);

					if(strlen($preciotcc) <= 7){
						$pdf->SetFont('', 'B', 24);
					}else if(strlen($preciotcc) > 7 && strlen($preciotcc) < 12 ){
						$pdf->SetFont('', 'B', 18);
					}else{
						$pdf->SetFont('', 'B', 14);
					}
					$pdf->MultiCell(0, 18,"$ ".$preciotcc, '', 'R', false, 1, null, null, true, 0, false, true, 0, 'M', false);
					
					if(!empty($extratxt)){
						$pdf->SetFont('', '', 12);
						$pdf->MultiCell(0, 0, $extratxt, 'T', 'C', false, 1, null, null, true, 0, false, true, 0, 'M', false);
					}
				
				$html.= $pdf->Ln();
				$html.= '';
			};
		$html.= '';	
		}
		
		if($fkbarcodetype=="Code 128"){
			$html = '';
			for ($i = 0; $i < $qty; $i++) {
				$html.= '';
				
				$html.= $pdf->write1DBarcode( $forbarcode , 'C128', '', '', '50', 28, 0.4, $styleCODE128, 'T');
				$pdf->SetFont('', '', 8);
				$pdf->MultiCell(39, 10, $etiquetaproducto, '', 'C', false, 1, null, null, true, 0, false, true, 0, 'M', true);

				if(strlen($preciotcc) <= 7){
					$pdf->SetFont('', 'B', 24);
				}else if(strlen($preciotcc) > 7 && strlen($preciotcc) < 12 ){
					$pdf->SetFont('', 'B', 18);
				}else{
					$pdf->SetFont('', 'B', 14);
				}
				$pdf->MultiCell(0, 18,"$ ".$preciotcc, '', 'R', false, 1, null, null, true, 0, false, true, 0, 'M', false);
				
				if(!empty($extratxt)){
					$pdf->SetFont('', '', 12);
					$pdf->MultiCell(0, 0, $extratxt, 'T', 'C', false, 1, null, null, true, 0, false, true, 0, 'M', false);
				}

				$html.= $pdf->Ln();
				$html.= '';
			};
			$html.= '';	
		}
		
		//$html= '<p>'.$fkbarcodetype.'</p>';
		
		$pdf->writeHTML($html, true, 0, true, 0);	
		$pdf->resetColumns();
		$pdf->lastPage();
		
		ob_end_clean();ob_start();
		$pdf->Output($dolibarr_main_data_root.'/barcodeprod/'.$forbarcode.'.pdf', 'F');
		echo json_encode(array('url' => $forbarcode.'.pdf', 'success' => 1 ));
	}
} catch (Exception $e) {
	//print_r($e);
	echo json_encode(array('success' => 666,'code'=>$e->getCode(),'msj'=> $e->getMessage()));
}



















