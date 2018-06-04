<section class="dashboard configuration">
  <h2>
    <i class="fa fa-cogs"></i>
    <a href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration">Configuration</a> &raquo; Users
  </h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/users/new" title="New">+</a>
      </li>
    </ul>
  </nav>
    <?php if (isset($users)) : ?>
      <ul class="configuration grid-wrapper">
          <?php foreach ($users as $user) : ?>
            <li class="grid-container">
              <div class="grid-box-8">
                <h3>
                  <a class="btn documentTitle" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/users/edit?slug=<?= $user->slug ?>" title="Edit">
                    <i class="fa fa-user"></i> <?= $user->username ?>
                  </a>
                </h3>
              </div>
              <div class="documentActions grid-box-4">
                <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/users/edit?slug=<?= $user->slug ?>" title="Edit">
                  <i class="fa fa-pencil"></i>
                </a>
                <a data-confirm="Are you sure you want to delete the user '<?= $user->username ?>'?"
                   data-confirm-text="Delete"
                   data-decline-text="Cancel"
                   class="btn error" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/users/delete?slug=<?= $user->slug ?>" title="Delete">
                  <i class="fa fa-trash"></i>
                </a>
              </div>
            </li>
          <?php endforeach ?>
      </ul>
    <?php endif ?>
</section>