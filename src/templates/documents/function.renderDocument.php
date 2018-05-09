<?php
/**
 * @param \CloudControl\Cms\storage\entities\Document $document
 * @param string $path
 * @param \CloudControl\Cms\cc\Request $request
 * @param string $cmsPrefix
 */
function renderDocument($document, $path, $request, $cmsPrefix)
{ ?>
    <?php
    $documentSlug = substr($path, 1) . ($path === '/' ? '' : '/') . $document->slug;
    $editDocumentLink = $request::$subfolders . $cmsPrefix . '/documents/edit-document?slug=' . $documentSlug;
    $deleteDocumentLink = $request::$subfolders . $cmsPrefix . '/documents/delete-document?slug=' . $documentSlug;
    ?>
  <td class="icon" title="<?= $document->type ?>">
    <i class="fa fa-file-text-o"></i>
  </td>
  <td class="icon" title="<?= $document->state ?>">
    <i class="fa <?= $document->state === 'published' ? 'fa-check-circle-o' : 'fa-times-circle-o' ?>"></i>
  </td>
  <td>
    <a href="<?= $editDocumentLink ?>"><?= $document->title ?></a>
      <?php if ($document->unpublishedChanges) : ?>
        <small class="small unpublished-changes">Unpublished Changes</small>
      <?php endif ?>
  </td>
  <td class="icon context-menu-container">
    <div class="context-menu">
      <i class="fa fa-ellipsis-v"></i>
      <ul>
        <li>
          <a href="<?= $editDocumentLink ?>">
            <i class="fa fa-pencil"></i>
            Edit
          </a>
        </li>
          <?php if ($document->state === 'unpublished' || $document->unpublishedChanges) : ?>
            <li>
              <a href="<?= $request::$subfolders . $cmsPrefix . '/documents/publish-document?slug=' . $documentSlug ?>">
                <i class="fa fa-check"></i>
                Publish
              </a>
            </li>
          <?php endif ?>
          <?php if ($document->state === 'published') : ?>
            <li>
              <a href="<?= $request::$subfolders . $cmsPrefix . '/documents/unpublish-document?slug=' . $documentSlug ?>">
                <i class="fa fa-times"></i>
                Unpublish
              </a>
            </li>
          <?php endif ?>
          <?php if ($document->state === 'unpublished') : ?>
            <li>
              <a href="<?= $deleteDocumentLink ?>" onclick="return confirm('Are you sure you want to delete this document?');">
                <i class="fa fa-trash"></i>
                Delete
              </a>
            </li>
          <?php endif ?>
      </ul>
    </div>
  </td>
<?php } ?>
