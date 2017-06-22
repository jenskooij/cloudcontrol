<?php
/**
 * Created by jensk on 22-6-2017.
 */

namespace library\storage\factories;


use library\cc\StringUtil;

class RedirectsFactory
{
    /**
     * Create a new redirect object from postvalues
     *
     * @param $postValues
     * @return \stdClass
     * @throws \Exception
     */
    public static function createRedirectFromPostValues($postValues)
    {
        if (isset($postValues['title'], $postValues['fromUrl'], $postValues['toUrl'])) {
            $redirectObject = new \stdClass();
            $redirectObject->title = $postValues['title'];
            $redirectObject->slug = StringUtil::slugify($postValues['title']);
            $redirectObject->fromUrl = $postValues['fromUrl'];
            $redirectObject->toUrl = $postValues['toUrl'];
            $redirectObject->type = $postValues['type'];

            return $redirectObject;
        } else {
            throw new \Exception('Trying to create valuelist with invalid data.');
        }
    }
}