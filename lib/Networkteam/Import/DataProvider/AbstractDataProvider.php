<?php
namespace Networkteam\Import\DataProvider;

/***************************************************************
 *  (c) 2014 networkteam GmbH - all rights reserved
 ***************************************************************/

abstract class AbstractDataProvider implements DataProviderInterface {

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @param array $options
	 * @return mixed|void
	 */
	public function setOptions(array $options) {
		foreach($options as $option) {

		}
	}
}