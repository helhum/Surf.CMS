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
 * Creates the package states file and removes all not active packages from the according folders
 * This task is meant to be used for local packaging only
 */
class CreatePackageStatesTask extends AbstractCliTask {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		if (!$application instanceof CMS) {
			throw new InvalidConfigurationException('Application must be of type TYPO3 CMS when executing this task!', 1420210955);
		}
		$options = array_replace($options, array('useApplicationWorkspace' => TRUE));
		if (!$this->packageStatesFileExists($node, $application, $deployment, $options)) {
			if ($this->packageExists('typo3_console', $node, $application, $deployment, $options)) {
				$this->executeCliCommand(array('./typo3cms', 'install:generatepackagestates', '--remove-inactive-packages'), $node, $application, $deployment, $options);
			} else {
				throw new InvalidConfigurationException('No package states file found in the repository and no typo3_console package found to generate it. Stopping packaging!', 1420210956);
			}
		}
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
	 * Checks if the package states file exists
	 *
	 * If no manifest exists, a log message is recorded.
	 *
	 * @param Node $node
	 * @param CMS $application
	 * @param Deployment $deployment
	 * @param array $options
	 * @return boolean
	 */
	protected function packageStatesFileExists(Node $node, CMS $application, Deployment $deployment, array $options = array()) {
		return $this->fileExists('typo3conf/PackageStates.php', $node, $application, $deployment, $options);
	}
}
