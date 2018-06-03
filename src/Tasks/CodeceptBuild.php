<?php
/**
 * @package     Joomla\Testing\Robo
 * @subpackage  Tasks
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Testing\Robo\Tasks;

use Robo\Exception\TaskException;
use Robo\Task\Testing\Codecept;

/**
 * Builder tasks for Codeception (based on Robo's Codecept task)
 *
 * @package     Joomla\Testing\Robo
 * @subpackage  Tasks
 *
 * @since       1.0.0
 */
final class CodeceptBuild extends Codecept
{
	/**
	 * Constructor function - based on Codecept constructor
	 *
	 * @param   string  $pathToCodeception  Path to codeception, optional
	 *
	 * @since   1.0.0
	 *
	 * @throws  TaskException
	 */
	public function __construct($pathToCodeception = '')
	{
		// No parent constructor call since the command will be generated manually

		$this->command = $pathToCodeception;

		if (!$this->command)
		{
			$this->command = $this->findExecutable('codecept');
		}

		if (!$this->command)
		{
			throw new TaskException(__CLASS__, "Neither composer nor phar installation of Codeception found.");
		}

		$this->command .= ' build';
	}

	/**
	 * Generates AcceptanceTester
	 *
	 * @return  \Robo\Result
	 *
	 * @since   1.0.0
	 */
	public function run()
	{
		$command = $this->getCommand();
		$this->printTaskInfo('Executing {command}', array('command' => $command));

		return $this->executeCommand($command);
	}
}
