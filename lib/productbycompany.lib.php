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
 *	\file		lib/productbycompany.lib.php
 *	\ingroup	productbycompany
 *	\brief		This file is an example module library
 *				Put some comments here
 */

/**
 * @return array
 */
function productbycompanyAdminPrepareHead()
{
    global $langs, $conf, $object;

    $langs->load('productbycompany@productbycompany');

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/productbycompany/admin/productbycompany_setup.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;
    /*$head[$h][0] = dol_buildpath("/productbycompany/admin/productbycompany_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFields");
    $head[$h][2] = 'extrafields';
    $h++;*/
    $head[$h][0] = dol_buildpath("/productbycompany/admin/productbycompany_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@productbycompany:/productbycompany/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@productbycompany:/productbycompany/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'productbycompany');

    return $head;
}

/**
 * Return array of tabs to used on pages for third parties cards.
 *
 * @param 	ProductByCompany	$object		Object company shown
 * @return 	array				Array of tabs
 */
function productbycompany_prepare_head(ProductByCompany $object)
{
    global $langs, $conf;
    $h = 0;
    $head = array();
    $head[$h][0] = dol_buildpath('/productbycompany/card.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("ProductByCompanyCard");
    $head[$h][2] = 'card';
    $h++;

	// Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@productbycompany:/productbycompany/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@productbycompany:/productbycompany/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'productbycompany');

	return $head;
}

/**
 * @param Form      $form       Form object
 * @param ProductByCompany  $object     ProductByCompany object
 * @param string    $action     Triggered action
 * @return string
 */
function getFormConfirmProductByCompany($form, $object, $action)
{
    global $langs, $user;

    $formconfirm = '';

    if ($action === 'valid' && $user->hasRight('productbycompany', 'write'))
    {
        $body = $langs->trans('ConfirmValidateProductByCompanyBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmValidateProductByCompanyTitle'), $body, 'confirm_validate', '', 0, 1);
    }
    elseif ($action === 'accept' && $user->hasRight('productbycompany', 'write'))
    {
        $body = $langs->trans('ConfirmAcceptProductByCompanyBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmAcceptProductByCompanyTitle'), $body, 'confirm_accept', '', 0, 1);
    }
    elseif ($action === 'refuse' && $user->hasRight('productbycompany', 'write'))
    {
        $body = $langs->trans('ConfirmRefuseProductByCompanyBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmRefuseProductByCompanyTitle'), $body, 'confirm_refuse', '', 0, 1);
    }
    elseif ($action === 'reopen' && $user->hasRight('productbycompany', 'write'))
    {
        $body = $langs->trans('ConfirmReopenProductByCompanyBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmReopenProductByCompanyTitle'), $body, 'confirm_refuse', '', 0, 1);
    }
    elseif ($action === 'delete' && $user->hasRight('productbycompany', 'write'))
    {
        $body = $langs->trans('ConfirmDeleteProductByCompanyBody');
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?origin_id='.$object->origin_id.'&type='.$object->origin_type.'&id=' . $object->id, $langs->trans('ConfirmDeleteProductByCompanyTitle'), $body, 'confirm_delete', '', 0, 1);
    }
    elseif ($action === 'delete_ref' && $user->hasRight('productbycompany', 'write'))
    {
        $body = $langs->trans('ConfirmDeleteProductByCompanyBody');
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id='.$object->origin_id.'&type='.$object->origin_type.'&ref_id=' . $object->id, $langs->trans('ConfirmDeleteProductByCompanyTitle'), $body, 'confirm_delete', '', 0, 1);
    }
    elseif ($action === 'clone' && $user->hasRight('productbycompany', 'write'))
    {
        $body = $langs->trans('ConfirmCloneProductByCompanyBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmCloneProductByCompanyTitle'), $body, 'confirm_clone', '', 0, 1);
    }
    elseif ($action === 'cancel' && $user->hasRight('productbycompany', 'write'))
    {
        $body = $langs->trans('ConfirmCancelProductByCompanyBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmCancelProductByCompanyTitle'), $body, 'confirm_cancel', '', 0, 1);
    }

    return $formconfirm;
}

/**
 * methode eval du listView pour renvoi getNomUrl
 */
function getOriginLink($type, $val)
{
	global $db;

	$link = '';
	if ($type == 'company')
	{
		require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
		$obj = new Product($db);
		$res = $obj->fetch($val);
	}
	else if ($type == 'product')
	{
		require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
		$obj = new Societe($db);
		$res = $obj->fetch($val);
	}

	if ($res > 0) $link = $obj->getNomUrl(1);

	return $link;
}
