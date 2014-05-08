<?php
namespace Networkteam\Import\Tests\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

class TransformingProviderDecoratorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \Networkteam\Import\DataProvider\TransformingProviderDecorator
	 */
	protected $transformingProviderDecorator;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	protected $dataProvider;

	/**
	 * @var array
	 */
	protected static $dataRow;

	/**
	 * @return array
	 */
	protected function getDataRow() {
		return array(
			'bezeichnung' => 'Satsch',
			'vorname' => 'Harald',
			'email-adresse' => 'foo@example.com',
			'dummyField' => NULL,
		);
	}

	public function setUp() {
		$this->dataProvider = $this->getMockBuilder('Networkteam\Import\DataProvider\DataProviderInterface')
			->disableArgumentCloning()
			->getMock();
		$this->transformingProviderDecorator = new \Networkteam\Import\DataProvider\TransformingProviderDecorator($this->dataProvider);
	}

	/**
	 * @test
	 */
	public function implementsDataProviderInterface() {
		$this->assertInstanceOf('Networkteam\Import\DataProvider\DataProviderInterface', $this->transformingProviderDecorator);
	}

	/**
	 * @test
	 */
	public function dataIsTransformedAccordingToMapping() {
		$configuration = array(
			'firstName' => 'vorname',
			'lastName' => 'bezeichnung'
		);
		$this->dataProvider
			->expects($this->atLeastOnce())
			->method('current')
			->will($this->returnValue(self::getDataRow()));

		$this->transformingProviderDecorator->setMapping($configuration);
		$this->assertEquals(array(
			'firstName' => 'Harald',
			'lastName' => 'Satsch'
		), $this->transformingProviderDecorator->current());
	}

	/**
	 * @test
	 * @expectedException \Networkteam\Import\Exception\ConfigurationException
	 * @expectedExceptionMessage The key "does_not_exist" was not found in the list of keys: bezeichnung, vorname, email-adresse, dummyField
	 */
	public function exceptionIsThrownForInvalidMapping() {
		$configuration = array(
			'firstName' => 'does_not_exist',
		);
		$this->dataProvider
			->expects($this->atLeastOnce())
			->method('current')
			->will($this->returnValue(self::getDataRow()));

		$this->transformingProviderDecorator->setMapping($configuration);
		$this->transformingProviderDecorator->current();
	}

	/**
	 * @test
	 */
	public function mappingCanContainExpressions() {
		$configuration = array(
			'firstName' => 'vorname',
			'lastName' => 'bezeichnung',
			'parent' => '${\'1234\'}'
		);
		$this->dataProvider
			->expects($this->atLeastOnce())
			->method('current')
			->will($this->returnValue(self::getDataRow()));

		$this->transformingProviderDecorator->setMapping($configuration);
		$this->transformingProviderDecorator->current();
	}

	/**
	 * @test
	 */
	public function expressionsAreEvaluatedCorrectly() {
		$configuration = array(
			'firstName' => 'vorname',
			'lastName' => 'bezeichnung',
			'parent' => '${\'1234\'}',
			'name' => '${row["vorname"] ~ \' \' ~ row["bezeichnung"]}'
		);
		$this->dataProvider
			->expects($this->atLeastOnce())
			->method('current')
			->will($this->returnValue(self::getDataRow()));

		$this->transformingProviderDecorator->setMapping($configuration);
		$this->assertEquals(array(
			'firstName' => 'Harald',
			'lastName' => 'Satsch',
			'parent' => '1234',
			'name' => 'Harald Satsch'
		), $this->transformingProviderDecorator->current());
	}

	/**
	 * @test
	 */
	public function functionsInExpressionsCanBeUsed() {
		$configuration = array(
			'name' => '${helper.substr(row["vorname"], 1, 4)}'
		);
		$this->dataProvider
			->expects($this->atLeastOnce())
			->method('current')
			->will($this->returnValue(self::getDataRow()));

		$this->transformingProviderDecorator->setMapping($configuration);
		$this->assertEquals(array(
			'name' => 'aral'
		), $this->transformingProviderDecorator->current());
	}

	/**
	 * @test
	 */
	public function nullValuesInInputDataIsAcceptedAsPresent() {
		$configuration = array(
			'firstName' => 'vorname',
			'dummyField' => 'dummyField',
		);
		$this->dataProvider
			->expects($this->atLeastOnce())
			->method('current')
			->will($this->returnValue(self::getDataRow()));

		$this->transformingProviderDecorator->setMapping($configuration);
		$this->assertEquals(array(
			'firstName' => 'Harald',
			'dummyField' => NULL
		), $this->transformingProviderDecorator->current());
	}

}
