<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * Download robo.phar from http://robo.li/robo.phar and type in the root of the repo: $ php robo.phar
 * Or do: $ composer update, and afterwards you will be able to execute robo like $ php vendor/bin/robo
 *
 * @package     Joomla\Testing\Robo
 * @subpackage  RoboFile
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @see        http://robo.li/
 *
 * @since      1.0.0
 */

define('JPATH_TESTING_BASE', __DIR__);

/**
 * Test Robo File
 *
 * @package     Joomla\Testing\Robo
 * @subpackage  RoboFile
 *
 * @since       1.0.0
 */
class RoboFile extends \Robo\Tasks
{
	use Joomla\Testing\Robo\Tasks\LoadTasks;

	/**
	 * Check the code style of the project against a passed sniffers using PHP_CodeSniffer_CLI
	 *
	 * @param   string  $sniffersPath  Path to the sniffers. If not provided Joomla Coding Standards will be used.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function checkCodestyle($sniffersPath = null)
	{
		if (is_null($sniffersPath))
		{
			$sniffersPath = __DIR__ . '/.tmp/coding-standards';
		}

		$this->taskCodeChecks()
			->setBaseRepositoryPath(__DIR__)
			->setCodeStyleStandardsFolder($sniffersPath)
			->setCodeStyleCheckFolders(
				array(
					'src'
				)
			)
			->checkCodeStyle()
			->run()
			->stopOnFail();
	}
}
