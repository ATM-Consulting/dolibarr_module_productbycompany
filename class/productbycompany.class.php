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

if (!class_exists('SeedObject'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call or for session timeout on our module page
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
}


class ProductByCompany extends SeedObject
{
	/** @var string $table_element Table name in SQL */
	public $table_element = 'product_by_company';

	/** @var string $element Name of the element (tip for better integration in Dolibarr: this value should be the reflection of the class name with ucfirst() function) */
	public $element = 'productbycompany';

	/** @var int $isextrafieldmanaged Enable the fictionalises of extrafields */
    public $isextrafieldmanaged = 0;

    /** @var int $ismultientitymanaged 0=No test on entity, 1=Test with field entity, 2=Test with link by societe */
    public $ismultientitymanaged = 1;

    /**
     *  'type' is the field format.
     *  'label' the translation key.
     *  'enabled' is a condition when the field must be managed.
     *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). Using a negative value means field is not shown by default on list but can be selected for viewing)
     *  'noteditable' says if field is not editable (1 or 0)
     *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
     *  'default' is a default value for creation (can still be replaced by the global setup of default values)
     *  'index' if we want an index in database.
     *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
     *  'position' is the sort order of field.
     *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
     *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
     *  'css' is the CSS style to use on field. For example: 'maxwidth200'
     *  'help' is a string visible as a tooltip on field
     *  'comment' is not used. You can store here any text of your choice. It is not used by application.
     *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
     *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
     */

    public $fields = array(

        'entity' => array(
            'type' => 'integer',
            'label' => 'Entity',
            'enabled' => 1,
            'visible' => 0,
            'default' => 1,
            'notnull' => 1,
            'index' => 1,
            'position' => 20
        ),

        'fk_product' => array(
            'type' => 'integer:Product:product/class/product.class.php',
            'label' => 'Product',
            'visible' => 1,
            'enabled' => 1,
            'position' => 30,
            'index' => 1,
            'notnull' => 1,
            'help' => 'LinkToProduct'
        ),

        'fk_soc' => array(
            'type' => 'integer:Societe:societe/class/societe.class.php',
            'label' => 'ThirdParty',
            'visible' => 1,
            'enabled' => 1,
            'position' => 40,
            'index' => 1,
            'notnull' => 1,
            'help' => 'LinkToThirparty'
        ),

        'ref' => array(
            'type' => 'varchar(128)',
            'length' => 128,
            'label' => 'Ref',
            'enabled' => 1,
            'visible' => 1,
            'notnull' => 1,
            'showoncombobox' => 1,
            'index' => 1,
            'position' => 50,
            'comment' => 'Reference of product'
        ),

        'label' => array(
            'type' => 'varchar(255)',
            'label' => 'Label',
            'enabled' => 1,
            'visible' => 1,
            'position' => 60,
            'css' => 'minwidth200',
            'showoncombobox' => 1
        ),

//        'description' => array(
//            'type' => 'text', // or html for WYSWYG
//            'label' => 'Description',
//            'enabled' => 1,
//            'visible' => -1, //  un bug sur la version 9.0 de Dolibarr necessite de mettre -1 pour ne pas apparaitre sur les listes au lieu de la valeur 3
//            'position' => 70
//        ),

        'import_key' => array(
            'type' => 'varchar(14)',
            'label' => 'ImportId',
            'enabled' => 1,
            'visible' => -2,
            'notnull' => -1,
            'index' => 0,
            'position' => 1000
        ),

    );

    /** @var int $entity Object entity */
	public $entity;

    /** @var int $fk_product Product reference */
	public $fk_product;

    /** @var int $fk_product Company reference */
	public $fk_soc;

    /** @var string $ref Object reference */
	public $ref;

    /** @var string $label Object label */
    public $label;

//    /** @var string $description Object description */
//    public $description;

    /** @var string $import_key Key */
    public $import_key;

    /**
     * ProductByCompany constructor.
     * @param DoliDB    $db    Database connector
     */
    public function __construct($db)
    {
		global $conf;

        parent::__construct($db);

		$this->init();

		$this->entity = $conf->entity;
    }

    public function fetchByArray($limit = 0, $TFilter = array(), $loadChild = true, $justFetchIfOnlyOneResult = true)
	{
		if (empty($params)) return -1;

		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql.= " WHERE 1=1";
		foreach ($params as $key => $value)
		{
			$sql .= " AND " . $key . " = '" . $this->db->escape($value)."'";
		}

		$res = $this->db->query($sql);
		if ($res)
		{
			if ($this->db->num_rows($res))
			{
				$obj = $this->db->fetch_object($res);
				$this->fetch($obj->rowid, $loadChild);
			}
			else return 0;
		}
		else return -2;
	}

	/**
     * @param User $user User object
     * @return int
     */
    public function save($user)
    {
        return $this->create($user);
    }


    /**
     * @see cloneObject
     * @return void
     */
    public function clearUniqueFields()
    {
        $this->ref = 'Copy of '.$this->ref;
    }


    /**
     * @param User $user User object
     * @return int
     */
    public function delete(User &$user, $notrigger = false)
    {
        $this->deleteObjectLinked();

        unset($this->fk_element); // avoid conflict with standard Dolibarr comportment
        return parent::delete($user);
    }

	/**
	 * Verify if the productbycompany already exists or not
	 * @return int > 0 if existing, 0 if not or < 0 if KO
	 */
    public function alreadyExists()
	{
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql.= " WHERE fk_soc = ".$this->fk_soc;
		$sql.= " AND fk_product = ".$this->fk_product;

		$res = $this->db->query($sql);
		if (!$res)
		{
			$this->error = $this->db->lasterror;
			return -1;
		}
		else
		{
			$obj = $this->db->fetch_object($res);
			if ($obj !== null) {
				return (int) $obj->rowid;
			} else {
				return 0;
			}
		}
	}

	/**
	 * Verify if the productbycompany already exists or not
	 * @return int > 0 if existing, 0 if not or < 0 if KO
	 */
	public function countSocCustomRef()
	{
		$sql = "SELECT COUNT(*) nb FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql.= " WHERE fk_soc = ".$this->fk_soc;

		$obj = $this->db->getRow($sql);
		if ($obj === false)
		{
			$this->error = $this->db->lasterror;
			return -1;
		}
		else
		{
			return (int) $obj->nb;
		}
	}


    /**
     * @param int    $withpicto     Add picto into link
     * @param string $moreparams    Add more parameters in the URL
     * @return string
     */
    public function getNomUrl($withpicto = 0, $moreparams = '')
    {
		global $langs;

        $result='';
//        $label = '<u>' . $langs->trans("ShowProductByCompany") . '</u>';
//        if (! empty($this->ref)) $label.= '<br><b>'.$langs->trans('Ref').':</b> '.$this->ref;

        $linkclose = '" title="'.dol_escape_htmltag($label, 1).'" class="classfortooltip">';
        $link = '<a href="'.dol_buildpath('/productbycompany/card.php', 1).'?id='.$this->id.urlencode($moreparams).$linkclose;

        $linkend='</a>';

        $picto='generic';
//        $picto='productbycompany@productbycompany';

        if ($withpicto) $result.=($link.img_object($label, $picto, 'class="classfortooltip"').$linkend);
        if ($withpicto && $withpicto != 2) $result.=' ';

        $result.=$link.$this->ref.$linkend;

        return $result;
    }

    /**
     * @param int       $id             Identifiant
     * @param null      $ref            Ref
     * @param int       $withpicto      Add picto into link
     * @param string    $moreparams     Add more parameters in the URL
     * @return string
     */
    public static function getStaticNomUrl($id, $ref = null, $withpicto = 0, $moreparams = '')
    {
		global $db;

		$object = new ProductByCompany($db);
		$object->fetch($id, false, $ref);

		return $object->getNomUrl($withpicto, $moreparams);
    }
}


class ProductByCompanyDet extends ProductByCompany
{
    public $table_element = 'product_by_company_det';

    public $element = 'productbycompanydet';

    public $fk_origin;

    public $origin_type;


    /**
     * ProductByCompanyDet constructor.
     * @param DoliDB    $db    Database connector
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->fields['fk_origin'] = array(
			'type' => 'integer',
			'label' => 'Origin',
			'enabled' => 1,
			'visible' => 0,
			'default' => 1,
			'notnull' => 1,
			'index' => 1,
			'position' => 70
		);

		$this->fields['origin_type'] = array(
			'type' => 'varchar(128)',
			'length' => 128,
			'label' => 'Origin_id',
			'enabled' => 1,
			'visible' => 0,
			'notnull' => 1,
			'position' => 80
		);

        $this->init();
    }

	/**
	 * Verify if the productbycompanydet already exists or not
	 * @return int > 0 if existing, 0 if not or < 0 if KO
	 */
	public function alreadyExists()
	{
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql.= " WHERE fk_origin = ".$this->fk_origin;
		$sql.= " AND origin_type = '".$this->origin_type."'";

		$res = $this->db->query($sql);
		if (!$res)
		{
			$this->error = $this->db->lasterror;
			return -1;
		}
		else
		{
			$obj = $this->db->fetch_object($res);
			if ($obj !== null) {
				return (int) $obj->rowid;
			} else {
				return 0;
			}
		}
	}

	/**
	 * @param string $origin
	 * @param int $origin_id
	 * @return int
	 */
	public function getOriginData($origin, $origin_id) {
		switch($origin) {
			case 'propal' :
				$origin = 'propaldet';
				break;
			case 'commande' :
				$origin = 'commandedet';
				break;
			case 'order_supplier' :
				$origin = 'commande_fournisseurdet';
				break;
			case 'supplier_proposal' :
				$origin = 'supplier_proposaldet';
				break;
		}

		$this->fk_origin = $origin_id;
		$this->origin_type = $origin;

		$fk_pbc = $this->alreadyExists();
		if(!empty($fk_pbc)) {
			return $this->fetch($fk_pbc);
		}
		return 0;
	}
}
