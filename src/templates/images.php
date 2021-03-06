<section class="dashboard images">
  <h2>
    <i class="fa fa-image"></i>
    Images
  </h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/images/new" title="New">+</a>
      </li>
    </ul>
  </nav>
  <div class="grid-wrapper">
    <ul class="images grid-container">
        <?php if (isset($images)) : ?>
            <?php foreach ($images as $image) : ?>
            <li class="grid-box-2">
              <div class="grid-inner">
                <a data-confirm="Are you sure you want to delete the image '<?= $image->file ?>'?"
                   data-confirm-text="Delete"
                   data-decline-text="Cancel" class="btn error" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/images/delete?file=<?= $image->file ?>" title="Delete">
                  <i class="fa fa-trash"></i>
                </a>
                <a class="image-link" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/images/show?file=<?= $image->file ?>" title="Show">
                  <img src="<?= $request::$subfolders ?>images/<?= isset($image->set->$smallestImage) ? $image->set->$smallestImage : current($image->set) ?>"/>
                </a>
                <small class="small filename">
                  <span class="label">Name:</span>
                    <?= $image->file ?>
                </small>
                <small class="small fileType">
                  <span class="label">Type:</span>
                    <?= $image->type ?>
                </small>
                <small class="small fileSize">
                  <span class="label">Size:</span>
                    <?= \CloudControl\Cms\util\StringUtil::humanFileSize($image->size) ?>
                </small>
              </div>
            </li>
            <?php endforeach ?>
        <?php endif ?>
    </ul>
  </div>
</section>