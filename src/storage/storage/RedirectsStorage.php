<?php
/**
 * Created by jensk on 22-6-2017.
 */

namespace CloudControl\Cms\storage\storage;


use CloudControl\Cms\storage\factories\RedirectsFactory;

class RedirectsStorage extends AbstractStorage
{
    /**
     * Get all redirects
     *
     * @return mixed
     */
    public function getRedirects()
    {
        $redirects = $this->repository->redirects;
        if ($redirects === null) {
            $redirects = array();
        }
        usort($redirects, array($this, 'cmp'));
        return $redirects;
    }

    /**
     * Add a new redirect
     * @param $postValues
     */
    public function addRedirect($postValues) {
        $redirectObject = RedirectsFactory::createRedirectFromPostValues($postValues);
        $redirects = $this->repository->redirects;
        $redirects[] = $redirectObject;
        $this->repository->redirects = $redirects;
        $this->save();
    }

    /**
     * Get a redirect by it's slug
     *
     * @param $slug
     * @return \stdClass|null
     */
    public function getRedirectBySlug($slug)
    {
        $redirects = $this->repository->redirects;
        foreach ($redirects as $redirect) {
            if ($redirect->slug == $slug) {
                return $redirect;
            }
        }

        return null;
    }

    /**
     * Save a redirect by it's slug
     * @param $slug
     * @param $postValues
     */
    public function saveRedirect($slug, $postValues)
    {
        $redirectObject = RedirectsFactory::createRedirectFromPostValues($postValues);

        $redirects = $this->repository->redirects;
        foreach ($redirects as $key => $redirect) {
            if ($redirect->slug == $slug) {
                $redirects[$key] = $redirectObject;
            }
        }
        $this->repository->redirects = $redirects;
        $this->save();
    }

    /**
     * Delete a redirect by it's slug
     * @param $slug
     */
    public function deleteRedirectBySlug($slug)
    {
        $redirects = $this->repository->redirects;
        foreach ($redirects as $key => $redirect) {
            if ($redirect->slug == $slug) {
                unset($redirects[$key]);
            }
        }
        $redirects = array_values($redirects);
        $this->repository->redirects = $redirects;
        $this->save();
    }

    /**
     * Compare a redirect by it's title
     * @param $a
     * @param $b
     * @return int
     */
    public static function cmp($a, $b) {
        return strcmp($a->title, $b->title);
    }
}