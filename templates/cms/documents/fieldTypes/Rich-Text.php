<label for="<?=$field->slug?>"><?=$field->title?></label>
<div class="rte">
	<div id="summernote_<?=$field->slug?>" class="summernote"></div>
</div>
<textarea style="display:none;" id="summernote_<?=$field->slug?>_container" name="<?=$fieldPrefix?>[<?=$field->slug?>][]"></textarea>
<script src="//code.jquery.com/jquery-1.9.1.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.1/js/bootstrap.min.js"></script>
<script src="<?=$request::$subfolders?>../../cc/cloudcontrol/summernote/summernote.min.js"></script>
<!--[if IE]>
<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
<script src="//code.jquery.com/jquery-1.9.1.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.1/js/bootstrap.min.js"></script>
<![endif]-->
<script>
	if (rtes === undefined) {
		var rtes = [],
			rte_containers = [];
	}
	$(document).ready(function() {
		$('.summernote').summernote({
			height: 200,
			toolbar: [
				//[groupname, [button list]]

				['style', ['bold', 'italic', 'underline', 'clear', 'style']],
				['font', ['strikethrough', 'superscript', 'subscript']],
				['para', ['ul', 'ol']],
				['insert', ['table', 'link', 'picture']],
				['misc', ['codeview']],
			]
		});
	});
	if (processSummernotes === undefined) {
		function processSummernotes () {
			"use strict";
			var i,
				rte;
			for (i = 0; i < rtes.length; i += 1) {
				rte = document.getElementById(rtes[i]);
				document.getElementById(rte_containers[i]).innerHTML = $(rte).code();
			}
		}
		$('form').submit(processSummernotes);
	}
	rtes.push('summernote_<?=$field->slug?>');
	rte_containers.push('summernote_<?=$field->slug?>_container');
</script>
