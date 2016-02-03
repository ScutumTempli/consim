<?php
/**
* @package ConSim for phpBB3.1
*
* @copyright (c) 2015 Marco Candian (tacitus@strategie-zone.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*/

namespace consim\core\entity;

/**
* Entity for a single ressource
*/
class ConsimFigure extends abstractEntity
{
	/**
	* All of fields of this objects
	*
	**/
	protected static $fields = array(
    	'id'						=> 'integer',
      	'beschreibung'				=> 'string',
      	'wert'						=> 'string',
      	'translate'					=> 'string',
	);

	/**
	* Some fields must be unsigned (>= 0)
	**/
	protected static $validate_unsigned = array(
      	'id',
	);

	protected $data;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/**
	* The database table the consim user data are stored in
	* @var string
	*/
	protected $consim_person_table;

   /**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db                   Database object
	* @param string                               $consim_person_table  Name of the table used to store consim user data
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $consim_person_table)
	{
		$this->db = $db;
		$this->consim_person_table = $consim_person_table;
	}

	/**
	* Load the data from the database for this ressource
	*
	* @param int $user_id user identifier
	* @return object $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \consim\user\exception\out_of_bounds
	*/
	public function load($id)
	{
		$sql = 'SELECT *
			FROM ' . $this->consim_person_table . '
			WHERE id = ' . (int) $id;
		$result = $this->db->sql_query($sql);
		$this->data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($this->data === false)
		{
			throw new \consim\core\exception\out_of_bounds('id');
		}

		return $this;
	}

	/**
	* Insert the Data for the first time
	*
	* Will throw an exception if the data was already inserted (call save() instead)
	*
	* @return object $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \consim\core\exception\out_of_bounds
	*/
	public function insert()
	{
        if (!empty($this->data['id']))
		{
			// The data already exists
			throw new \consim\core\exception\out_of_bounds('id');
		}

		// Make extra sure there is no id set
		unset($this->data['id']);

		// Insert the data to the database
		$sql = 'INSERT INTO ' . $this->consim_person_table . ' ' . $this->db->sql_build_array('INSERT', $this->data);
		$this->db->sql_query($sql);

        // Set the id using the id created by the SQL insert
		$this->data['user_id'] = (int) $this->db->sql_nextid();

		return $this;
	}

	/**
	* Save the current settings to the database
	*
	* This must be called before closing or any changes will not be saved!
	* If adding a data (saving for the first time), you must call insert() or an exeception will be thrown
	*
	* @return object $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \consim\core\exception\out_of_bounds
	*/
	public function save()
	{
		if (empty($this->data['id']))
		{
			// The data does not exist
			throw new \consim\core\exception\out_of_bounds('user_id');
		}

		$sql = 'UPDATE ' . $this->consim_person_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $this->data) . '
			WHERE id = ' . $this->getId();
		$this->db->sql_query($sql);

		return $this;
	}

	/**
	* Get User ID
	*
	* @return string ID
	* @access public
	*/
	public function getId()
	{
		return $this->getString($this->data['id']);
	}

	/**
	* Get Beschreibung
	*
	* @return string Beschreibung
	* @access public
	*/
	public function getBeschreibung()
	{
		return $this->getString($this->data['beschreibung']);
	}

   /**
	* Get Wert
	*
	* @return string Wert
	* @access public
	*/
	public function getWert()
	{
		return $this->getString($this->data['wert']);
	}

   /**
	* Get Translate
	*
	* @return string Translate
	* @access public
	*/
	public function getTranslate()
	{
		return $this->getString($this->data['translate']);
	}
}