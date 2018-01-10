<section class="dashboard search">
  <h2><i class="fa fa-search"></i> Search</h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn<?php if (!$searchNeedsUpdate) : ?> reset<?php endif ?>" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/search/update-index" title="Update Index">Update Index</a>
      </li>
    </ul>
  </nav>
    <?php if ($searchNeedsUpdate) : ?>
      <div class="message warning">
        <i class="fa fa-exclamation-triangle"></i> Search index is no longer in sync with documents.
      </div>
    <?php else : ?>
      <div class="message valid">
        <i class="fa fa-check"></i> Search index is in sync with documents.
      </div>
    <?php endif ?>
</section>