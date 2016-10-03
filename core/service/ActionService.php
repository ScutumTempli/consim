<?php
/**
* @package ConSim for phpBB3.1
*
* @copyright (c) 2015 Marco Candian (tacitus@strategie-zone.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*/

namespace consim\core\service;

use consim\core\entity\Action;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Operator for all locations, that you can travel
*/
class ActionService
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var ContainerInterface */
	protected $container;

	/** @var  \consim\core\service\UserService */
	protected $userService;

	/**
	* The database table the consim user data are stored in
	* @var string
	*/
	protected $consim_action_table;

	/** @var  \consim\core\entity\Action|null */
	protected $currentAction = null;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db						Database object
	 * @param ContainerInterface					$container				Service container interface
	 * @param \consim\core\service\UserService		$userService			UserService object
	 * @param string								$consim_travel_table	Name of the table used to store data
	 * @access public
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db,
		ContainerInterface $container,
		\consim\core\service\UserService $userService,
		$consim_action_table)
	{
		$this->db = $db;
		$this->container = $container;
		$this->userService = $userService;
		$this->consim_action_table = $consim_action_table;
	}

	/**
	 * Return current action of current user
	 *
	 * @return Action
	 */
	public function getCurrentAction()
	{
		if($this->currentAction == null)
		{
			$this->currentAction = $this->getCurrentActionFromUser($this->userService->getCurrentUser()->getUserId());
		}

		return $this->currentAction;
	}

	/**
	* Get all finished actions
	*
	* @access public
	*/
	public function finishedActions()
	{
		$sql = 'SELECT a.id, a.user_id, a.location_id, a.starttime, a.endtime, a.route_id, a.work_id, a.result, a.successful_trials, a.status
			FROM ' . $this->consim_action_table . ' a
			WHERE a.endtime <= '. time() .' AND a.status = '. Action::active;
		$result = $this->db->sql_query($sql);

		while($row = $this->db->sql_fetchrow($result))
		{
			$this->container->get('consim.core.entity.action')->import($row)->done();
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Get current action from user
	*
	* @param int $user_id User ID
	* @return Action
	* @access public
	*/
	public function getCurrentActionFromUser($user_id)
	{
		if($this->currentAction != null && $this->userService->getCurrentUser()->getUserId() == $user_id)
		{
			return $this->currentAction;
		}

		$sql = 'SELECT a.id, a.user_id, a.location_id, a.starttime, a.endtime, a.route_id, a.work_id, a.result, a.successful_trials, a.status
			FROM ' . $this->consim_action_table . ' a
			WHERE user_id = ' . (int) $user_id .' AND status = '. Action::active .' OR status = '. Action::mustConfirm;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		
		return $this->container->get('consim.core.entity.action')->import($row);
	}
}
