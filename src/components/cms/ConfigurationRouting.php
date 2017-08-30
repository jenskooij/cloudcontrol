<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 12:59
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\cms\configuration\ApplicationComponentRouting;
use CloudControl\Cms\components\cms\configuration\BricksRouting;
use CloudControl\Cms\components\cms\configuration\DocumentTypeRouting;
use CloudControl\Cms\components\cms\configuration\ImageSetRouting;
use CloudControl\Cms\components\cms\configuration\UsersRouting;
use CloudControl\Cms\components\CmsComponent;

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
            $cmsComponent->subTemplate = 'configuration';
            $cmsComponent->setParameter(CmsCOmponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
        }

        new UsersRouting($request, $relativeCmsUri, $cmsComponent);
        new DocumentTypeRouting($request, $relativeCmsUri, $cmsComponent);
        new BricksRouting($request, $relativeCmsUri, $cmsComponent);
        new ImageSetRouting($request, $relativeCmsUri, $cmsComponent);
        new ApplicationComponentRouting($request, $relativeCmsUri, $cmsComponent);
    }
}