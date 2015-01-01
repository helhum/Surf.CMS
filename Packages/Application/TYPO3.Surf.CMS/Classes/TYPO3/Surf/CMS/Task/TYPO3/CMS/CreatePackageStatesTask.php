<?php
namespace TYPO3\Surf\CMS\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf.CMS".*
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Surf\CMS\Task\TYPO3\CMS\AbstractCliTask;

/**
 * Creates the package states file and removes all not active packages from the according folders
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
		$node = $deployment->getNode('localhost');
		$options = array('useApplicationWorkspace' => TRUE);
		if ($this->packageExists('typo3_console', $node, $application, $deployment, $options)) {
			$this->executeCliCommand(array('./typo3cms', 'install:generatepackagestates', '--remove-inactive-packages'), $node, $application, $deployment, $options);
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

}
