<?php
/**
 * User: Jens
 * Date: 31-8-2017
 * Time: 17:24
 */

namespace CloudControl\Cms\storage\storage;


use CloudControl\Cms\components\CmsComponent;

class ActivityLogStorage extends AbstractStorage
{
    public function getActivityLog()
    {
        return $this->repository->activityLog;
    }

    public function add($message, $icon = null)
    {
        $activity = $this->createActivity($message, $icon);

        $activityLog = $this->repository->activityLog;
        $activityLog[] = $activity;
        usort($activityLog, array($this, 'cmp'));
        $activityLog = array_slice($activityLog, 0, 100);
        $this->repository->activityLog = $activityLog;
        $this->repository->save();
    }

    /**
     * @param $message
     * @param $icon
     * @return \stdClass
     */
    private function createActivity($message, $icon)
    {
        $stdObj = new \stdClass();
        $stdObj->timestamp = time();
        $stdObj->message = $message;
        $stdObj->icon = $icon;
        $ccSessionObj = $_SESSION[CmsComponent::SESSION_PARAMETER_CLOUD_CONTROL];
        $stdObj->user = isset($ccSessionObj->username) ? $ccSessionObj->username : 'undefined';
        return $stdObj;
    }

    /**
     * Compare a redirect by it's title
     * @param $a
     * @param $b
     * @return int
     */
    public static function cmp($a, $b)
    {
        return $a->timestamp < $b->timestamp;
    }

}