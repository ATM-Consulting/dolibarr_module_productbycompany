<?php
/* Copyright (C) 2019 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_productbycompany.class.php
 * \ingroup productbycompany
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */
require_once __DIR__ . '/../backport/v19/core/class/commonhookactions.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

/**
 * Class ActionsProductByCompany
 */
class ActionsProductByCompany extends \productbycompany\RetroCompatCommonHookActions
{
    /**
     * @var DoliDb		Database handler (result of a new DoliDB)
     */
    public $db;

	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
     * @param DoliDB    $db    Database connector
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{

	}

    public function printFieldListWhere($parameters, &$object, &$action, $hookmanager)
    {
        if ($parameters['currentcontext'] == 'productservicelist' && getDolGlobalString('PBC_USE_CUSTOM_REF_SEARCH_ON_PRODUCTLIST')) {
            $search_companyproductreference = GETPOST("search_companyproductreference", 'alpha');
            if (!empty($search_companyproductreference)) {
                $hookmanager->resPrint = " AND EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."product_by_company AS pbc WHERE pbc.fk_product = p.rowid AND pbc.ref LIKE CONCAT('%', '".$search_companyproductreference."', '%')) ";
            }
        }
        return 0;
    }
    
    public function printFieldListOption($parameters, &$object, &$action, $hookmanager)
	{
        if ($parameters['currentcontext'] == 'productservicelist' && getDolGlobalString('PBC_USE_CUSTOM_REF_SEARCH_ON_PRODUCTLIST')) {
            $search_companyproductreference = GETPOST("search_companyproductreference", 'alpha');

            print '<td class="liste_titre left">';
            print '<input class="flat" type="text" name="search_companyproductreference" size="12" value="'.dol_escape_htmltag($search_companyproductreference).'">';
            print '</td>';
        }
        return 0;
	}

    public function printFieldListTitle($parameters, &$object, &$action, $hookmanager)
	{
        if ($parameters['currentcontext'] == 'productservicelist' && getDolGlobalString('PBC_USE_CUSTOM_REF_SEARCH_ON_PRODUCTLIST')) {
            global $langs;

            $langs->load('productbycompany@productbycompany');

            $param = $parameters["param"];
            $sortfield = $parameters["sortfield"];
            $sortorder = $parameters["sortorder"];
            
            print_liste_field_titre($langs->trans("ProductRefByCompany"), $_SERVER["PHP_SELF"], "", "", $param, '', $sortfield, $sortorder, 'center nowrap ');
        }
        return 0;
	}

    public function printFieldListValue($parameters, &$object, &$action, $hookmanager)
	{
        if ($parameters['currentcontext'] == 'productservicelist' && getDolGlobalString('PBC_USE_CUSTOM_REF_SEARCH_ON_PRODUCTLIST')) {
            $product = $parameters["obj"];

            print '<td class="center nowraponall">';

            $sql = "SELECT ref FROM ".MAIN_DB_PREFIX."product_by_company WHERE fk_product = '".$product->rowid."'";
            $resql = $this->db->query($sql);
            if ($resql) {
                $num = $this->db->num_rows($resql);
                for ($i = 0; $i < $num; $i++) {
                    $dbObject = $this->db->fetch_object($resql);
                    if (!empty($dbObject->ref)) {
                        print $dbObject->ref."<br />";
                    }
                }
            }

            print '</td>';
        }
        return 0;
	}

	public function formEditProductOptions($parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $form, $conf;
		$langs->load('productbycompany@productbycompany');

		$TContext = explode(':', $parameters['context']);

		if (
			(getDolGlobalString('PBC_USE_CUSTOM_REF_CUSTOMER')  &&
				(in_array('propalcard', $TContext)
				|| in_array('invoicecard', $TContext)
				|| in_array('ordercard', $TContext))
			)
			||
			(getDolGlobalString('PBC_USE_CUSTOM_REF_SUPPLIER')  &&
				(in_array('supplier_proposalcard', $TContext)
				|| in_array('invoicesuppliercard', $TContext)
				|| in_array('ordersuppliercard', $TContext))
			)
		)
		{
			?>
			<a class="button" id="btnCustomRef">+ <?php echo $langs->trans('Customize'); ?></a>
			<br /><br />
			<fieldset id="js_fieldset" style="display: none; ">
				<legend><?php echo $langs->trans('CustomRef'); ?></legend>
				<div id="js_customref"></div>
			</fieldset>
			<script type="text/javascript">
				while ($('#btnCustomRef').prev('br').length > 0) $('#btnCustomRef').prev('br').remove();

                // afficher les champs ref et label + "checkbox mise à jour existant"

                $(document).ready(function(){

					$.ajax({
						url : "<?php echo dol_buildpath('/productbycompany/script/interface.php',1) ?>"
						,data:{
							get: 'getCustomRefEditFields'
							,id: '<?php echo $parameters['line']->id; ?>'
							,element_type: '<?php echo $parameters['line']->element; ?>'
							,fk_product: '<?php echo $parameters['line']->fk_product; ?>'
						}
						,method:"get"
					}).done(function(html){
						$("#js_customref").html(html);
					});

                    $('#btnCustomRef').on('click', function (e) {
                        e.preventDefault();
                        if($('#js_fieldset').is(':visible')) {
                            $('#js_fieldset').hide();
                            $("#customRefSelect").click();
                            $(this).html("+ <?php echo $langs->trans('Customize'); ?>");
                        }
                        else {
                            $('#js_fieldset').show();
                            $("#customRefSelect").click();
                            $(this).html("- <?php echo $langs->trans('Customize'); ?>");
                        }
                    })
                });
			</script>

			<?php

		}

	}

	/**
	 * Overloading the formCreateProductOptions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function formCreateProductOptions($parameters, &$object, &$action, $hookmanager)
	{

		global $langs, $form, $conf;
		$langs->load('productbycompany@productbycompany');

		$TContext = explode(':', $parameters['context']);

		if (
			getDolGlobalString('PBC_USE_CUSTOM_REF_CUSTOMER') &&
			(
				in_array('propalcard', $TContext)
				|| in_array('invoicecard', $TContext)
				|| in_array('ordercard', $TContext)
			)
		)
		{

			$jsConf = new stdClass();
			$jsConf->ajaxUrl = dol_buildpath('/productbycompany/script/interface.php',1);
			$jsConf->socid = $object->socid;
			$jsConf->langs = array(
				'Customize' => $langs->trans('Customize')
			);

			dol_include_once('/productbycompany/class/productbycompany.class.php');
			$pbc = new ProductByCompany($object->db);
			$pbc->fk_soc = $object->socid;
			$jsConf->countedCustomRef = $pbc->countSocCustomRef();


			?>
			<!-- START ProductByCompany -->

			<input type="text" list="customRefSearchFieldOptions" id="customRefSearchField"  placeholder="<?php echo $langs->trans('SearchForCustomerCustomRef'); ?>"  >
			<datalist id="customRefSearchFieldOptions"></datalist>
			<a class="button" id="btnCustomRef" style="display: none;">+ <?php echo $langs->trans('Customize'); ?></a>
			<fieldset id="js_fieldset" style="display: none;">
				<legend><?php echo $langs->trans('CustomRef'); ?></legend>
				<div id="js_customref"></div>
			</fieldset>
			<script type="text/javascript">

			// afficher les champs ref et label + "checkbox mise à jour existant"

            $(document).ready(function(){

				let jsConf = <?php echo json_encode($jsConf) ?>;

				/**
				 * add array element into select field
				 *
				 * @param {jQuery} target The select input jquery element
				 * @param {array} data an array of object
				 * @param {string} selected The current selected value
				 */
				let updateInputListOptions = function(target, data = false, selected = -1 )
				{
					/* Remove all options from the select list */
					target.empty();
					target.prop("disabled", true);

					if(Array.isArray(data))
					{
						/* Insert the new ones from the array above */
						for(var i= 0; i < data.length; i++)
						{
							let item = data[i];
							let newOption =  $('<option>', {
								value: item.ref,
								text : item.label,
								"data-fk_product":  item.fk_product,
								"data-origin_ref":  item.origin_ref,
								"data-ref":  item.ref
							});

							if(selected == item.id){
								newOption.prop('selected');
							}

							target.append(newOption);
						}

						if(data.length > 0){
							target.prop("disabled", false);
						}
					}
				}

				if(jsConf.countedCustomRef > 0){
					$('#customRefSearchField').show();
				}

				$('body').on('keyup keypress', '#customRefSearchField', function(e) {

					// on list click search for clicked list option
					if(!e.key){
						// it is possible to detect whether an option was typed or selected from the list.
						//   Both typing and <datalist> clicks trigger the input's keydown listener, but only keyboard events have a key property.
						//   So if a keydown is triggered having no key property, you know it was a click from the list
						let dataListClickedOption  = $("#customRefSearchFieldOptions").find("[value='" + e.target.value + "']");

						// applique la selection du produit
						if(dataListClickedOption != undefined){
							$("#search_idprod").val(dataListClickedOption.attr('data-origin_ref'));
							$("#idprod").val(dataListClickedOption.attr('data-fk_product')).trigger("change");
						}

						return;
					}

					if($(this).val().length <= 3){
						return;
					}

					if (e.key === 'Enter') {
						e.preventDefault();
						return false;
					}

					$.ajax({
						url : jsConf.ajaxUrl,
						dataType: 'json',
						data:{
							get: 'getProductFromCustomerCustomRef',
							ref_prod:$(this).val(),
							fk_soc:jsConf.socid
						},
						method:"get",
						success: function (response) {
							if(response.result > 0){
								updateInputListOptions($("#customRefSearchFieldOptions"), response.data);

								// if(response.data.length == 1){
								// 	// take first result : FBI fausse bonne idée
								// 	let firstProd = response.data[0];
								// 	$("#idprod").val(firstProd.fk_product).trigger("change");
								// }
							}else{
								updateInputListOptions($("#customRefSearchFieldOptions"), false);
							}
						},
						error: function (err) {
							console.error('ProductByCompagnyAjaxCallError');
						}
					});

				});


                $('#idprod').on('change', function(e){


					updateInputListOptions($("#customRefSearchFieldOptions"), false);

                    $('#btnCustomRef').show();
                    $('#js_fieldset').hide();

                    $.ajax({
                        url : jsConf.ajaxUrl
                        ,data:{
                            get: 'getCustomRefCreateFields'
                            ,id_prod:$(this).val()
							,fk_soc:jsConf.socid
                        }
                        ,method:"get"
                    }).done(function(html){
                        $("#js_customref").html(html);
                        if (! $('#js_fieldset').is(':visible')){
							$('#btnCustomRef').html("+ " + jsConf.langs.Customize)
						}
                    });
                });

                $('#btnCustomRef').on('click', function (e) {
                    e.preventDefault();
                    if($('#js_fieldset').is(':visible')) {
                        $('#js_fieldset').hide();
                        $("#customRefSelect").click()
                    }
                    else {
                        $('#js_fieldset').show();
                        $("#customRefSelect").click()
					}
				})
            });
			</script>
			<!-- END ProductByCompany -->

			<?php
		}
	}

	public function formCreateProductSupplierOptions($parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $form, $conf;
		$langs->load('productbycompany@productbycompany');

		$TContext = explode(':', $parameters['context']);

		if (
			getDolGlobalString('PBC_USE_CUSTOM_REF_SUPPLIER') &&
			(
				in_array('supplier_proposalcard', $TContext)
				|| in_array('invoicesuppliercard', $TContext)
				|| in_array('ordersuppliercard', $TContext)
			)
		)
		{
			?>
			<!-- START ProductByCompany -->
			<a class="button" id="btnCustomRef" style="display: none;">+ <?php echo $langs->trans('Customize'); ?></a>
			<fieldset id="js_fieldset" style="display: none;">
				<legend>Personnalisation</legend>
				<div id="js_customref"></div>
			</fieldset>
			<script type="text/javascript">

                // afficher les champs ref et label + "checkbox mise à jour existant"

                $(document).ready(function(){

                    $('#idprodfournprice').on('change', function(e){

                        $('#btnCustomRef').show();
                        $('#js_fieldset').hide();

						var $val = $(this).val();
						var isPrice = 1;

						if (isNaN($val)){
							$val = $(this).val().substr(7);
							isPrice = 0;
						}

                        $.ajax({
                            url : "<?php echo dol_buildpath('/productbycompany/script/interface.php',1) ?>"
                            ,data:{
                                get: 'getCustomRefCreateFields'
                                ,id_prod:$val
                                ,fk_soc:<?php echo $object->socid; ?>
                                ,isPrice: isPrice
                            }
                            ,method:"get"
                        }).done(function(html){
                            $("#js_customref").html(html);
                        });
                    });

                    $('#btnCustomRef').on('click', function (e) {
                        e.preventDefault();
                        if($('#js_fieldset').is(':visible')) {
                            $('#js_fieldset').hide();
                            $("#customRefSelect").click()
                        }
                        else {
                            $('#js_fieldset').show();
                            $("#customRefSelect").click()
                        }
                    })
                });
			</script>
			<!-- END ProductByCompany -->
			<?php
		}
	}

	public function pdf_writelinedesc($parameters, &$object, &$action, $hookmanager)
	{
		global $db, $conf;

		$TContext = explode(':', $parameters['context']);

		if (
			in_array('pdfgeneration', $TContext)
			&& (
				(getDolGlobalString('PBC_USE_CUSTOM_REF_CUSTOMER')  &&
					(
						$object->element == 'propal'
						|| $object->element == 'commande'
						|| $object->element == 'facture'
					)
				)
				||
				(getDolGlobalString('PBC_USE_CUSTOM_REF_SUPPLIER')  &&
					(
						$object->element == 'supplier_proposal'
						|| $object->element == 'order_supplier'
						|| $object->element == 'invoice_supplier'
					)
				)
			)
		)
		{
			foreach($parameters as $key => $value) {
				$$key = $value;
			}

			dol_include_once('/productbycompany/class/productbycompany.class.php');
			$pbc_det = new ProductByCompanyDet($db);
			$pbc_det->origin_type = $object->lines[$i]->element;
			$pbc_det->fk_origin = $object->lines[$i]->id;

			$existing = $pbc_det->alreadyExists();
			if ($existing > 0)
			{
				$pbc_det->fetch($existing);
				$object->lines[$i]->label = $pbc_det->label;
				$object->lines[$i]->fk_product = 0;
				$desc = $pbc_det->ref.' - '.pdf_getlinedesc($object, $i, $outputlangs, $hideref, $hidedesc, $issupplierline);
//				var_dump($desc, $pbc_det->ref, pdf_getlinedesc($object, $i, $outputlangs, $hideref, $hidedesc, $issupplierline)); exit;

				$pdf->writeHTMLCell($w, $h, $posx, $posy, $outputlangs->convToOutputCharset($desc), 0, 1, false, true, 'J',true);

				$this->resprints = $desc;
				return 1;
			}
		}

	}
}
