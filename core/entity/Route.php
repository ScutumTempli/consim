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
class Route extends abstractEntity
{
	/**
	* All of fields of this objects
	*
	**/
	protected static $fields = array(
    	'id'					=> 'integer',
      	'start_id'              => 'integer',
        'end_id'                => 'integer',
        'time'                  => 'integer',
	);

	/**
	* Some fields must be unsigned (>= 0)
	**/
	protected static $validate_unsigned = array(
      	'id',
        'start_id',
        'end_id',
        'time',
	);

	protected $data;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/**
	* The database table the consim user data are stored in
	* @var string
	*/
	protected $consim_route_table;

   /**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db                          Database object
	* @param string                               $consim_location_type_table  Name of the table used to store data
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $consim_route_table)
	{
		$this->db = $db;
		$this->consim_route_table = $consim_route_table;
	}

	/**
	* Load the data from the database for this object
	*
	* @param int $start Start Location
    * @param int $end End Location
	* @return object $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \consim\user\exception\out_of_bounds
	*/
	public function load($start, $end)
	{
		$sql = 'SELECT id, start_id, end_id, time
			FROM ' . $this->consim_route_table . '
			WHERE start_id = '. (int) $start .' AND end_id = '. (int) $end .'
               OR start_id = '. (int) $end.' AND end_id = '. (int) $start;
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
	* Get User ID
	*
	* @return int ID
	* @access public
	*/
	public function getId()
	{
		return $this->getInteger($this->data['id']);
	}

    /**
	* Get Start Location ID
	*
	* @return int ID
	* @access public
	*/
	public function getStartId()
	{
		return $this->getInteger($this->data['start_id']);
	}

    /**
	* Get End Location ID
	*
	* @return int ID
	* @access public
	*/
	public function getEndId()
	{
		return $this->getInteger($this->data['end_id']);
	}

    /**
	* Get Time
	*
	* @return int ID
	* @access public
	*/
	public function getTime()
	{
		return $this->getInteger($this->data['time']);
	}
}