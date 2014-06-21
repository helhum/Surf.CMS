<?php
namespace TYPO3SurfCms\SurfTools\Task;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3SurfCms.SurfTools".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\Flow\Annotations as Flow;

/**
 * Clear TYPO3 caches
 * This task requires the extension coreapi.
 */
class ClearCacheTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Execute this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$targetReleasePath = $deployment->getApplicationReleasePath($application);
		$httpHost = $node->getOption('HTTP_HOST');
		$webDirectory = $application->hasOption('webDirectory') ? rtrim($application->getOption('webDirectory'), '/') . '/' : '';
		$this->shell->executeOrSimulate(array(
			'cd ' . escapeshellarg($targetReleasePath),
				($httpHost ? 'HTTP_HOST=' . escapeshellarg($httpHost) . ' ' : '') . 'php ' . $webDirectory . 'typo3/cli_dispatch.phpsh extbase cacheapi:clearallcaches'
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

}
?>