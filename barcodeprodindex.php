<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       barcodeprod/barcodeprodindex.php
 *	\ingroup    barcodeprod
 *	\brief      Home page of barcodeprod top menu
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

require_once DOL_DOCUMENT_ROOT.'/core/lib/format_cards.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formbarcode.class.php';


// Load translation files required by the page
$langs->loadLangs(array("barcodeprod@barcodeprod"));

$action = GETPOST('action', 'aZ09');


// Security check
if (! $user->rights->barcodeprod->barcodeprod->read) {
 	accessforbidden();
 }
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

$max = 5;
$now = dol_now();
//Declaramos las variables a imprimir
$forbarcode = GETPOST('forbarcode', 'alphanohtml');
$forbarcode_label = GETPOST('etiquetaproducto', 'alphanohtml');
$forbarcode_price_tcc = GETPOST('preciotcc', 'double');
$forbarcode_min_price_tcc = GETPOST('$forbarcode_min_price_tcc', 'double');
$fk_barcode_type = GETPOST('fk_barcode_type', 'int');

/*
 * Actions
 */

$producttmp = new Product($db);
if (GETPOST('submitproduct') && GETPOST('submitproduct')) {
	$action = ''; // We reset because we don't want to build doc
	if (GETPOST('productid', 'int') > 0) {
		$result = $producttmp->fetch(GETPOST('productid', 'int'));
		if ($result < 0) {
			setEventMessage($producttmp->error, 'errors');
		}
		$forbarcode = $producttmp->barcode;
		$fk_barcode_type = $producttmp->barcode_type;
		$forbarcode_label = $producttmp->label;
		$forbarcode_price_tcc = $producttmp->price_ttc; //Unit price inc VAT
		$forbarcode_min_price_tcc = $producttmp->price_min_ttc; //Unit price no VAT
		if (empty($fk_barcode_type) && !empty($conf->global->PRODUIT_DEFAULT_BARCODE_TYPE)) {
			$fk_barcode_type = $conf->global->PRODUIT_DEFAULT_BARCODE_TYPE;
		}
		
		if (empty($forbarcode) || empty($fk_barcode_type)) {
			setEventMessages($langs->trans("DefinitionOfBarCodeForProductNotComplete", $producttmp->getNomUrl()), null, 'warnings');
		}
	}
}

/*
 * View
 */
?>

<style>

</style>

<?php
$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("BarcodeprodArea"));

print load_fiche_titre($langs->trans("BarcodeprodArea"), '', 'barcode');

print '<div class="fichecenter"><div class="fichethirdleft">';


print '<form id="form" action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<input type="hidden" name="action" value="builddocument">';
print '<input type="hidden" name="token" value="'.currentToken().'">';

print '<br>';

//Search Form Product
print '<i class="fas fa-search-plus"></i>';
$form->select_produits(GETPOST('productid', 'int'), 'productid', '', '', 0, -1, 2, '', 0, array(), 0, '1', 0, 'minwidth400imp', 1);

print '<p>';
print '<input type="submit" id="submitproduct" 
name="submitproduct" class="button" value="'.(dol_escape_htmltag($langs->trans("btnobtener"))).'">';
print '';
print '</p>';

?>
<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {


	$( "#productid" ).change(function (event) {
		event.preventDefault();
		$("#etiquetaproducto").val('');
		$("#preciotcc").val('');
		$("#forbarcode").val('');
		$("#extratxt").val('');
	  });
	  
	function openWindows(url)
	{
		var params = "location=no,toolbar=no,menubar=no,width=1200,height=1200,left=100,top=100";
		var newWindow = window.open('<?php echo DOL_URL_ROOT;?>/document.php?modulepart=barcodeprod&file='+url,'barcodeprod', params);
		return newWindow;
	}
		
	$( "#submitform" ).click(function() {
		var selectedbarcode='';
		etiquetaproducto=$("#etiquetaproducto").val();
		preciotcc =$("#preciotcc").val();
		fkbarcodetype= $("#select_fk_barcode_type option:selected").text();
		forbarcode=$("#forbarcode").val();
		extratxt=$("#extratxt").val();
		$.ajax({
			type: 'POST',
			url: '<?php echo DOL_URL_ROOT;?>/custom/barcodeprod/printfile.php?action=printbarcode',
			data:{etiquetaproducto:etiquetaproducto,preciotcc:preciotcc,fkbarcodetype:fkbarcodetype,forbarcode:forbarcode,extratxt:extratxt},
			success: function(data) {

				try {
	        		var jsonData = JSON.parse(data);
					
	        		switch (jsonData.success) {
					case 1:
						    $('#txtresult').html('');
						    openWindows(jsonData.url);
						break;
					default:
						break;
					}	
				} catch (e) {
					$('#txtresult').html('<?php echo dol_escape_htmltag($langs->trans("responsetxt")); ?>').addClass("error0 highlight");
				}

			},
			error:function(err) {
				alert("error"+JSON.stringify(err));
			}
		});
	});
		
	function init_gendoc_button()
	{
		if (jQuery("#select_fk_barcode_type").val() > 0 && jQuery("#forbarcode").val())
		{
			jQuery("#submitform").removeAttr("disabled");
		}
		else
		{
			jQuery("#submitform").prop("disabled", true);
		}
	}
	init_gendoc_button();
	jQuery("#select_fk_barcode_type").change(function() {
		init_gendoc_button();
	});
	jQuery("#forbarcode").keyup(function() {
		init_gendoc_button()
	});

});
</script>
<?php
// Barcode price label
print '<div class="tagtable">';
print '	<div class="tagtr">';
print '	<div class="tagtd" style="overflow: hidden; white-space: nowrap; max-width: 600px;">';
print $langs->trans("EtiquetaProducto").' &nbsp; ';
print '</div><div class="tagtd" style="overflow: hidden; white-space: nowrap; max-width: 600px;">';
print '<input size="35" type="text" name="etiquetaproducto" id="etiquetaproducto" value="'.$forbarcode_label.'">';
print '</div></div>';

// Barcode price tcc
print '	<div class="tagtr">';
print '	<div class="tagtd" style="overflow: hidden; white-space: nowrap; max-width: 300px;">';
print $langs->trans("PrecioTcc").' &nbsp; ';
print '</div><div class="tagtd" style="overflow: hidden; white-space: nowrap; max-width: 300px;">';
print '<input size="35" type="text" name="preciotcc" id="preciotcc" value="'.number_format(floatval($forbarcode_price_tcc), 2, ',',' ').'">';
print '</div></div>';

// Barcode type
print '	<div class="tagtr">';
print '	<div class="tagtd" style="overflow: hidden; white-space: nowrap; max-width: 300px;">';
print $langs->trans("BarcodeType").' &nbsp; ';
print '</div><div class="tagtd" style="overflow: hidden; white-space: nowrap; max-width: 300px;">';
$formbarcode = new FormBarCode($db);
print $formbarcode->selectBarcodeType($fk_barcode_type, 'fk_barcode_type', 1);
print '</div></div>';

// Barcode value
print '	<div class="tagtr">';
print '	<div class="tagtd" style="overflow: hidden; white-space: nowrap; max-width: 300px;">';
print $langs->trans("BarcodeValue").' &nbsp; ';
print '</div><div class="tagtd" style="overflow: hidden; white-space: nowrap; max-width: 300px;">';
print '<input size="35" type="text" name="forbarcode" id="forbarcode" value="'.$forbarcode.'">';
print '</div></div>';
print '<br>';

print '	<div class="tagtr">';
print '	<div class="tagtd" style="overflow: hidden; white-space: nowrap; max-width: 500px;">';
print $langs->trans("InfoAdicional").' &nbsp; ';
print '</div><div class="tagtd" style="overflow: hidden; white-space: nowrap; max-width: 500px;">';
print '<input size="35" type="text" id="extratxt" placeholder="Menos de 35 carácteres" maxlength="35">';
print '</div></div>';
print '<br>';


print '</div>';
//btn
print '<br><input size="40" class="button" id="submitform" '.((GETPOST("selectorforbarcode") && GETPOST("selectorforbarcode")) ? '' : 'disabled ').' value="'.$langs->trans("BuildPageToPrint").'">';

print '</form>';
print '<br>';
print '<div id="txtresult"></div>';
print '<div class="fichetwothirdright"></div>';


// End of page
llxFooter();
$db->close();
