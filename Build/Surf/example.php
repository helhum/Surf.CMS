<?php

/** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */


// APPLICATION
$application = new \TYPO3\Surf\CMS\Application\TYPO3\CMS();
$application->setOption('projectName', 'Introduction Package');
$application->setOption('repositoryUrl', 'git://git.typo3.org/TYPO3CMS/Distributions/Introduction.git');
$application->setOption('typo3.surf:gitCheckout[branch]', 'master');
$application->setDeploymentPath('/var/www/introduction');

// NODES
$node = new \TYPO3\Surf\Domain\Model\Node('Introduction Package on local system');
$node->setHostname('localhost');
$application->addNode($node);

// WORKFLOW
$workflow = new \TYPO3\Surf\Domain\Model\SimpleWorkflow();

// DEPLOYMENT
$deployment->setWorkflow($workflow);
$deployment->addApplication($application);
