<section class="dashboard files">
  <h2><i class="fa fa-files-o"></i> Files</h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/files/new" title="New">+</a>
      </li>
    </ul>
  </nav>
  <ul class="files grid-wrapper">
      <? if (isset($files)) : ?>
          <? foreach ($files as $file) : ?>
          <li class="grid-container">
            <div class="grid-box-10">
              <h3>
                <a class="btn documentTitle" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/files/get?file=<?= $file->file ?>&amp;unsanitized" title="Edit">
                  <i class="fa fa-<?= \CloudControl\Cms\cc\StringUtil::iconByFileType($file->type) ?>"></i> <?= $file->file ?>
                </a>
                <small class="small fileType">
                  <span class="label">Type:</span>
                    <?= $file->type ?>
                </small>
                <small class="small fileSize">
                  <span class="label">Size:</span>
                    <?= \CloudControl\Cms\cc\StringUtil::humanFileSize($file->size) ?>
                </small>
              </h3>
            </div>
            <div class="documentActions grid-box-2">
              <a onclick="return confirm('Are you sure you want to delete this item?');" class="btn error" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/files/delete?file=<?= $file->file ?>" title="Delete"><i class="fa fa-trash"></i></a>
            </div>
          </li>
          <? endforeach ?>
      <? endif ?>
  </ul>
</section>