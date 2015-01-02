<?php
namespace TYPO3\Surf\CMS\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf.CMS".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\Flow\Annotations as Flow;

/**
 * A symlink task for linking the shared data directory
 * If the symlink target has folder, the folders themselves must exist!
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
		$applicationRootDirectory = isset($options['applicationRootDirectory']) ? trim($options['applicationRootDirectory'], '/') : '';
		$workingDirectory = escapeshellarg(Files::concatenatePaths(array($targetReleasePath, $applicationRootDirectory)));
		$relativeDataPath = '../../shared/Data';
		if (!empty($applicationRootDirectory)) {
			$relativeDataPath = str_repeat('../', substr_count(trim($applicationRootDirectory, '/'), '/') + 1) . $relativeDataPath;
		}
		$commands = array(
			"cd $workingDirectory",
			"{ [ -d {$relativeDataPath}/fileadmin ] || mkdir -p {$relativeDataPath}/fileadmin ; }",
			"{ [ -d {$relativeDataPath}/uploads ] || mkdir -p {$relativeDataPath}/uploads ; }",
			"ln -snvf {$relativeDataPath}/fileadmin",
			"ln -snvf {$relativeDataPath}/uploads"
		);
		if (isset($options['directories']) && is_array($options['directories'])) {
			foreach ($options['directories'] as $directory) {
				$targetDirectory = escapeshellarg("{$relativeDataPath}/{$directory}");
				$commands[] = '{ [ -d ' . $targetDirectory . ' ] || mkdir -p ' . $targetDirectory . ' ; }';
				$commands[] = 'ln -snvf ' . escapeshellarg(str_repeat('../', substr_count(trim($directory, '/'), '/')) . "$relativeDataPath/$directory") . ' ' . escapeshellarg($directory);
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