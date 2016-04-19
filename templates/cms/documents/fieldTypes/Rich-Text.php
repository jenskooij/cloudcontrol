<label for="<?=$field->slug?>"><?=$field->title?></label>
<div class="rte">
	<div class="summernote"></div>
</div>
<script src="//code.jquery.com/jquery-1.9.1.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.1/js/bootstrap.min.js"></script>
<script src="<?=$request::$subfolders?>../../cc/cloudcontrol/summernote/summernote.min.js"></script>


<script>
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
</script>
