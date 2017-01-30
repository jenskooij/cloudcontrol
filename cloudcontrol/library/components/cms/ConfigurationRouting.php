<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 12:59
 */

namespace library\components\cms;


use library\cc\Request;
use library\components\cms\configuration\ApplicationComponentRouting;
use library\components\cms\configuration\BricksRouting;
use library\components\cms\configuration\DocumentTypeRouting;
use library\components\cms\configuration\ImageSetRouting;
use library\components\cms\configuration\UsersRouting;
use library\components\CmsComponent;

class ConfigurationRouting implements CmsRouting
{
	/**
	 * ConfigurationRouting constructor.
	 *
	 * @param Request $request
	 * @param String $relativeCmsUri
	 * @param CmsComponent $cmsComponent
	 */
	public function __construct($request, $relativeCmsUri, $cmsComponent)
	{
		if ($relativeCmsUri == '/configuration') {
			$cmsComponent->subTemplate = 'cms/configuration';
			$cmsComponent->setParameter(CmsCOmponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		}

		new UsersRouting($request, $relativeCmsUri, $cmsComponent);
		new DocumentTypeRouting($request, $relativeCmsUri, $cmsComponent);
		new BricksRouting($request, $relativeCmsUri, $cmsComponent);
		new ImageSetRouting($request, $relativeCmsUri, $cmsComponent);
		new ApplicationComponentRouting($request, $relativeCmsUri, $cmsComponent);
	}
}