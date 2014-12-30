<?php
namespace TYPO3\Surf\CMS\Task\Package;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf.CMS".*
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Surf\CMS\Task\AbstractTypo3CliTask;

/**
 * Installs the composer packages based on a composer.json file in the projects root folder
 */
class ComposerTask extends AbstractTypo3CliTask {

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
		$composerRootPath = $deployment->getWorkspacePath($application);
		$node = $deployment->getNode('localhost');

		if ($this->composerManifestExists($composerRootPath, $node, $deployment)) {
			$command = $this->buildComposerInstallCommand($composerRootPath, $options);
			$this->shell->executeOrSimulate($command, $node, $deployment);
			$options = array('useApplicationWorkspace' => TRUE);
			if ($this->packageExists('typo3_console', $node, $application, $deployment, $options)) {
				$this->executeCliCommand(array('./typo3cms', 'install:generatepackagestates', '--remove-inactive-packages'), $node, $application, $deployment, $options);
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
	 * Build the composer command to "install --no-dev" in the given $path.
	 *
	 * @param string $manifestPath
	 * @param array $options
	 * @return string
	 * @throws \TYPO3\Surf\Exception\TaskExecutionException
	 */
	protected function buildComposerInstallCommand($manifestPath, array $options) {
		if (!isset($options['composerCommandPath'])) {
			throw new \TYPO3\Surf\Exception\TaskExecutionException('Composer command not found. Set the composerCommandPath option.', 1349163257);
		}
		return sprintf('cd %s && %s install --no-ansi --no-interaction --no-dev --no-progress', escapeshellarg($manifestPath), escapeshellcmd($options['composerCommandPath']));
	}

	/**
	 * Checks if a composer manifest exists in the directory at the given path.
	 *
	 * If no manifest exists, a log message is recorded.
	 *
	 * @param string $path
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @return boolean
	 */
	protected function composerManifestExists($path, Node $node, Deployment $deployment) {
		$composerJsonPath = \TYPO3\Flow\Utility\Files::concatenatePaths(array($path, 'composer.json'));
		$composerJsonExists = $this->shell->executeOrSimulate('test -f ' . escapeshellarg($composerJsonPath), $node, $deployment, TRUE);
		if ($composerJsonExists === FALSE) {
			$deployment->getLogger()->log('No composer.json found in path "' . $composerJsonPath . '"', LOG_DEBUG);
			return FALSE;
		}

		return TRUE;
	}
}
?>