<? include('documents/function.renderAction.php'); ?>
<? include('documents/function.renderDocument.php'); ?>
<? include('documents/function.renderFolder.php'); ?>
<script>window.onload = function () {
    History.init();
    History.replaceState(null, 'Cloud Control CMS', '/<?=$request::$subfolders . $cmsPrefix?>/documents?path=/');
  };</script>
<section class="documents">
  <h2><i class="fa fa-file-text-o"></i> Documents</h2>
    <? if (isset($infoMessage)) : ?>
      <div class="infoMessage <?= isset($infoMessageClass) ? $infoMessageClass : '' ?>">
          <?= $infoMessage ?>
      </div>
    <? endif ?>
  <div class="search">
      <? if ($searchNeedsUpdate) : ?>
        <div class="message warning">
          <i class="fa fa-exclamation-triangle"></i> Search index is no longer in sync with documents.
          <a href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/search/update-index?returnUrl=<?= urlencode($request::$subfolders . $cmsPrefix . '/documents') ?>" title="Update Index">Update Index</a>
        </div>
      <? else : ?>
        <div class="message valid">
          <i class="fa fa-check"></i> Search index is in sync with documents.
        </div>
      <? endif ?>
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
    <? if (isset($documents)) : ?>
      <ul class="documents grid-wrapper">
        <li class="grid-container">
          <div class="grid-box-12">
            <i class="fa fa-terminal" title="Path"></i>
            <i id="pathHolder">/</i>
          </div>
        </li>
          <? foreach ($documents as $document) : ?>
            <li class="grid-container">
                <? if ($document->type == 'document') : ?>
                    <? renderDocument($document, $cmsPrefix, '', $request); ?>
                <? elseif ($document->type == 'folder') : ?>
                    <? renderFolder($document, $cmsPrefix, '', true, $request); ?>
                <? endif ?>
            </li>
          <? endforeach ?>
      </ul>
    <? endif ?>
</section>