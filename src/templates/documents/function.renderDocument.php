<?php /**
 * @param \CloudControl\Cms\storage\entities\Document $document
 * @param string $cmsPrefix
 * @param string $slugPrefix
 * @param \CloudControl\Cms\cc\Request $request
 */
function renderDocument($document, $cmsPrefix, $slugPrefix = '', $request)
{ ?>
  <div class="grid-box-10">
    <h3>
      <a class="btn documentTitle" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/documents/edit-document?slug=<?= $slugPrefix . $document->slug ?>" title="Edit">
        <i class="fa fa-file-text-o"></i>
        <small class="state <?= strtolower($document->state) ?>">
          <i class="fa <?= $document->state == 'published' ? 'fa-check-circle-o' : 'fa-times-circle-o' ?>"></i></small>
          <?= $document->title ?>
      </a>
        <?php if ($document->unpublishedChanges) : ?>
          <small class="small unpublished-changes">Unpublished Changes</small>
        <?php endif ?>
      <small class="small documentType"><?= $document->documentType ?></small>
      <small class="small lastModified" title="<?= date('r', $document->lastModificationDate) ?>">
        <span class="label">Modified:</span>
          <?= \CloudControl\Cms\cc\StringUtil::timeElapsedString($document->lastModificationDate) ?>
      </small>
      <small class="small lastModifiedBy">
        <span class="label">By:</span>
          <?= $document->lastModifiedBy ?>
      </small>
    </h3>
  </div>
  <div class="documentActions grid-box-2">
      <?php renderAction(
          $document->state == 'unpublished' || $document->unpublishedChanges,
          'Publish',
          'publish',
          $request::$subfolders . $cmsPrefix . '/documents/publish-document?slug=' . $slugPrefix . $document->slug,
          'check'); ?>
      <?php renderAction(
          $document->state == 'published',
          'Unpublish',
          'unpublish',
          $request::$subfolders . $cmsPrefix . '/documents/unpublish-document?slug=' . $slugPrefix . $document->slug,
          'times'); ?>
      <?php renderAction(
          true,
          'Edit',
          '',
          $request::$subfolders . $cmsPrefix . '/documents/edit-document?slug=' . $slugPrefix . $document->slug,
          'pencil'); ?>
      <?php renderAction(
          $document->state == 'unpublished',
          'Delete',
          'error',
          $request::$subfolders . $cmsPrefix . '/documents/delete-document?slug=' . $slugPrefix . $document->slug,
          'trash',
          /** @scrutinizer ignore-type */ 'return confirm(\'Are you sure you want to delete this document?\');'); ?>
  </div>
<?php } ?>
