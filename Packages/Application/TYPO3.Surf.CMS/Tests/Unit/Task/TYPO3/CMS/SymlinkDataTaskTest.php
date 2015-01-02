<?php
namespace TYPO3\Surf\CMS\Tests\Unit\Task\TYPO3\CMS;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Helmut Hummel <helmut.hummel@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the text file GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\Surf\CMS\Task\TYPO3\CMS\SymlinkDataTask;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Service\ShellCommandService;

/**
 * Class SymlinkDataTest
 */
class SymlinkDataTaskTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var SymlinkDataTask
	 */
	protected $task;

	/**
	 * @var ShellCommandService|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $shellMock;

	/**
	 * @var Node|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $nodeMock;

	/**
	 * @var Application|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $applicationMock;

	/**
	 * @var Deployment|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $deploymentMock;


	public function setUp() {
		$this->task = new SymlinkDataTask();
		$this->shellMock = $this->getMock(ShellCommandService::class);
		$this->inject($this->task, 'shell', $this->shellMock);
		$this->nodeMock = $this->getMock(Node::class);
		$this->deploymentMock = $this->getMock(Deployment::class);

		$this->deploymentMock->expects($this->once())
			->method('getApplicationReleasePath')
			->willReturn('/releases/current');

		$this->applicationMock = $this->getMock(Application::class);
	}

	/**
	 * @test
	 */
	public function withoutOptionsCreatesCorrectLinks() {
		$dataPath = '../../shared/Data';
		$expectedCommands = array(
			"cd '/releases/current'",
			"{ [ -d {$dataPath}/fileadmin ] || mkdir -p {$dataPath}/fileadmin ; }",
			"{ [ -d {$dataPath}/uploads ] || mkdir -p {$dataPath}/uploads ; }",
			"ln -snvf {$dataPath}/fileadmin",
			"ln -snvf {$dataPath}/uploads",
		);

		$options = array();

		$this->shellMock->expects($this->once())
			->method('executeOrSimulate')
			->with($expectedCommands, $this->nodeMock, $this->deploymentMock)
		;

		$this->task->execute($this->nodeMock, $this->applicationMock, $this->deploymentMock, $options);
	}

	/**
	 * @test
	 */
	public function withAdditionalDirectoriesCreatesCorrectLinks() {
		$dataPath = '../../shared/Data';
		$expectedCommands = array(
			"cd '/releases/current'",
			"{ [ -d {$dataPath}/fileadmin ] || mkdir -p {$dataPath}/fileadmin ; }",
			"{ [ -d {$dataPath}/uploads ] || mkdir -p {$dataPath}/uploads ; }",
			"ln -snvf {$dataPath}/fileadmin",
			"ln -snvf {$dataPath}/uploads",
			"{ [ -d '{$dataPath}/pictures' ] || mkdir -p '{$dataPath}/pictures' ; }",
			"ln -snvf '{$dataPath}/pictures' 'pictures'",
			"{ [ -d '{$dataPath}/test/assets' ] || mkdir -p '{$dataPath}/test/assets' ; }",
			"ln -snvf '../{$dataPath}/test/assets' 'test/assets'",
		);

		$options = array(
			'directories' => array('pictures', 'test/assets'),
		);

		$this->shellMock->expects($this->once())
			->method('executeOrSimulate')
			->with($expectedCommands, $this->nodeMock, $this->deploymentMock)
		;

		$this->task->execute($this->nodeMock, $this->applicationMock, $this->deploymentMock, $options);
	}

	/**
	 * @test
	 */
	public function withApplicationRootCreatesCorrectLinks() {
		$dataPath = '../../../../shared/Data';
		$expectedCommands = array(
			"cd '/releases/current/app/dir'",
			"{ [ -d {$dataPath}/fileadmin ] || mkdir -p {$dataPath}/fileadmin ; }",
			"{ [ -d {$dataPath}/uploads ] || mkdir -p {$dataPath}/uploads ; }",
			"ln -snvf {$dataPath}/fileadmin",
			"ln -snvf {$dataPath}/uploads",
		);

		$options = array(
			'applicationRootDirectory' => 'app/dir/'
		);

		$this->shellMock->expects($this->once())
			->method('executeOrSimulate')
			->with($expectedCommands, $this->nodeMock, $this->deploymentMock)
		;

		$this->task->execute($this->nodeMock, $this->applicationMock, $this->deploymentMock, $options);
	}

	/**
	 * @test
	 */
	public function withAdditionalDirectoriesAndApplicationRootCreatesCorrectLinks() {
		$dataPath = '../../../../shared/Data';
		$expectedCommands = array(
			"cd '/releases/current/app/dir'",
			"{ [ -d {$dataPath}/fileadmin ] || mkdir -p {$dataPath}/fileadmin ; }",
			"{ [ -d {$dataPath}/uploads ] || mkdir -p {$dataPath}/uploads ; }",
			"ln -snvf {$dataPath}/fileadmin",
			"ln -snvf {$dataPath}/uploads",
			"{ [ -d '{$dataPath}/pictures' ] || mkdir -p '{$dataPath}/pictures' ; }",
			"ln -snvf '{$dataPath}/pictures' 'pictures'",
			"{ [ -d '{$dataPath}/test/assets' ] || mkdir -p '{$dataPath}/test/assets' ; }",
			"ln -snvf '../{$dataPath}/test/assets' 'test/assets'",
		);

		$options = array(
			'applicationRootDirectory' => 'app/dir/',
			'directories' => array('pictures', 'test/assets'),
		);

		$this->shellMock->expects($this->once())
			->method('executeOrSimulate')
			->with($expectedCommands, $this->nodeMock, $this->deploymentMock)
		;

		$this->task->execute($this->nodeMock, $this->applicationMock, $this->deploymentMock, $options);
	}

}