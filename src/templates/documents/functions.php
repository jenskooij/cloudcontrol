<?php
function getDocumentSlug($path, $document)
{
    return substr($path, 1) . ($path === '/' ? '' : '/') . $document->slug;
}

function getEditDocumentLink($request, $cmsPrefix, $path, $document)
{
    return $request::$subfolders . $cmsPrefix . '/documents/edit-document?slug=' . getDocumentSlug($path, $document);
}

function getDeleteDocumentLink($request, $cmsPrefix, $path, $document)
{
    return $request::$subfolders . $cmsPrefix . '/documents/delete-document?slug=' . getDocumentSlug($path, $document);
}

function getPublishDocumentLink($request, $cmsPrefix, $path, $document)
{
    return $request::$subfolders . $cmsPrefix . '/documents/publish-document?slug=' . getDocumentSlug($path, $document);
}

function getUnpublishDocumentLink($request, $cmsPrefix, $path, $document)
{
    return $request::$subfolders . $cmsPrefix . '/documents/unpublish-document?slug=' . getDocumentSlug($path, $document);
}

function getFolderPath($path, $document)
{
    return $path . ($path === '/' ? '' : '/') . $document->slug;
}

function openFolderLink($path, $document)
{
    return '?path=' . getFolderPath($path, $document);
}

function getFolderSlug($path)
{
    return substr($path, 1);
}

function getDeleteFolderLink($request, $cmsPrefix, $path, $document)
{
    return $request::$subfolders . $cmsPrefix . '/documents/delete-folder?slug=' . getFolderSlug($path) . ($path === '/' ? '' : '/') . $document->slug;
}

?>