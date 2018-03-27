<section class="dashboard search">
  <h2>
    <i class="fa fa-search"></i>
    <a href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/search" title="Search">Search</a> &raquo; Update Index
  </h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $returnUrl ?>" title="Back">Back</a>
      </li>
    </ul>
  </nav>
  <div class="search-progress">
    <h3 class="search-index-status" id="search_index_status">Initializing...</h3>
    <div id="search_index_progress_bar" class="progress-bar active" data-progress="1">
      <div class="progress"></div>
    </div>
    <a class="btn show-log-button" id="search_index_show_log" href="#" onclick="document.getElementById('search_index_log').style.display='block';document.getElementById('search_index_show_log').style.display='none';return false;">Show log</a>
    <ul id="search_index_log" class="search-index-log"></ul>
  </div>
</section>