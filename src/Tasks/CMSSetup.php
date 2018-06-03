<?php
/**
 * @package     Joomla\Testing\Robo
 * @subpackage  Tasks
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Testing\Robo\Tasks;

use Joomla\Testing\Robo\Handlers\RoboHandler;

/**
 * Class with Robo tasks for setting up the CMS for testing
 *
 * @package     Joomla\Testing\Robo
 * @subpackage  Tasks
 *
 * @since       1.0.0
 */
final class CMSSetup extends GenericTask
{
	use Traits\OS;

	/**
	 * Human readable name of the task
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected $taskName = 'CMS setup';

	/**
	 * Repository CMS (Github key)
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	private $cmsRepository = 'joomla/joomla-cms';

	/**
	 * Branch to be used to set up the CMS
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	private $cmsBranch = 'staging';

	/**
	 * Repository cache time in seconds
	 *
	 * @var    integer
	 *
	 * @since  1.0.0
	 */
	private $cmsCacheTime = 86400;

	/**
	 * Base path where tests are executed
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	private $baseTestsPath = '';

	/**
	 * Path for the CMS, under base path
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	private $cmsPath = 'joomla';

	/**
	 * Path for cache CMS repository, under base path
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	private $cachePath = 'cache';

	/**
	 * Optional user to execute the tasks, for permission purposes
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	private $executeUser = '';

	/**
	 * Path to the file containing the extra certificates used by the Joomla certificates file
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	private $certificatesPath = '';

	/**
	 * Root path in the web server for the CMS installation
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	private $cmsRootFolder = '';

	/**
	 * Original path where the CMS is stored to have it copied instead of cloned
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	private $cmsOriginalFolder = '';

	/**
	 * When copying Joomla, it will exclude the given folders
	 *
	 * @var    array
	 *
	 * @since  1.0.0
	 */
	private $cmsExcludeCopyFolders = array();

	/**
	 * Sets the Github repository (owner/repo) with the CMS
	 *
	 * @param   string  $cmsRepository  Owner/Repo combination of the Github repository
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function setCmsRepository($cmsRepository)
	{
		$this->cmsRepository = $cmsRepository;

		return $this;
	}

	/**
	 * Sets the branch of the CMS
	 *
	 * @param   string  $cmsBranch  CMS branch
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function setCmsBranch($cmsBranch)
	{
		$this->cmsBranch = $cmsBranch;

		return $this;
	}

	/**
	 * Sets the cache time for the CMS repository
	 *
	 * @param   string  $cmsCacheTime  Cached time for the CMS repository
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function setCmsCache($cmsCacheTime)
	{
		$this->cmsCacheTime = $cmsCacheTime;

		return $this;
	}

	/**
	 * Sets the testing base path
	 *
	 * @param   string  $baseTestsPath  Base path for all tests operations
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function setBaseTestsPath($baseTestsPath)
	{
		$this->baseTestsPath = $baseTestsPath;

		return $this;
	}

	/**
	 * Sets the CMS path (under base path) where the CMS will run
	 *
	 * @param   string  $cmsPath  Path where the CMS will be executed
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function setCmsPath($cmsPath)
	{
		$this->cmsPath = $cmsPath;

		return $this;
	}

	/**
	 * Sets the cache path for downloading the CMS
	 *
	 * @param   string  $cachePath  Path to temporarily store the CMS repository
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function setCachePath($cachePath)
	{
		$this->cachePath = $cachePath;

		return $this;
	}

	/**
	 * Sets the user that will run the executables
	 *
	 * @param   string  $executeUser  User that needs to own everything to execute correctly
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function setExecuteUser($executeUser)
	{
		$this->executeUser = $executeUser;

		return $this;
	}

	/**
	 * Sets the path where the file containing extra certificates to be used exists
	 *
	 * @param   string  $path  Path with the extra certificates
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function setCertificatesPath($path)
	{
		$this->certificatesPath = $path;

		return $this;
	}

	/**
	 * Sets the root folder of the CMS in the web server
	 *
	 * @param   string  $cmsRootFolder  Root folder of the CMS in the web server
	 *
	 * @return  $this
	 */
	public function setCMSRootFolder($cmsRootFolder)
	{
		$this->cmsRootFolder = $cmsRootFolder;

		return $this;
	}

	/**
	 * Sets the original folder of the CMS, to copy the CMS from it
	 *
	 * @param   string  $cmsOriginalFolder  Original folder of the CMS
	 *
	 * @return  $this
	 */
	public function setCMSOriginalFolder($cmsOriginalFolder)
	{
		$this->cmsOriginalFolder = $cmsOriginalFolder;

		return $this;
	}

	/**
	 * When copying Joomla, it will exclude the given folders
	 *
	 * @param   string|array  $cmsExcludeCopyFolders  Folders to be excluded, comma separated or array
	 *
	 * @return  $this
	 */
	public function setCMSExcludeCopyFolders($cmsExcludeCopyFolders)
	{
		if (!is_array($cmsExcludeCopyFolders))
		{
			$cmsExcludeCopyFolders = explode(',', $cmsExcludeCopyFolders);

			if (!empty($cmsExcludeCopyFolders))
			{
				// Removes trailing spaces
				foreach ($cmsExcludeCopyFolders as $i => $folder)
				{
					$cmsExcludeCopyFolders[$i] = trim($folder);
				}
			}
		}

		$this->cmsExcludeCopyFolders = $cmsExcludeCopyFolders;

		return $this;
	}

	/**
	 * Task for cloning the CMS repository
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function cloneCMSRepository()
	{
		return $this->setupTask('cloneCMSRepository');
	}

	/**
	 * Task for setting up the CMS path, using the available cache
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function setupCMSPath()
	{
		return $this->setupTask('setupCMSPath');
	}

	/**
	 * Task for fixing permissions when needed
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function fixPathPermissions()
	{
		return $this->setupTask('fixPathPermissions');
	}

	/**
	 * Task for setting up the .htaccess file in the CMS
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function setupHtAccess()
	{
		return $this->setupTask('setupHtAccess');
	}

	/**
	 * Task for appending the certificates to the CMS certificates path
	 *
	 * @return  $this
	 *
	 * @since   1.0.0
	 */
	public function appendCertificates()
	{
		return $this->setupTask('appendCertificates');
	}

	/**
	 * Clones (or copies) the CMS repository
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	protected function cloneCMSRepositoryExecute()
	{
		$this->printTaskInfo('Cloning CMS repository (or validated cache)');

		if (empty($this->baseTestsPath) || !file_exists($this->baseTestsPath) || !is_dir($this->baseTestsPath))
		{
			$this->printTaskError('No valid base path defined for tests');

			return false;
		}

		if (!empty($this->cmsOriginalFolder) && file_exists($this->cmsOriginalFolder))
		{
			$this->copyCMS($this->cmsOriginalFolder, $this->cmsPath, $this->cmsExcludeCopyFolders);

			return true;
		}

		$fullCachePath = $this->baseTestsPath . '/' . $this->cachePath;

		if (empty($this->cachePath))
		{
			$this->printTaskError('No valid base path defined for caching the CMS repository');

			return false;
		}

		// Cache is timed out or does not exist
		if (!is_dir($fullCachePath) || (time() - filemtime($fullCachePath) > $this->cmsCacheTime))
		{
			if (file_exists($fullCachePath))
			{
				$roboHandler = RoboHandler::getInstance();

				if (!$roboHandler->deleteDirectory($fullCachePath))
				{
					$this->printTaskError('Error trying to remove an old cache directory');

					return false;
				}
			}

			if (!$this->executeCloneCommand())
			{
				return false;
			}

			return true;
		}

		return true;
	}

	/**
	 * Task for setting up the CMS path, using the available cache
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	protected function setupCMSPathExecute()
	{
		$this->printTaskInfo('Setting up the CMS in its path');

		if (empty($this->baseTestsPath) || !file_exists($this->baseTestsPath) || !is_dir($this->baseTestsPath))
		{
			$this->printTaskError('No valid base path defined for tests');

			return false;
		}

		$fullCachePath = $this->baseTestsPath . '/' . $this->cachePath;
		$fullCMSPath = $this->baseTestsPath . '/' . $this->cmsPath;
		$roboHandler = RoboHandler::getInstance();

		if (empty($this->cmsPath))
		{
			$this->printTaskError('No valid path defined for the CMS');

			return false;
		}

		if (!file_exists($fullCachePath) || !is_dir($fullCachePath))
		{
			$this->printTaskError('No valid cache path defined for tests');

			return false;
		}

		if (file_exists($fullCMSPath))
		{
			if (!$roboHandler->deleteDirectory($fullCMSPath))
			{
				$this->printTaskError('Error trying to remove an old CMS directory');

				return false;
			}
		}

		if (!$roboHandler->copyDir($fullCachePath, $fullCMSPath))
		{
			$this->printTaskError('Error copying from cache to create a new CMS path');

			return false;
		}

		return true;
	}

	/**
	 * Task for fixing permissions when needed
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	protected function fixPathPermissionsExecute()
	{
		$this->printTaskInfo('Fixing permissions to the CMS path');

		// No permission fixing for Windows
		if ($this->isWindows())
		{
			return true;
		}

		$fullCMSPath = $this->baseTestsPath . '/' . $this->cmsPath;

		// No paths to set permissions to
		if (!$this->baseTestsPath || !$this->cmsPath || !file_exists($fullCMSPath) || !is_dir($fullCMSPath))
		{
			$this->$this->printTaskError('No CMS path to set permissions to');

			return false;
		}

		if ($this->executeUser != '')
		{
			$roboHandler = RoboHandler::getInstance();

			if (!$roboHandler->executeCommand('chown -R ' . $this->executeUser . ' ' . $fullCMSPath))
			{
				$this->$this->printTaskError('Permissions could not be set to the CMS path');

				return false;
			}
		}

		return true;
	}

	/**
	 * Task for setting up the .htaccess file in the CMS
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	protected function setupHtAccessExecute()
	{
		$this->printTaskInfo('Setting up .htaccess file in CMS folder');

		$fullCMSPath = $this->baseTestsPath . '/' . $this->cmsPath;

		// No valid CMS path
		if (!$this->baseTestsPath || !$this->cmsPath || !file_exists($fullCMSPath) || !is_dir($fullCMSPath))
		{
			$this->printTaskError('No CMS path was found');

			return false;
		}

		if (!file_exists($fullCMSPath . '/htaccess.txt'))
		{
			$this->printTaskError('No htaccess.txt found in the CMS');

			return false;
		}

		$roboHandler = RoboHandler::getInstance();

		if (!$roboHandler->copyFile($fullCMSPath . '/htaccess.txt', $fullCMSPath . '/.htaccess'))
		{
			$this->printTaskError('htaccess.txt file could not be setup');

			return false;
		}

		if (!empty($this->cmsRootFolder))
		{
			$roboHandler->replaceInFile($fullCMSPath . '/.htaccess', '# RewriteBase /', 'RewriteBase /' . $this->cmsRootFolder);
		}

		return true;
	}

	/**
	 *  Task for appending the certificates to the CMS certificates path
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	protected function appendCertificatesExecute()
	{
		$this->printTaskInfo('Appending certificates to default Joomla certificates file');

		$fullCMSPath = $this->baseTestsPath . '/' . $this->cmsPath;
		$cmsCertificatesPath = $fullCMSPath . '/libraries/joomla/http/transport/cacert.pem';

		// No valid CMS path
		if (!$this->baseTestsPath || !$this->cmsPath || !file_exists($fullCMSPath) || !is_dir($fullCMSPath))
		{
			$this->printTaskError('No CMS path was found');

			return false;
		}

		// No valid certificates file
		if (empty($this->certificatesPath) || !file_exists($this->certificatesPath))
		{
			$this->printTaskError('No valid path was set for the files containing the certificates');

			return false;
		}

		if (!file_exists($fullCMSPath . '/libraries/joomla/http/transport/cacert.pem'))
		{
			$this->printTaskError('No cacert.pem file was found in the Joomla folder');

			return false;
		}

		$roboHandler = RoboHandler::getInstance();

		if (!$roboHandler->concatFiles(array($cmsCertificatesPath, $this->certificatesPath), $cmsCertificatesPath))
		{
			$this->printTaskError('Certificates could not be added to the CMS file');

			return false;
		}

		return true;
	}

	/**
	 * Executes the actual git clone command
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	private function executeCloneCommand()
	{
		$command = $this->buildCloneCommand();

		if ($command == '')
		{
			return false;
		}

		$roboHandler = RoboHandler::getInstance();

		return $roboHandler->executeCommand($command);
	}

	/**
	 * Builds the clone command to be executed
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function buildCloneCommand()
	{
		if (empty($this->cmsBranch))
		{
			$this->printTaskError('No branch is defined for the CMS');

			return '';
		}

		if (empty($this->cmsRepository) || !preg_match('/([0-9a-z_-]+)\/([0-9a-z_-]+)/i', $this->cmsRepository))
		{
			$this->printTaskError('No valid CMS repository was provided');

			return '';
		}

		if (empty($this->baseTestsPath) || !file_exists($this->baseTestsPath) || !is_dir($this->baseTestsPath))
		{
			$this->printTaskError('Invalid base path for tests');

			return '';
		}

		$fullCachePath = $this->baseTestsPath . '/' . $this->cachePath;

		if (empty($this->cachePath) || file_exists($fullCachePath))
		{
			$this->printTaskError('Invalid or not empty path for caching the CMS repository');

			return '';
		}

		return 'git' . $this->getGitExecutableExtension() .
			' clone -b ' . $this->cmsBranch . ' --single-branch --depth 1 ' .
			'https://github.com/' . $this->cmsRepository . '.git ' . $fullCachePath;
	}

	/**
	 * Copies Joomla from one folder to another
	 *
	 * @param   string  $source           Source Joomla path
	 * @param   string  $destination      Destination path for Joomla
	 * @param   array   $excludedFolders  Folders to be excluded from being copied
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 *
	 * @throws  \Exception
	 */
	private function copyCMS($source, $destination, $excludedFolders)
	{
		$dir = @opendir($source);

		if (false === $dir)
		{
			throw new \Exception($this, "Cannot open Joomla source directory");
		}

		if (!is_dir($destination))
		{
			mkdir($destination, 0755, true);
		}

		while (false !== ($file = readdir($dir)))
		{
			if (in_array($file, $excludedFolders))
			{
				continue;
			}

			if (($file !== '.') && ($file !== '..'))
			{
				$srcFile  = "." . '/' . $file;
				$destFile = $destination . '/' . $file;

				if (is_dir($srcFile))
				{
					$this->_copyDir($srcFile, $destFile);
				}
				else
				{
					copy($srcFile, $destFile);
				}
			}
		}

		closedir($dir);

		return true;
	}
}
