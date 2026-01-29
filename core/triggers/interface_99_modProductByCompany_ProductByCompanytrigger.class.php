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
 * 	\file		core/triggers/interface_99_modMyodule_ProductByCompanytrigger.class.php
 * 	\ingroup	productbycompany
 * 	\brief		Sample trigger
 * 	\remarks	You can create other triggers by copying this one
 * 				- File name should be either:
 * 					interface_99_modProductbycompany_Mytrigger.class.php
 * 					interface_99_all_Mytrigger.class.php
 * 				- The file must stay in core/triggers
 * 				- The class name must be InterfaceMytrigger
 * 				- The constructor method must be named InterfaceMytrigger
 * 				- The name property name must be Mytrigger
 */

/**
 * Trigger class
 */
class InterfaceProductByCompanytrigger
{

	private $db;

	/**
	 * Constructor
	 *
	 * 	@param		DoliDB		$db		Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "demo";
		$this->description = "Triggers of this module are empty functions. They have no effect. They are provided for tutorial purpose only.";
		// 'development', 'experimental', 'dolibarr' or version
		$this->version = 'development';
		$this->picto = 'productbycompany@productbycompany.png';
	}

	/**
	 * Trigger name
	 *
	 * 	@return		string	Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Trigger description
	 *
	 * 	@return		string	Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}

	/**
	 * Trigger version
	 *
	 * 	@return		string	Version of trigger file
	 */
	public function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') {
			return $langs->trans("Development");
		} elseif ($this->version == 'experimental')

				return $langs->trans("Experimental");
		elseif ($this->version == 'dolibarr') return DOL_VERSION;
		elseif ($this->version) return $this->version;
		else {
			return $langs->trans("Unknown");
		}
	}
	/**
	 * Function called when a Dolibarrr business event is done.
	 * All functions "run_trigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * 	@param		string		$action		Event action code
	 * 	@param		Object		$object		Object
	 * 	@param		User		$user		Object user
	 * 	@param		Translate	$langs		Object langs
	 * 	@param		conf		$conf		Object conf
	 * 	@return		int						<0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, $user, $langs, $conf)
	{
		// Put here code you want to execute when a Dolibarr business events occurs.
		// Data and type of action are stored into $object and $action
		// Users

		$update = '_MODIFY';
		if ($action == 'LINEORDER_INSERT' || $action == 'LINEORDER_CREATE') {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->createCustomRef($object);
		} elseif ($action == 'LINEORDER'.$update) {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->createCustomRef($object, 'edit');
		} elseif ($action == 'LINEORDER_DELETE') {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->deleteCustomRef($object);
		} elseif ($action == 'LINEORDER_SUPPLIER_CREATE') {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->createCustomRef($object);
		} elseif ($action == 'LINEORDER_SUPPLIER'.$update) {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->createCustomRef($object, 'edit');
		} elseif ($action == 'LINEORDER_SUPPLIER_DELETE') {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->deleteCustomRef($object);
		} elseif ($action == 'LINEPROPAL_INSERT' || $action == 'LINEPROPAL_CREATE') {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->createCustomRef($object);
		} elseif ($action == 'LINEPROPAL'.$update) {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->createCustomRef($object, 'edit');
		} elseif ($action == 'LINEPROPAL_DELETE') {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->deleteCustomRef($object);
		} elseif ($action == 'LINEBILL_INSERT' || $action == 'LINEBILL_CREATE') {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->createCustomRef($object);
		} elseif ($action == 'LINEBILL'.$update) {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->createCustomRef($object, 'edit');
		} elseif ($action == 'LINEBILL_DELETE') {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->deleteCustomRef($object);
		} elseif ($action == 'LINEBILL_SUPPLIER_CREATE') {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->createCustomRef($object);
		} elseif ($action == 'LINEBILL_SUPPLIER'.$update) {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->createCustomRef($object, 'edit');
		} elseif ($action == 'LINEBILL_SUPPLIER_DELETE') {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->deleteCustomRef($object);
		} elseif ($action == 'LINESUPPLIER_PROPOSAL_INSERT' || $action == 'LINESUPPLIER_PROPOSAL_CREATE') {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->createCustomRef($object);
		} elseif ($action == 'LINESUPPLIER_PROPOSAL'.$update) {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->createCustomRef($object, 'edit');
		} elseif ($action == 'LINESUPPLIER_PROPOSAL_DELETE') {
			dol_syslog(
				"Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
			);
			return $this->deleteCustomRef($object);
		}

		return 0;
	}
	/**
	 * Create or update custom reference
	 *
	 * @param   CommonObject    $object     Object line
	 * @param   string          $mode       Mode ('create' or 'edit')
	 * @return  int                         0
	 */
	public function createCustomRef(&$object, $mode = 'create')
	{
		global $langs, $db, $user;
		$langs->load('productbycompany@productbycompany');

		$selected 		= GETPOST('customRefSelect');
		$customRef		= trim(GETPOST('customRef'));
		$customLabel 	= trim(GETPOST('customLabel'));
		$majCustomRef	= (bool) GETPOST('majCustomRef');
		$customRowid	= GETPOST('customRowid');
		$customDetRowid = GETPOST('customDetRowid');

		if (!empty($object->origin)) {
			dol_include_once('/productbycompany/class/productbycompany.class.php');
			$previousElement = new ProductByCompanyDet($db);
			$previousElement->fk_origin = $object->origin_id;
			// the ."det" is needed to append to origin as the origin of a line shows only the main object and not the line object with det
			if ($object->origin == "order_supplier") {
				$object->origin = "commande_fournisseur";
			}
			$previousElement->origin_type = $object->origin."det";
			$exists = $previousElement->alreadyExists();
			if ($exists > 0) {
				$previousElement->fetch($exists);
			}

			$selected = 'on';
			$customRef = $previousElement->ref;
			$customLabel = $previousElement->label;
		}

		// récupération du fk_soc
		$parentTable = '';
		switch ($object->element) {
			case 'commandedet':
				$parentTable = 'commande';
				$fk="fk_".$parentTable;
				break;
			case 'facturedet':
				$parentTable = 'facture';
				$fk="fk_".$parentTable;
				break;
			case 'propaldet':
				$parentTable = 'propal';
				$fk="fk_".$parentTable;
				break;
			case 'commande_fournisseurdet':
				$parentTable = 'commande_fournisseur';
				$fk='fk_commande';
				break;
			case 'facture_fourn_det':
				$parentTable = 'facture_fourn';
				$fk='fk_facture_fourn';
				break;
			case 'supplier_proposaldet':
				$parentTable = 'supplier_proposal';
				$fk='fk_supplier_proposal';
				break;
			default :
				return 0;
		}



		$sql = "SELECT fk_soc FROM ".MAIN_DB_PREFIX.$parentTable;
		$sql.= " WHERE rowid = ".$object->{$fk};
		$resql = $db->query($sql);
		if ($resql) {
			$obj = $db->fetch_object($resql);
			$fk_soc = $obj->fk_soc;
		}

		dol_include_once('/productbycompany/class/productbycompany.class.php');
		$parent_pbc 	= new ProductByCompany($db);
		$pbc_det 		= new ProductByCompanyDet($db);

		//Dans le cas où l'on converti un objet en un autre (ex : propal vers commande)
		if (empty($selected) && empty($customRef) && empty($customLabel) && !empty($object->origin) && !empty($object->origin_id)) {
			$res = $pbc_det->getOriginData($object->origin, $object->origin_id);
			if ($res > 0 && !empty($pbc_det->ref)) {
				$pbc_det->id = 0;
				$customRef = $pbc_det->ref;
				$customLabel = $pbc_det->label;
				$selected = true;
			}
		}

		$data = array(
			'fk_soc' 		=> $fk_soc
			,'fk_product' 	=> $object->fk_product
			,'ref' 			=> $customRef
			,'label' 		=> $customLabel
			,'fk_origin' 	=> $object->id
			,'origin_type' 	=> $object->element
		);
		if (empty($selected)) {
			if ($mode == "edit") {
				$pbc_det->setValues($data);
				$pbc_det->id = $pbc_det->alreadyExists();
				if ($pbc_det->id > 0) {
					$pbc_det->delete($user);
				}
			}
		} else {
			if ($majCustomRef) {
				$parent_pbc->setValues($data);
				$parent_pbc->id = $parent_pbc->alreadyExists();
				$parent_pbc->save($user);
			}

			$pbc_det->setValues($data);
			$pbc_det->id = $pbc_det->alreadyExists();
			$pbc_det->save($user);
		}

		/*      var_dump(
			array(
				'selected' => $selected,
				'customRowid' => $customRowid,
				'customDetRowid' => $customDetRowid,
				'customref' => $customRef,
				'majCustomRef' => $majCustomRef,
				'data' => $data
			)
		);
		exit;*/

		return 0;
	}
	/**
	 * Delete custom reference
	 *
	 * @param   CommonObject    $object     Object line
	 * @return  void
	 */
	public function deleteCustomRef(&$object)
	{
		global $db, $user;

		dol_include_once('/productbycompany/class/productbycompany.class.php');
		$pbc_det= new ProductByCompanyDet($db);

		$pbc_det->fk_origin = $object->id;
		$pbc_det->origin_type = $object->element;

		$pbc_det->id = $pbc_det->alreadyExists();
		$pbc_det->delete($user);
	}
}
