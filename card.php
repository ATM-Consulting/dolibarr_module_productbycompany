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

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
dol_include_once('productbycompany/class/productbycompany.class.php');
dol_include_once('productbycompany/lib/productbycompany.lib.php');

if(!$user->hasRight('productbycompany', 'read')) accessforbidden();
$permissiondellink = $user->hasRight('productbycompany', 'write');	// Used by the include of actions_dellink.inc.php

$langs->load('productbycompany@productbycompany');
$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];

$object = new ProductByCompany($db);

$action = GETPOST('action');
$cancel = GETPOST('cancel');
$origin_id = GETPOST('origin_id');
$type = GETPOST('type');
$id = GETPOST('id');
$confirm = GETPOST('confirm');
$fk_soc = GETPOST('fk_soc');
$fk_product = GETPOST('fk_product');

$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'productbycompanycard';   // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');

if (empty($origin_id) || !in_array($type, array('product', 'company')))
{
    accessforbidden();
}
elseif ($type === 'product')
{
    require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
    require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';

    $langs->loadLangs(array('products', 'other'));

    $origin_object = new Product($db);
    $origin_object->fetch($origin_id);

    $head = product_prepare_head($origin_object);
    $picto = ($origin_object->type== Product::TYPE_SERVICE?'service':'product');
    $title = $langs->trans("CardProduct".$origin_object->type);
}
else
{
    require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
    require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
    require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';

    $langs->loadLangs(array("companies","commercial"));

    $origin_object = new Societe($db);
    $origin_object->fetch($origin_id);

    $head = societe_prepare_head($origin_object);
    $picto = 'company';
    $title = $langs->trans('ThirdParty');
}

$object->fetch($id);
$object->origin_id = $origin_id;
$object->origin_type = $type;

$hookmanager->initHooks(array('productbycompanycard', 'globalcard'));


if ($object->isextrafieldmanaged)
{
    $extrafields = new ExtraFields($db);

    $extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
    $search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');
}

// Initialize array of search criterias
//$search_all=trim(GETPOST("search_all",'alpha'));
//$search=array();
//foreach($origin_object->fields as $key => $val)
//{
//    if (GETPOST('search_'.$key,'alpha')) $search[$key]=GETPOST('search_'.$key,'alpha');
//}

/*
 * Actions
 */

$parameters = array('origin_id' => $origin_id, 'ref' => $ref, 'productbycompany' => $object);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $origin_object, $action); // Note that $action and $origin_object may have been modified by some
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

// Si vide alors le comportement n'est pas remplacé
if (empty($reshook))
{
//var_dump($cancel, $backtopage);
    if ($cancel)
    {
        if (! empty($backtopage))
        {
            header("Location: ".$backtopage);
            exit;
        }
        $action='';
    }

    // For object linked
    include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';		// Must be include, not include_once

    $error = 0;
	switch ($action) {
		case 'add':
		case 'update':
        	$object->setValues($_REQUEST); // Set standard attributes
            if ($object->isextrafieldmanaged)
            {
                $ret = $extrafields->setOptionalsFromPost($extralabels, $object);
                if ($ret < 0) $error++;
            }

            if ($action == 'add')
			{
				$exists = $object->alreadyExists();
				if ($exists > 0)
				{
					setEventMessages($langs->trans('RecordAlreadyExists'), '', 'errors');
					header('Location: '.dol_buildpath('/productbycompany/list.php', 1).'?id='.$origin_object->id.'&type='.$type);
					exit;
				}
			}

			if (empty($object->ref))
			{
				setEventMessage('RefIsRequired', 'errors');
				$error++;
			}

			if ($error > 0)
			{
				$action = 'edit';
				break;
			}

			$res = $object->save($user);
            if ($res < 0)
            {
                setEventMessage($object->errors, 'errors');
                if (empty($object->id)) $action = 'create';
                else $action = 'edit';
            }
            else
            {
                header('Location: '.dol_buildpath('/productbycompany/list.php', 1).'?id='.$origin_object->id.'&type='.$type);
                exit;
            }
        case 'update_extras':

            $object->oldcopy = dol_clone($object);

            // Fill array 'array_options' with data from update form
            $ret = $extrafields->setOptionalsFromPost($extralabels, $object, GETPOST('attribute', 'none'));
            if ($ret < 0) $error++;

            if (! $error)
            {
                $result = $object->insertExtraFields('PRODUCTBYCOMPANY_MODIFY');
                if ($result < 0)
                {
                    setEventMessages($object->error, $object->errors, 'errors');
                    $error++;
                }
            }

            if ($error) $action = 'edit_extras';
            else
            {
				header('Location: '.dol_buildpath('/productbycompany/list.php', 1).'?id='.$origin_object->id.'&type='.$type);
				exit;
            }
            break;
		case 'confirm_delete':
			if ($confirm != 'yes') {$action = ''; break;}
			$object->setValues($_REQUEST);
			$res = $object->delete($user);
			if ($res < 0)
			{
				setEventMessages($object->error, $object->errors, "errors");
				$error++;
			}

			if (!$error)
			{
				header('Location: '.dol_buildpath('/productbycompany/list.php', 1).'?id='.$origin_object->id.'&type='.$type);
				exit;
			}
			break;
	}
}

/**
 * View
 */
$form = new Form($db);

//$title=$langs->trans('ProductByCompany');
llxHeader('', $title);

if ($action == 'create')
{
	$picto = $type === 'product' ? 'product' : 'company';
	dol_fiche_head($head, 'productbycompanytab', ($type === 'product' ? $langs->trans('Product') : $langs->trans('ThirdParty')), 0, $picto);

	$paramid = $type == 'product' ? 'ref' : 'origin_id';
	$fieldid = $type == 'product' ? 'ref' : 'rowid';

	dol_banner_tab($origin_object, $paramid, '', 0, $fieldid, 'ref', '', '&type='.$type);
	print '<div class="underbanner clearboth"></div>';

    print load_fiche_titre($langs->trans('NewProductByCompany'), '', 'title_generic.png');

    print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
    print '<input type="hidden" name="token" value="'.$newToken.'">';
    print '<input type="hidden" name="action" value="add">';
    print '<input type="hidden" name="origin_id" value="'.$origin_id.'">';
    print '<input type="hidden" name="type" value="'.$type.'">';
    print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

    print '<table class="border centpercent">'."\n";

    // Common attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

    // Other attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

    print '</table>'."\n";

    dol_fiche_end();

    print '<div class="center">';
    print '<input type="submit" class="button" name="add" value="'.dol_escape_htmltag($langs->trans('Create')).'">';
    print '&nbsp; ';
    print '<input type="'.($backtopage?"submit":"button").'" class="button" name="cancel" value="'.dol_escape_htmltag($langs->trans('Cancel')).'"'.($backtopage?'':' onclick="javascript:history.go(-1)"').'>';	// Cancel for create does not post form if we don't know the backtopage
    print '</div>';

    print '</form>';

	//hideUselessFields($type, $fk_product, $fk_soc, $origin_object);

}
else
{

    if (empty($origin_object->id))
    {
        $langs->load('errors');
        print $langs->trans('ErrorRecordNotFound');
    }
    else
    {
        if (!empty($origin_object->id) && $action === 'edit')
        {
            print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
            print '<input type="hidden" name="token" value="'.$newToken.'">';
            print '<input type="hidden" name="action" value="update">';
            print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
            print '<input type="hidden" name="origin_id" value="'.$origin_object->id.'">';
            print '<input type="hidden" name="type" value="'.$type.'">';
            print '<input type="hidden" name="id" value="'.$id.'">';

            if ($type == 'product') $head = product_prepare_head($origin_object);
            else $head = societe_prepare_head($origin_object);

            $picto = $type === 'product' ? 'product' : 'company';
            dol_fiche_head($head, 'productbycompanytab', ($type === 'product' ? $langs->trans('Product') : $langs->trans('ThirdParty')), 0, $picto);

			$paramid = $type == 'product' ? 'ref' : 'origin_id';
			$fieldid = $type == 'product' ? 'ref' : 'rowid';

			dol_banner_tab($origin_object, $paramid, '', 0, $fieldid, 'ref', '', '&type='.$type);
			print '<div class="underbanner clearboth"></div>';

            print '<table class="border centpercent">'."\n";

            // Common attributes
            include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

            // Other attributes
            include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

            print '</table>';

            dol_fiche_end();

            print '<div class="center"><input type="submit" class="button" name="save" value="'.$langs->trans('Save').'">';
            print ' &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'">';
            print '</div>';

            print '</form>';

			//hideUselessFields($type, $fk_product, $fk_soc, $origin_object);

        }
        elseif ($origin_object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create')))
        {
            dol_fiche_head($head, 'productbycompanytab', $title, -1, $picto);

            $formconfirm = getFormConfirmProductByCompany($form, $object, $action);
            if (!empty($formconfirm)) print $formconfirm;


            //$linkback = '<a href="' .dol_buildpath('/productbycompany/list.php', 1) . '?restore_lastsearch_values=1">' . $langs->trans('BackToList') . '</a>';

            //$morehtmlref='<div class="refidno">';
            /*
            // Ref bis
            $morehtmlref.=$form->editfieldkey("RefBis", 'ref_client', $origin_object->ref_client, $origin_object, $user->rights->productbycompany->write, 'string', '', 0, 1);
            $morehtmlref.=$form->editfieldval("RefBis", 'ref_client', $origin_object->ref_client, $origin_object, $user->rights->productbycompany->write, 'string', '', null, null, '', 1);
            // Thirdparty
            $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $soc->getNomUrl(1);
            */
            //$morehtmlref.='</div>';


            $morehtmlstatus.=''; //$origin_object->getLibStatut(2); // pas besoin fait doublon
            dol_banner_tab($origin_object, '', '', ($user->socid?0:1));

            print '<div class="fichecenter">';

//            print '<div class="fichehalfleft">'; // Auto close by commonfields_view.tpl.php
            print '<div class="underbanner clearboth"></div>';
            print '<table class="border tableforfield" width="100%">'."\n";


            if ($type === 'product')
            {
                print '<tr>';
                print '<td class="titlefield fieldrequired">'.$langs->trans($object->fields['fk_soc']['label']).'</td>';
                print '<td>'.$object->showOutputField($object->fields['fk_soc'], 'fk_soc', $object->fk_soc, '', '', '', 0).'</td>';
                print "</tr>\n";
            }
            else
            {
                print '<tr>';
                print '<td class="titlefield fieldrequired">'.$langs->trans($object->fields['fk_product']['label']).'</td>';
                print '<td>'.$object->showOutputField($object->fields['fk_product'], 'fk_product', $object->fk_product, '', '', '', 0).'</td>';
                print "</tr>\n";
            }

            print '<tr>';
            print '<td class="titlefield">'.$langs->trans($object->fields['ref']['label']).'</td>';
            print '<td>'.$object->showOutputField($object->fields['ref'], 'ref', $object->ref, '', '', '', 0).'</td>';
            print "</tr>\n";

            print '<tr>';
            print '<td class="titlefield">'.$langs->trans($object->fields['label']['label']).'</td>';
            print '<td>'.$object->showOutputField($object->fields['label'], 'label', $object->label, '', '', '', 0).'</td>';
            print "</tr>\n";

            // Common attributes
            //$keyforbreak='fieldkeytoswithonsecondcolumn';
//            include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';

            // Other attributes
//            include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

            print '</table>';

//            print '</div></div>'; // Fin fichehalfright & ficheaddleft
            print '</div>'; // Fin fichecenter

            print '<div class="clearboth"></div><br />';

            print '<div class="tabsAction">'."\n";
            $parameters=array();
            $reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $origin_object, $action);    // Note that $action and $origin_object may have been modified by hook
            if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

            if (empty($reshook))
            {
                if ($user->hasRight('productbycompany', 'write'))
                {
                    // Modify
                    print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?origin_id='.$origin_object->id.'&type='.$type.'&id='.$object->id.'&action=edit">'.$langs->trans("ProductByCompanyModify").'</a></div>'."\n";

                    // Delete
                    print '<div class="inline-block divButAction"><a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?origin_id='.$origin_object->id.'&type='.$type.'&id='.$object->id.'&action=delete">'.$langs->trans("ProductByCompanyDelete").'</a></div>'."\n";

                }
                else
                {

                    // Modify
                    print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("ProductByCompanyModify").'</a></div>'."\n";

                    // Delete
                    print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("ProductByCompanyDelete").'</a></div>'."\n";
                }
            }
            print '</div>'."\n";

            dol_fiche_end(-1);
        }
    }
}

function hideUselessFields($type, $fk_product, $fk_soc, $origin_object)
{

	// cacher les champs inutiles
	$trtochange = '';
	if ($type == 'company' && !empty($fk_soc))
	{
		$trtochange = '#field_fk_soc';
		$input = '#fk_soc';
	}
	else if ($type == 'product' && !empty($fk_product))
	{
		$trtochange = '#field_fk_product';
		$input = '#fk_product';
	}

	if (!empty($trtochange))
	{
		$nomUrl = $origin_object->getNomUrl(1);
		?>
		<script type="text/javascript">
            $(document).ready(function(){
                var trtochange = '<?php echo $trtochange; ?>';
                var input = '<?php echo $input; ?>';

                // create
                $(trtochange).children(':last-child').find('.select2').hide();
                $(trtochange).children(':last-child').append($('<?php echo $nomUrl; ?>'));

                // edit
				$(input).next().hide();
				$(input).parent().append($('<?php echo $nomUrl; ?>'));
            });
		</script>
		<?php
	}
}

llxFooter();
$db->close();
