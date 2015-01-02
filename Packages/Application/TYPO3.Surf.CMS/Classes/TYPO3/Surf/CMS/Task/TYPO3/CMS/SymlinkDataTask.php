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

/**
 * A symlink task for linking the shared data directory
 */
class SymlinkDataTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Executes this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$targetReleasePath = $deployment->getApplicationReleasePath($application);
		$applicationRootDirectory = $application->hasOption('applicationRootDirectory') ? rtrim($application->getOption('applicationRootDirectory'), '/') . '/' : '';
		$commands = array(
			"cd $targetReleasePath/$applicationRootDirectory",
			'{ [ -d ../../shared/Data/fileadmin ] || mkdir -p ../../shared/Data/fileadmin ; }',
			'{ [ -d ../../shared/Data/uploads ] || mkdir -p ../../shared/Data/uploads ; }',
			"ln -snvf ../../shared/Data/fileadmin fileadmin",
			"ln -snvf ../../shared/Data/uploads uploads"
		);
		if (isset($options['directories']) && is_array($options['directories'])) {
			foreach ($options['directories'] as $directory) {
				$targetDirectory = escapeshellarg('../../shared/Data/' . $directory);
				$commands[] = '{ [ -d ' . $targetDirectory . ' ] || mkdir -p ' . $targetDirectory . ' ; }';
				$commands[] = 'ln -snvf ' . escapeshellarg(str_repeat('../', substr_count(trim($directory, '/'), '/')) . '../../shared/Data/' . $directory) . ' ' . escapeshellarg($directory);
			}
		}
		$this->shell->executeOrSimulate($commands, $node, $deployment);
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