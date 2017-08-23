<section class="dashboard configuration">
  <h2><i class="fa fa-cogs"></i>
    <a href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration">Configuration</a> &raquo; Image Set</h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/image-set" title="Back">Back</a>
      </li>
    </ul>
  </nav>
  <form method="post" class="panel" id="documentTypesForm">
    <div class="form-element">
      <label for="title">Title</label>
      <input required="required" id="title" type="text" name="title" placeholder="Title" value="<?= isset($imageSet) ? $imageSet->title : '' ?>"/>
    </div>
    <div class="form-element">
      <label for="width">Width</label>
      <input required="required" id="width" type="number" name="width" placeholder="Width" value="<?= isset($imageSet) ? $imageSet->width : '' ?>"/>
    </div>
    <div class="form-element">
      <label for="height">Height</label>
      <input required="required" id="height" type="number" name="height" placeholder="Height" value="<?= isset($imageSet) ? $imageSet->height : '' ?>"/>
    </div>
    <div class="form-element">
      <label for="method">Method</label>
      <select name="method">
        <option value="resize"<?= isset($imageSet) && $imageSet->method == 'resize' ? ' selected="selected"' : '' ?>>Resize</option>
        <option value="smartcrop"<?= isset($imageSet) && $imageSet->method == 'smartcrop' ? ' selected="selected"' : '' ?>>Smartcrop</option>
        <option value="boxcrop"<?= isset($imageSet) && $imageSet->method == 'boxcrop' ? ' selected="selected"' : '' ?>>Boxcrop</option>
      </select>
    </div>
    <div class="form-element">
      <input class="btn" type="submit" value="Save"/>
    </div>
  </form>
</section>