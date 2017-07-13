<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * Download robo.phar from http://robo.li/robo.phar and type in the root of the repo: $ php robo.phar
 * Or do: $ composer update, and afterwards you will be able to execute robo like $ php vendor/bin/robo
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @see        http://robo.li/
 *
 * @since      __DEPLOY_VERSION__
 */

namespace Joomla\Testing\Robo\RoboFile;

use Joomla\Testing\Robo\Configuration\RoboFileConfiguration;
use Joomla\Testing\Robo\Tasks\LoadTasks;

/**
 * Base Robo File for extension testing
 *
 * @package     Joomla\Testing\Robo
 * @subpackage  RoboFile
 *
 * @since       1.0.0
 */
abstract class RoboFileBase extends \Robo\Tasks implements RoboFileInterface
{
	use ClientContainer;

	// Load tasks from composer, see composer.json
	use LoadTasks;

	/**
	 * Configuration of this test script
	 *
	 * @var RoboFileConfiguration;
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $configuration = null;

	/**
	 * RoboFile constructor.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct()
	{
		if (!defined('JPATH_TESTING_BASE'))
		{
			die('Please define JPATH_TESTING_BASE constant');
		}

		// Loads the configuration file for this test (if present)
		$this->configuration = new RoboFileConfiguration;
		$this->configuration->loadConfigurationFile(JPATH_TESTING_BASE . '/JoomlaTesting.ini');

		// Set default timezone (so no warnings are generated if it is not set)
		date_default_timezone_set('UTC');
	}

	/**
	 * Prepare the environment for testing and executes the install/setup tests
	 *
	 * @param   array  $opts  Array of configuration options:
	 *                        - 'use-htaccess': renames and enable embedded Joomla .htaccess file
	 *                        - 'env': set a specific environment to get configuration from
	 *                        - 'append-certificates': path with extra certificates to append
	 *                        - 'debug': executes codeception tasks with extended debug
	 *                        - 'install-suite': path to the installation suite
	 *                        - 'install-test': path to the installation test
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function runTestPreparation(
		$opts = array(
			'use-htaccess' => false,
			'env' => 'desktop',
			'append-certificates' => '',
			'debug' => false,
			'install-suite' => 'acceptance',
			'install-test' => 'install'
		)
	)
	{
		$this->createTestingSite($opts['use-htaccess'], $opts['append-certificates']);
		$this->prepareTestingPackage(array('dev' => true));
		$this->runSelenium();
		$this->codeceptionBuild();

		if (!empty($opts['install-suite']) && !empty($opts['install-test']))
		{
			$this->runCodeceptionSuite($opts['install-suite'], $opts['install-test'], $opts['debug'], $opts['env']);
		}
	}

	/**
	 * Creates a testing Joomla site for running the tests
	 *
	 * @param   bool    $useHtAccess             (1/0) Rename and enable embedded Joomla .htaccess file
	 * @param   string  $appendCertificatesPath  Path to add extra certificates to the Joomla pem file
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createTestingSite($useHtAccess = false, $appendCertificatesPath = '')
	{
		$taskCMSSetup = $this->taskCMSSetup()
			->setCmsRepository($this->configuration->getCmsRepository())
			->setCmsBranch($this->configuration->getCmsBranch())
			->setBaseTestsPath(JPATH_TESTING_BASE)
			->setCmsPath($this->configuration->getCmsPath())
			->setExecuteUser($this->configuration->getCmsPathOwner())
			->setCMSRootFolder($this->configuration->getCmsRootFolder())
			->setCertificatesPath($appendCertificatesPath);

		// Skips cloning the repository if requested and cache is present
		if (!$this->configuration->getCmsSkipClone()
			|| !file_exists('cache')
			|| !is_dir('cache'))
		{
			$taskCMSSetup->cloneCMSRepository();
		}

		// Sets up Joomla in the requested path
		$taskCMSSetup->setupCMSPath();

		// Fixes permissions if requested to
		if (!empty($this->configuration->getCmsPathOwner()))
		{
			$taskCMSSetup->fixPathPermissions();
		}

		// Sets up htaccess file with the requested configuration
		if ($useHtAccess)
		{
			$taskCMSSetup->setupHtAccess();
		}

		// Appends certificates to Joomla pem file if requested
		if (!empty($appendCertificatesPath)
			&& file_exists($appendCertificatesPath))
		{
			$taskCMSSetup->appendCertificates();
		}

		// Executes the requested tasks
		$taskCMSSetup->run()
			->stopOnFail();
	}

	/**
	 * Run the whole test script for this extension
	 *
	 * @param   array  $opts  Array of configuration options:
	 *                        - 'use-htaccess': renames and enable embedded Joomla .htaccess file
	 *                        - 'env': set a specific environment to get configuration from
	 *                        - 'append-certificates': path with extra certificates to append
	 *                        - 'debug': executes codeception tasks with extended debug
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function runTests(
		$opts = array(
			'use-htaccess' => false,
			'env' => 'desktop',
			'append-certificates' => '',
			'debug' => false
		)
	)
	{
		// Removes install suite and test from the preparation script, to execute it with the full script
		$opts['install-suite'] = '';
		$opts['install-test'] = '';

		$this->runTestPreparation($opts);
		$this->runTestSuites($opts);

		$this->killSelenium();
	}

	/**
	 * Runs Selenium Standalone Server.
	 *
	 * @param   bool  $debugMode  Sets the debug mode
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function runSelenium($debugMode = false)
	{
		$taskSelenium = $this->taskSeleniumStandaloneServer()
			->setBinary($this->configuration->getSeleniumBinary())
			->setWebdriver($this->configuration->getSeleniumWebDriver())
			->setLogFile('selenium.log');

		if ($debugMode)
		{
			$taskSelenium->setDebug(true);
		}

		$taskSelenium->runSelenium()
			->waitForSelenium()
			->run()
			->stopOnFail();
	}

	/**
	 * Kills Selenium Standalone Server
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function killSelenium()
	{
		$this->taskSeleniumStandaloneServer()
			->killSelenium()
			->run()
			->stopOnFail();
	}

	/**
	 * Builds Codeception AcceptanceTester
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function codeceptionBuild()
	{
		$this->taskCodeceptBuild()
			->run();
	}

	/**
	 * Runs a Codeception suite
	 *
	 * @param   string  $suite  Codeception suite to run
	 * @param   string  $test   Codeception test to run
	 * @param   bool    $debug  Execute codeception with debug options
	 * @param   string  $env    Set a specific environment to get configuration from
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function runCodeceptionSuite($suite, $test, $debug = false, $env = '')
	{
		$codeceptionTask = $this->taskCodecept()
			->arg('--fail-fast');

		if ($debug)
		{
			$codeceptionTask->arg('--steps')
				->arg('--debug');
		}

		if (!empty($env))
		{
			$codeceptionTask->rawArg('--env ' . $env);
		}

		$codeceptionTask->suite($suite)
			->test($test)
			->run()
			->stopOnFail();
	}
}


