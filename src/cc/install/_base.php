<!DOCTYPE html>
<html>
  <head>
    <title>Cloud Control</title>
    <link rel="stylesheet" href="<?= $request::$subfolders ?>/css/site.css"/>
  </head>
  <body>
    <h1>Cloud Control</h1>
      <? if (isset($document)) : ?>
        <h2><?= $document->title ?></h2>
        <div><?= $document->fields->content[0] ?></div>
      <? endif ?>
    <script src="<?= $request::$subfolders ?>js/site.js"></script>
  </body>
</html>