<section class="dashboard images">
  <h2><i class="fa fa-image"></i> Images</h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/images" title="Back">Back</a>
      </li>
    </ul>
  </nav>
  <div class="show-image">
    <label>File</label>
    <div class="value">
        <?= isset($image) ? $image->file : '' ?>
    </div>
    <label>Type</label>
    <div class="value">
        <?= isset($image) ? $image->type : '' ?>
    </div>
    <label>Size</label>
    <div class="value">
        <?= isset($image) ? \CloudControl\Cms\cc\StringUtil::humanFileSize($image->size) : '' ?>
    </div>
    <label>Set</label>
      <?php if (isset($image)) : ?>
          <?php foreach ($image->set as $key => $set) : ?>
          <div class="sets">
            <label><?= $key ?></label>
            <img src="<?= $request::$subfolders . 'images/' . $set ?>"/>
          </div>
          <?php endforeach ?>
      <?php endif ?>
  </div>
</section>