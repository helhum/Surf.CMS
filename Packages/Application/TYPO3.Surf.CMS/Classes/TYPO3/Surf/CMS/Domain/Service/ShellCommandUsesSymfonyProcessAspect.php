<?php
namespace TYPO3\Surf\CMS\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Symfony\Component\Process\Process;
use TYPO3\Flow\Annotations as Flow;

/**
 * An aspect which cares for a special publishing of private resources.
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class ShellCommandUsesSymfonyProcessAspect {

	/**
	 * Returns exit code and output
	 *
	 * @Flow\Around("method(TYPO3\Surf\Domain\Service\ShellCommandService->executeProcess())")
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint The current join point
	 * @return array
	 */
	public function useSymfonyProcessInsteadOfPopen(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$deployment = $joinPoint->getMethodArgument('deployment');
		$command = $joinPoint->getMethodArgument('command');
		$logOutput = $joinPoint->getMethodArgument('logOutput');
		$logPrefix = $joinPoint->getMethodArgument('logPrefix');
		$process = new Process($command);
		$process->setTimeout(NULL);
		$callback = NULL;
		if ($logOutput) {
			$callback = function($type, $data) use ($deployment, $logPrefix) {
				if ($type === Process::OUT) {
					$deployment->getLogger()->log($logPrefix . trim($data), LOG_DEBUG);
				} elseif ($type === Process::ERR) {
					$deployment->getLogger()->log($logPrefix . trim($data), LOG_ERR);
				}
			};
		}
		$exitCode = $process->run($callback);
		return array($exitCode, trim($process->getOutput()));
	}
}
