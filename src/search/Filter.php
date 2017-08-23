<?php
/**
 * Created by IntelliJ IDEA.
 * User: jensk
 * Date: 21-2-2017
 * Time: 11:52
 */

namespace CloudControl\Cms\search;


interface Filter
{
	/**
	 * Filter constructor.
	 *
	 * @param array $tokens
	 */
	public function __construct($tokens);

	/**
	 * @return array
	 */
	public function getFilterResults();
}