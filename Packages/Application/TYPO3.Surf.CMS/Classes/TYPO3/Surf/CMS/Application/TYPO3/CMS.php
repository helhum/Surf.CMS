<?php
namespace TYPO3\Surf\CMS\Application\TYPO3;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf.CMS".*
 *                                                                        *
 *                                                                        */
use TYPO3\Surf\Domain\Model\Workflow;

/**
 * A TYPO3 CMS application template
 * @TYPO3\Flow\Annotations\Proxy(false)
 */
class CMS extends \TYPO3\Surf\Application\TYPO3\CMS {

	/**
	 * The production context
	 * @var string
	 */
	protected $context = 'Production';

	/**
	 * Set the application production context
	 *
	 * @param string $context
	 * @return CMS
	 */
	public function setContext($context) {
		$this->context = trim($context);
		return $this;
	}

	/**
	 * Get the application production context
	 *
	 * @return string
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * Register tasks for this application
	 *
	 * @param \TYPO3\Surf\Domain\Model\Workflow $workflow
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @return void
	 */
	public function registerTasks(\TYPO3\Surf\Domain\Model\Workflow $workflow, \TYPO3\Surf\Domain\Model\Deployment $deployment) {
		parent::registerTasks($workflow, $deployment);

		if ($deployment->hasOption('initialDeployment') && $deployment->getOption('initialDeployment') === TRUE) {
			$workflow->addTask('typo3.surf.cms:dumpDatabase', 'initialize', $this);
			$workflow->addTask('typo3.surf.cms:rsyncFolders', 'initialize', $this);
		}

		$workflow
				->afterStage(
					'update',
					array(
						'typo3.surf.cms:typo3:cms:symlinkData',
						'typo3.surf.cms:typo3:cms:copyConfiguration'
					), $this
				)
				->addTask('typo3.surf.cms:typo3:cms:compareDatabase', 'migrate', $this)
				->afterStage('switch', 'typo3.surf.cms:typo3:cms:flushCaches', $this);
	}

	/**
	 * @param Workflow $workflow
	 * @param string $packageMethod
	 */
	protected function registerTasksForPackageMethod(Workflow $workflow, $packageMethod) {
		parent::registerTasksForPackageMethod($workflow, $packageMethod);

		switch ($packageMethod) {
			case 'git':
				$workflow->defineTask('typo3.surf:composer:localInstall', 'typo3.surf:composer:install', array(
					'nodeName' => 'localhost',
					'useApplicationWorkspace' => TRUE
				));

				$workflow->afterStage('package', 'typo3.surf:composer:localInstall', $this)
					->afterTask('typo3.surf:composer:localInstall', 'typo3.surf.cms:typo3:cms:createPackageStates', $this);
				break;
		}
	}

}
