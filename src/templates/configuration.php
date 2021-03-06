<section class="dashboard configuration">
  <h2>
    <i class="fa fa-cogs"></i>
    Configuration
  </h2>
  <nav class="tiles grid-wrapper">
    <ul class="grid-container">
      <li class="tile grid-box-3">
        <a class="btn configuration" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/users">
          <i class="fa fa-user"></i>
          Users
        </a>
      </li>
      <li class="tile grid-box-3">
        <a class="btn configuration" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/document-types">
          <i class="fa fa-file-code-o"></i>
          Document Types
        </a>
      </li>
      <li class="tile grid-box-3">
        <a class="btn configuration" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/bricks">
          <i class="fa fa-cubes"></i>
          Bricks
        </a>
      </li>
      <li class="tile grid-box-3">
        <a class="btn configuration" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/image-set">
          <i class="fa fa-file-image-o"></i>
          Image Set
        </a>
      </li>
      <li class="tile grid-box-3">
        <a class="btn configuration" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/configuration/application-components">
          <i class="fa fa-puzzle-piece"></i>
          Application Components
        </a>
      </li>
    </ul>
  </nav>
</section>