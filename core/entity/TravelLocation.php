<?php
/**
* @package ConSim for phpBB3.1
*
* @copyright (c) 2015 Marco Candian (tacitus@strategie-zone.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*/

namespace consim\core\entity;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Entity for a single ressource
*/
class TravelLocation extends Travel
{
	/**
	* All of fields of this objects
	*
	**/
	protected static $fields = array(
		'id'						=> 'integer',
		'user_id'					=> 'integer',
		'starttime'					=> 'integer',
		'endtime'					=> 'integer',
		'start_location'			=> 'setStartLocation',
		'end_location'				=> 'setEndLocation',
		'status'					=> 'boolean',
	);

	/**
	* Some fields must be unsigned (>= 0)
	**/
	protected static $validate_unsigned = array(
		'id',
		'user_id',
		'starttime',
		'endtime',
	);

	protected $data;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var ContainerInterface */
	protected $container;

	/**
	* The database table the consim user data are stored in
	* @var string
	*/
	protected $consim_action_table;
	protected $consim_travel_table;
	protected $consim_location_table;
	protected $consim_location_type_table;
	protected $consim_province_table;
	protected $consim_country_table;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface	$db							Database object
	* @param ContainerInterface					$container					Service container interface
	* @param string								$consim_action_table		Name of the table used to store data
	* @param string								$consim_travel_table		Name of the table used to store data
	* @param string								$consim_location_table		Name of the table used to store data
	* @param string								$consim_location_type_table	Name of the table used to store data
	* @param string								$consim_province_table		Name of the table used to store data
	* @param string								$consim_country_table		Name of the table used to store data
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db,
								ContainerInterface $container,
								$consim_action_table,
								$consim_travel_table,
								$consim_location_table,
								$consim_location_type_table,
								$consim_province_table,
								$consim_country_table)
	{
		$this->db = $db;
		$this->container = $container;
		$this->consim_action_table = $consim_action_table;
		$this->consim_travel_table = $consim_travel_table;
		$this->consim_location_table = $consim_location_table;
		$this->consim_location_type_table = $consim_location_type_table;
		$this->consim_province_table = $consim_province_table;
		$this->consim_country_table = $consim_country_table;
	}

	/**
	* Load the data from the database for this object
	*
	* @param int $id travel action id
	* @return TravelLocation $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \consim\core\exception\out_of_bounds
	*/
	public function load($id)
	{
		$sql = 'SELECT a.id, a.user_id, a.starttime, a.endtime, a.status,
					   t.start_location_id, t.end_location_id,
					   l1.id AS startId, l1.name AS startName, l1.description AS startDescription, l1.image AS startImage,
					   tp1.name AS startType, p1.name as startProvince, c1.name as startCountry,
					   l2.id AS endId, l2.name AS endName, l1.description AS endDescription, l2.image AS endImage,
					   tp2.name AS endType, p2.name as endProvince, c2.name as endCountry
			FROM ' . $this->consim_action_table . ' a
			LEFT JOIN ' . $this->consim_travel_table . ' t ON t.id = a.travel_id
			LEFT JOIN ' . $this->consim_location_table . ' l1 ON l1.id = t.start_location_id
			LEFT JOIN ' . $this->consim_location_type_table . ' tp1 ON l1.type_id = tp1.id
			LEFT JOIN ' . $this->consim_province_table . ' p1 ON l1.province_id = p1.id
			LEFT JOIN ' . $this->consim_country_table . ' c1 ON p1.country_id = c1.id

			LEFT JOIN ' . $this->consim_location_table . ' l2 ON l2.id = t.end_location_id
			LEFT JOIN ' . $this->consim_location_type_table . ' tp2 ON l2.type_id = tp2.id
			LEFT JOIN ' . $this->consim_province_table . ' p2 ON l2.province_id = p2.id
			LEFT JOIN ' . $this->consim_country_table . ' c2 ON p2.country_id = c2.id
			WHERE a.id = '. (int) $id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row === false)
		{
			throw new \consim\core\exception\out_of_bounds('id');
		}

		$this->data = array(
			'id'			=> $row['id'],
			'user_id'		=> $row['user_id'],
			'starttime'		=> $row['starttime'],
			'endtime'		=> $row['endtime'],
			'status'		=> $row['status'],
		);

		$start_location = array(
			'id'			=> $row['startId'],
			'name'			=> $row['startName'],
			'description'	=> $row['startDescription'],
			'image'			=> $row['startImage'],
			'type'			=> $row['startType'],
			'province'		=> $row['startProvince'],
			'country'		=> $row['startCountry'],
		);
		$this->data['start_location'] = $this->container->get('consim.core.entity.location')->import($start_location);

		$end_location = array(
			'id'			=> $row['endId'],
			'name'			=> $row['endName'],
			'description'	=> $row['endDescription'],
			'image'			=> $row['endImage'],
			'type'			=> $row['endType'],
			'province'		=> $row['endProvince'],
			'country'		=> $row['endCountry'],
		);
		$this->data['end_location'] = $this->container->get('consim.core.entity.location')->import($end_location);

		return $this;
	}

	/**
	* Get Start Location
	*
	* @return Object Location
	* @access public
	*/
	public function getStartLocation()
	{
		return $this->data['start_location'];
	}

	/**
	* Set Start Location
	*
	* @param mixed[] $location_data
	* @return TravelLocation $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \consim\core\exception\unexpected_value
	*/
	public function setStartLocation($location_data)
	{
		$this->data['start_location'] = $this->container->get('consim.core.entity.location')->import($location_data);
		return $this;
	}

	/**
	* Get End Location
	*
	* @return Object Location
	* @access public
	*/
	public function getEndLocation()
	{
		return $this->data['end_location'];
	}

	/**
	* Set End Location
	*
	* @param mixed[] $location_data
	* @return TravelLocation $this object for chaining calls; load()->set()->save()
	* @access public
	* @throws \consim\core\exception\unexpected_value
	*/
	public function setEndLocation($location_data)
	{
		$this->data['end_location'] = $this->container->get('consim.core.entity.location')->import($location_data);
		return $this;
	}

}
