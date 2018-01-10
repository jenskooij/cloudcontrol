<section class="dashboard configuration">
  <h2><i class="fa fa-cogs"></i>
    <a href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration">Configuration</a> &raquo; Image Set</h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/image-set/new" title="New">+</a>
      </li>
    </ul>
  </nav>
    <?php if (isset($imageSet)) : ?>
      <ul class="configuration grid-wrapper">
          <?php foreach ($imageSet as $currentSet) : ?>
            <li class="grid-container">
              <div class="grid-box-8">
                <h3>
                  <a class="btn documentTitle" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/image-set/edit?slug=<?= $currentSet->slug ?>" title="Edit">
                    <i class="fa fa-file-image-o"></i> <?= $currentSet->title ?>
                  </a>
                  <small class="small">
                    <span class="label">Size:</span>
                      <?= $currentSet->width ?>x<?= $currentSet->height ?>
                  </small>
                  -
                  <small class="small">
                    <span class="label">Method:</span>
                      <?= ucfirst($currentSet->method) ?>
                  </small>
                </h3>
              </div>
              <div class="documentActions grid-box-4">
                <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/image-set/edit?slug=<?= $currentSet->slug ?>" title="Edit"><i class="fa fa-pencil"></i></a>
                <a onclick="return confirm('Are you sure you want to delete this item?');" class="btn error" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/image-set/delete?slug=<?= $currentSet->slug ?>" title="Delete"><i class="fa fa-trash"></i></a>
              </div>
            </li>
          <?php endforeach ?>
      </ul>
    <?php endif ?>
</section>