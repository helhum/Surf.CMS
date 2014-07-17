<?php
namespace TYPO3SurfCms\SurfTools\Application;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3SurfCms.SurfTools".*
 *                                                                        *
 *                                                                        */

/**
 * A TYPO3 CMS application template
 * @TYPO3\Flow\Annotations\Proxy(false)
 */
class CMS extends \TYPO3\Surf\Application\TYPO3\CMS {

	/**
	 * Constructor
	 *
	 * @param string $name
	 */
	public function __construct($name = 'TYPO3 CMS') {
		parent::__construct($name);
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

		$workflow
				->afterStage('transfer', 'typo3surfcms.surftools:symlinkData', $this)
				->addTask('typo3surfcms.surftools:compareDatabase', 'migrate', $this)
				->afterStage('switch', 'typo3surfcms.surftools:clearCache', $this);
	}

}
?>