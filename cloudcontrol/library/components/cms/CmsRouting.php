<?php
/**
 * Created by IntelliJ IDEA.
 * User: jensk
 * Date: 30-1-2017
 * Time: 13:06
 */

namespace library\components\cms;


use library\cc\Request;
use library\components\CmsComponent;

interface CmsRouting
{
	/**
	 * CmsRouting constructor.
	 *
	 * @param Request $request
	 * @param string $relativeCmsUri
	 * @param CmsComponent $cmsComponent
	 */
	public function __construct($request, $relativeCmsUri, $cmsComponent);
}