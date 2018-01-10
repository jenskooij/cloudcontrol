<section class="dashboard configuration">
  <h2><i class="fa fa-cogs"></i>
    <a href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration">Configuration</a> &raquo; Document Types</h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/document-types/new" title="New">+</a>
      </li>
    </ul>
  </nav>
    <?php if (isset($documentTypes)) : ?>
      <ul class="configuration sortable grid-wrapper">
          <?php foreach ($documentTypes as $documentType) : ?>
            <li class="grid-container">
              <div class="grid-box-8">
                <h3>
                  <a class="btn documentTitle" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/document-types/edit?slug=<?= $documentType->slug ?>" title="Edit">
                    <i class="fa fa-file-code-o"></i> <?= $documentType->title ?>
                  </a>
                </h3>
              </div>
              <div class="documentActions grid-box-4">
                <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/document-types/edit?slug=<?= $documentType->slug ?>" title="Edit"><i class="fa fa-pencil"></i></a>
                <a onclick="return confirm('Are you sure you want to delete this item?');" class="btn error" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/document-types/delete?slug=<?= $documentType->slug ?>" title="Delete"><i class="fa fa-trash"></i></a>
              </div>
            </li>
          <?php endforeach ?>
      </ul>
    <?php endif ?>
</section>