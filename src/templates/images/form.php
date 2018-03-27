<section class="dashboard images">
  <h2>
    <i class="fa fa-image"></i>
    Images
  </h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/images" title="Back">Back</a>
      </li>
    </ul>
  </nav>
  <form method="post" enctype="multipart/form-data" class="panel" id="bricksForm">
    <div class="form-element">
      <label for="file">File</label>
      <input required="required" accept="image/*" id="file" type="file" name="file" placeholder="File" value="<?= isset($file) ? $file->file : '' ?>"/>
    </div>
    <div class="form-element">
      <input class="btn" type="submit" value="Save"/>
    </div>
  </form>
</section>