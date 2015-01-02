<?php
namespace TYPO3\Surf\CMS\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf.CMS".*
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\CMS\Application\TYPO3\CMS;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * Abstract task for any remote TYPO3 CMS cli action
 */
abstract class AbstractCliTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Execute this task
	 *
	 * @param array $cliArguments
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param CMS $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	protected function executeCliCommand(array $cliArguments, Node $node, CMS $application, Deployment $deployment, array $options = array()) {
		$phpBinaryPathAndFilename = isset($options['phpBinaryPathAndFilename']) ? $options['phpBinaryPathAndFilename'] : 'php';
		$commandPrefix = '';
		if (isset($options['context'])) {
			$commandPrefix = 'TYPO3_CONTEXT=' . escapeshellarg($options['context']) . ' ';
		}
		$commandPrefix .= $phpBinaryPathAndFilename . ' ';
		if (isset($options['useApplicationWorkspace']) && $options['useApplicationWorkspace'] === TRUE) {
			$targetPath = $deployment->getWorkspacePath($application);
			$node = $deployment->getNode('localhost');
		} else {
			$targetPath = $deployment->getApplicationReleasePath($application);
		}
		$webDirectory = isset($options['webDirectory']) ? rtrim($options['webDirectory'], '/') . '/' : '';

		$this->shell->executeOrSimulate(array(
			'cd ' . escapeshellarg($targetPath . $webDirectory),
			$commandPrefix . implode(' ', array_map('escapeshellarg', $cliArguments))
		), $node, $deployment);
	}

	/**
	 * Simulate this task
	 *
	 * @param Node $node
	 * @param Application $application
	 * @param Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$this->execute($node, $application, $deployment, $options);
	}

	/**
	 * @param Node $node
	 * @param Application $application
	 * @param Deployment $deployment
	 * @param array $options
	 * @return string
	 * @throws InvalidConfigurationException
	 */
	protected function getAvailableCliPackage(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		if ($this->packageExists('typo3_console', $node, $application, $deployment, $options)) {
			return 'typo3_console';
		}

		if ($this->packageExists('coreapi', $node, $application, $deployment)) {
			return 'coreapi';
		}

		throw new InvalidConfigurationException('No suitable cli package found for this command! Make sure typo3_console or coreapi is available in your project, or remove this task in your deployment configuration!', 1405527176);
	}

	/**
	 * Checks if a composer manifest exists in the directory at the given path.
	 *
	 * If no manifest exists, a log message is recorded.
	 *
	 * @param string $packageKey
	 * @param Node $node
	 * @param CMS $application
	 * @param Deployment $deployment
	 * @param array $options
	 * @return boolean
	 */
	protected function packageExists($packageKey, Node $node, CMS $application, Deployment $deployment, array $options = array()) {
		if (isset($options['useApplicationWorkspace']) && $options['useApplicationWorkspace'] === TRUE) {
			$targetPath = $deployment->getWorkspacePath($application);
		} else {
			$targetPath = $deployment->getApplicationReleasePath($application);
		}
		$packagePath = \TYPO3\Flow\Utility\Files::concatenatePaths(array($targetPath, 'typo3conf/ext/' . $packageKey));
		return $this->shell->executeOrSimulate('test -d ' . escapeshellarg($packagePath), $node, $deployment, TRUE) === FALSE ? FALSE : TRUE;
	}
}
