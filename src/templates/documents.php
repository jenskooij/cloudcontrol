<?php include('documents/function.renderAction.php'); ?>
<?php include('documents/function.renderDocument.php'); ?>
<?php include('documents/function.renderFolder.php'); ?>
<script>window.onload = function () {
    History.init();
    History.replaceState(null, 'Cloud Control CMS', '/<?=$request::$subfolders . $cmsPrefix?>/documents?path=/');
  };</script>
<section class="documents">
  <h2><i class="fa fa-file-text-o"></i> Documents</h2>
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
          <i class="fa fa-exclamation-triangle"></i> Search index is no longer in sync with documents.
          <a href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/search/update-index?returnUrl=<?= urlencode($request::$subfolders . $cmsPrefix . '/documents') ?>" title="Update Index">Update Index</a>
        </div>
      <?php else : ?>
        <div class="message valid">
          <i class="fa fa-check"></i> Search index is in sync with documents.
        </div>
      <?php endif ?>
  </div>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" onmousedown="this.setAttribute('href', '<?= $request::$subfolders ?><?= $cmsPrefix ?>/documents/new-document?path=' + getParameterByName('path'));" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/documents/new-document" title="New Document">
          + <i class="fa fa-file-text-o"></i>
        </a>
        <a class="btn" onmousedown="this.setAttribute('href', '<?= $request::$subfolders ?><?= $cmsPrefix ?>/documents/new-folder?path=' + getParameterByName('path'));" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/documents/new-folder" title="New Folder">
          + <i class="fa fa-folder-o"></i>
        </a>
      </li>
    </ul>
  </nav>
    <?php if (isset($documents)) : ?>
      <ul class="documents grid-wrapper">
        <li class="grid-container">
          <div class="grid-box-12">
            <i class="fa fa-terminal" title="Path"></i>
            <i id="pathHolder">/</i>
          </div>
        </li>
          <?php foreach ($documents as $document) : ?>
            <li class="grid-container">
                <?php if ($document->type == 'document') : ?>
                    <?php renderDocument($document, $cmsPrefix, '', $request); ?>
                <?php elseif ($document->type == 'folder') : ?>
                    <?php renderFolder($document, $cmsPrefix, '', true, $request); ?>
                <?php endif ?>
            </li>
          <?php endforeach ?>
      </ul>
    <?php endif ?>
</section>