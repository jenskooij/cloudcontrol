<?php include('documents/function.renderAction.php'); ?>
<?php include('documents/function.renderDocument.php'); ?>
<?php include('documents/function.renderFolder.php'); ?>
<section class="documents">
  <h2>
    <i class="fa fa-file-text-o"></i>
    Documents
  </h2>
    <?php if (isset($infoMessage)) : ?>
      <div class="infoMessage <?= isset($infoMessageClass) ? $infoMessageClass : '' ?>">
        <div class="content">
            <?= $infoMessage ?>
        </div>
      </div>
    <?php endif ?>
  <div class="search">
      <?php if ($searchNeedsUpdate) : ?>
        <div class="message warning">
          <i class="fa fa-exclamation-triangle"></i>
          Search index is no longer in sync with documents.
          <a href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/search/update-index?returnUrl=<?= urlencode($request::$subfolders . $cmsPrefix . '/documents') ?>" title="Update Index">Update Index</a>
        </div>
      <?php else : ?>
        <div class="message valid">
          <i class="fa fa-check"></i>
          Search index is in sync with documents.
        </div>
      <?php endif ?>
  </div>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $request::$subfolders . $cmsPrefix ?>/documents/new-document?path=<?= $path ?>" title="New Document">
          +
          <i class="fa fa-file-text-o"></i>
        </a>
        <a class="btn" href="<?= $request::$subfolders . $cmsPrefix ?>/documents/new-folder?path=<?= $path ?>" title="New Folder">
          +
          <i class="fa fa-folder-o"></i>
        </a>
      </li>
    </ul>
  </nav>
  <table class="documents">
      <?php foreach ($documents as $document) : ?>
        <tr>
            <?php if ($document->type === 'folder') : ?>
                <?php
                $folderPath = $path . ($path === '/' ? '' : '/') . $document->slug;
                $folderSlug = substr($path, 1);
                $openFolderLink = '?path=' . $folderPath;
                $editFolderLink = $request::$subfolders . $cmsPrefix . '/documents/edit-folder?slug=' . $folderSlug . ($path === '/' ? '' : '/') . $document->slug;
                $deleteFolderLink = $request::$subfolders . $cmsPrefix . '/documents/delete-folder?slug=' . $folderSlug . ($path === '/' ? '' : '/') . $document->slug;
                ?>
              <td class="icon" title="<?= $document->type ?>">
                <i class="fa fa-folder-o"></i>
              </td>
              <td class="icon"></td>
              <td>
                <a href="<?= $openFolderLink ?>"><?= $document->title ?></a>
              </td>
              <td class="icon">
                <div class="context-menu">
                  <i class="fa fa-ellipsis-v"></i>
                  <ul>
                    <li>
                      <a href="<?= $editFolderLink ?>">
                        <i class="fa fa-pencil"></i>
                        Rename
                      </a>
                    </li>
                    <li>
                      <a href="<?= $deleteFolderLink ?>" onclick="return confirm('Are you sure you want to delete this folder?');">
                        <i class="fa fa-trash"></i>
                        Delete
                      </a>
                    </li>
                  </ul>
                </div>
              </td>
            <?php else : ?>
                <?php
                $documentSlug = substr($path, 1) . ($path === '/' ? '' : '/') . $document->slug;
                $editDocumentLink = $request::$subfolders . $cmsPrefix . '/documents/edit-document?slug=' . $documentSlug;
                $deleteDocumentLink = $request::$subfolders . $cmsPrefix . '/documents/delete-document?slug=' . $documentSlug;
                ?>
              <td class="icon" title="<?= $document->type ?>">
                <i class="fa fa-file-text-o"></i>
              </td>
              <td class="icon">
                <i class="fa <?= $document->state === 'published' ? 'fa-check-circle-o' : 'fa-times-circle-o' ?>"></i>
              </td>
              <td>
                <a href="<?= $editDocumentLink ?>"><?= $document->title ?></a>
                  <?php if ($document->unpublishedChanges) : ?>
                    <small class="small unpublished-changes">Unpublished Changes</small>
                  <?php endif ?>
              </td>
              <td class="icon">
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
                          <a href="<?= $request::$subfolders . $cmsPrefix . '/documents/publish-document?slug=' . ($path === '/' ? '' : '/') . $document->slug ?>">
                            <i class="fa fa-check"></i>
                            Publish
                          </a>
                        </li>
                      <?php endif ?>
                      <?php if ($document->state === 'published') : ?>
                        <li>
                          <a href="<?= $request::$subfolders . $cmsPrefix . '/documents/unpublish-document?slug=' . ($path === '/' ? '' : '/') . $document->slug ?>">
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
            <?php endif ?>
        </tr>
      <?php endforeach ?>
  </table>
</section>