<section class="dashboard configuration">
  <h2>
    <i class="fa fa-cogs"></i>
    <a href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration">Configuration</a> &raquo; Bricks
  </h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/bricks/new" title="New">+</a>
      </li>
    </ul>
  </nav>
    <?php if (isset($bricks)) : ?>
      <ul class="configuration sortable grid-wrapper">
          <?php foreach ($bricks as $brick) : ?>
            <li class="grid-container">
              <div class="grid-box-8">
                <h3>
                  <a class="btn documentTitle" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/bricks/edit?slug=<?= $brick->slug ?>" title="Edit">
                    <i class="fa fa-cube"></i> <?= $brick->title ?>
                  </a>
                </h3>
              </div>
              <div class="documentActions grid-box-4">
                <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/bricks/edit?slug=<?= $brick->slug ?>" title="Edit">
                  <i class="fa fa-pencil"></i>
                </a>
                <a data-confirm="Are you sure you want to delete the brick '<?= $brick->title ?>'?"
                   data-confirm-text="Delete"
                   data-decline-text="Cancel"
                   class="btn error" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/bricks/delete?slug=<?= $brick->slug ?>" title="Delete">
                  <i class="fa fa-trash"></i>
                </a>
              </div>
            </li>
          <?php endforeach ?>
      </ul>
    <?php endif ?>
</section>