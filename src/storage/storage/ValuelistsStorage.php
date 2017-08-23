<?php
/**
 * User: Jens
 * Date: 8-6-2017
 * Time: 14:18
 */

namespace CloudControl\Cms\storage\storage;


use CloudControl\Cms\storage\factories\ValuelistFactory;

class ValuelistsStorage extends AbstractStorage
{
    /**
     * Get all valuelists
     *
     * @return mixed
     */
    public function getValuelists()
    {
        return $this->repository->valuelists;
    }

    public function addValuelist($postValues)
    {
        $valueListObject = ValuelistFactory::createValuelistFromPostValues($postValues);
        $valuelists = $this->repository->valuelists;
        $valuelists[] = $valueListObject;
        $this->repository->valuelists = $valuelists;
        $this->save();
    }

    /**
     * Save changes to a valuelist
     *
     * @param $slug
     * @param $postValues
     *
     * @throws \Exception
     */
    public function saveValuelist($slug, $postValues)
    {
        $valuelistObject = ValuelistFactory::createValuelistFromPostValues($postValues);

        $valuelists = $this->repository->valuelists;
        foreach ($valuelists as $key => $valuelist) {
            if ($valuelist->slug == $slug) {
                $valuelists[$key] = $valuelistObject;
            }
        }
        $this->repository->valuelists = $valuelists;
        $this->save();
    }

    /**
     * Get a valuelist by its slug
     *
     * @param $slug
     *
     * @return mixed
     */
    public function getValuelistBySlug($slug)
    {
        $valuelists = $this->repository->valuelists;
        foreach ($valuelists as $valuelist) {
            if ($valuelist->slug == $slug) {
                return $valuelist;
            }
        }

        return null;
    }

    /**
     * Delete a sitemap item by its slug
     *
     * @param $slug
     *
     * @throws \Exception
     */
    public function deleteValuelistBySlug($slug)
    {
        $valuelists = $this->repository->valuelists;
        foreach ($valuelists as $key => $valuelist) {
            if ($valuelist->slug == $slug) {
                unset($valuelists[$key]);
            }
        }
        $valuelists = array_values($valuelists);
        $this->repository->valuelists = $valuelists;
        $this->save();
    }
}