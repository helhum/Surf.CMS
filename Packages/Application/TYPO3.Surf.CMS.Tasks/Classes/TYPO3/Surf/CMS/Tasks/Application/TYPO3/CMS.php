<?php
namespace TYPO3\Surf\CMS\Tasks\Application\TYPO3;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf.CMS.Tasks".*
 *                                                                        *
 *                                                                        */
use TYPO3\Surf\Domain\Model\Workflow;

/**
 * A TYPO3 CMS application template
 * @TYPO3\Flow\Annotations\Proxy(false)
 */
class CMS extends \TYPO3\Surf\Application\TYPO3\CMS {

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
			$workflow->addTask('typo3.surf.cms.tasks:dumpDatabase', 'initialize', $this);
			$workflow->addTask('typo3.surf.cms.tasks:rsyncFolders', 'initialize', $this);
		}

		$workflow
				->afterStage('transfer', 'typo3.surf.cms.tasks:symlinkData', $this)
				->afterStage('transfer', 'typo3.surf.cms.tasks:copyConfiguration', $this)
				->addTask('typo3.surf.cms.tasks:compareDatabase', 'migrate', $this)
				->afterStage('switch', 'typo3.surf.cms.tasks:flushCaches', $this);
	}

	/**
	 * @param Workflow $workflow
	 * @param string $packageMethod
	 */
	protected function registerTasksForPackageMethod(Workflow $workflow, $packageMethod) {
		switch ($packageMethod) {
			case 'composer':
				$workflow->addTask('typo3.surf:package:git', 'package', $this);
				$workflow->addTask('typo3.surf.cms.tasks:package:composer', 'package', $this);
				break;
			default:
				parent::registerTasksForPackageMethod($workflow, $packageMethod);
		}
	}


}
