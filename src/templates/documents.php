<?php include(__DIR__ . '/documents/functions.php'); ?>
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
                <a href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/search/update-index?returnUrl=<?= urlencode($request::$subfolders . $cmsPrefix . '/documents') ?>"
                   title="Update Index">Update Index</a>
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
                <a class="btn"
                   href="<?= $request::$subfolders . $cmsPrefix ?>/documents/new-document?path=<?= $path ?>"
                   title="New Document">
                    +
                    <i class="fa fa-file-text-o"></i>
                </a>
                <a class="btn"
                   href="<?= $request::$subfolders . $cmsPrefix ?>/documents/new-folder?path=<?= $path ?>"
                   title="New Folder">
                    +
                    <i class="fa fa-folder-o"></i>
                </a>
            </li>
        </ul>
    </nav>
    <table class="documents">
        <tr>
            <?php include(__DIR__ . '/documents/breadcrumb.php') ?>
        </tr>
        <?php
        $parentPath = substr($path, 0, strrpos($path, '/'));
        if ($path !== '/' && substr_count($path, '/') === 1) {
            $parentPath = '/';
        }
        if (!empty($parentPath)) : ?>
            <tr>
                <td class="icon" title="folder">
                    <i class="fa fa-folder-o"></i>
                </td>
                <td class="icon"></td>
                <td>
                    <a href="?path=<?= $parentPath ?>">..</a>
                </td>
                <td class="icon context-menu-container"></td>
            </tr>
        <?php endif ?>
        <?php foreach ($documents as $document) : ?>
            <tr>
                <?php if ($document->type === 'folder') : ?>
                    <?php include(__DIR__ . '/documents/folder.php') ?>
                <?php else : ?>
                    <?php include(__DIR__ . '/documents/document.php'); ?>
                <?php endif ?>
            </tr>
        <?php endforeach ?>
        <?php if (count($documents) === 0) : ?>
            <tr>
                <td class="icon" colspan="4">
                    <i>&lt;Empty&gt;</i>
                </td>
            </tr>
        <?php endif ?>
    </table>
  <div class="schedule-publication">
    <form action="<?= getPublishDocumentBaseLink($request, $cmsPrefix) ?>">
      <input type="text" name="slug" />
      <input placeholder="<?=date('Y-m-d')?>" value="<?=date('Y-m-d')?>" type="date" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" oninvalid="this.setCustomValidity('Please fill in a date. Use this format: YYYY-MM-DD.');"
             onchange="this.setCustomValidity('');" name="date" required />
      <input type="time" pattern="(0[0-9]|1[0-9]|2[0-3])(:[0-5][0-9])" oninvalid="this.setCustomValidity('Please fill in a time. Use this format: HH:MM (24 hour notation).');"
             onchange="this.setCustomValidity('');" placeholder="<?=date('H:i')?>" name="time" required />
      <input class="btn" type="submit" />
    </form>
  </div>
</section>
