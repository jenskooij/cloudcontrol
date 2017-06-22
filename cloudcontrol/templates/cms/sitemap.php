<section class="sitemap">
	<h2><i class="fa fa-map-signs"></i> Sitemap</h2>
	<nav class="actions">
		<ul>
			<li>
				<a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/sitemap/new" title="New">+</a>
				<a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/sitemap/redirects" title="Redirects">
          <i class="fa fa-random"></i>
        </a>
			</li>
		</ul>
	</nav>
	<? if (isset($sitemap)) : ?>
	<form method="post">
		<input type="hidden" name="save" value="true" />
		<ul class="sitemap sortable">
		<? foreach ($sitemap as $sitemapItem) : ?>
			<li>
				<h3><?=$sitemapItem->title?></h3>
				<span class="url"><?=$sitemapItem->url?></span>
        <a class="btn move" title="Move"><i class="fa fa-arrows-v"></i></a>
				<a onclick="return confirm('Are you sure you want to delete this item?');" class="btn error" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/sitemap/delete?slug=<?=$sitemapItem->slug?>" title="Delete"><i class="fa fa-trash"></i></a>
        <a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/sitemap/edit?slug=<?=$sitemapItem->slug?>" title="Edit"><i class="fa fa-pencil"></i></a>
				<textarea name="sitemapitem[]"><?=json_encode($sitemapItem)?></textarea>
			</li>
		<? endforeach ?>
		</ul>
		<input onmousedown="window.onbeforeunload=null;" style="display:none;" id="save" class="btn" type="submit" value="Save" />
		<a class="btn reset" style="display:none;">Reset</a>
	</form>
	<? endif ?>
</section>
<script id="jqueryScript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.js"></script>
<script>
$(function() {
	$( ".sortable" ).sortable({
		placeholder: "ui-state-highlight",
		axis: "y",
		forcePlaceholderSize: true,
		tolerance: "pointer",
		handle: "a.move",
		stop: function( event, ui ) {
			$('#save').show();
			$('.reset').show();
			window.onbeforeunload = function(e) {
				return 'You have unsaved changes. Are you sure you want to leave this page?';
			};
		}
	});
	//$( ".sortable" ).disableSelection();
	var cache = $(".sortable").html();
	$('.reset').click(function () {
		$(".sortable").html(cache).sortable("refresh");
		window.onbeforeunload=null;
		$('#save').hide();
		$('.reset').hide();
	});
});
</script>