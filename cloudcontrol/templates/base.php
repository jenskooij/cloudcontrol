<!DOCTYPE html>
<html>
<head>
	<title>Cloud Control</title>
</head>
<body>
	<h1>Cloud Control</h1>
	<? if (isset($document)) : ?>
		<h2><?=$document->title?></h2>
		<div><?=$document->fields->content[0]?></div>
	<? endif ?>
</body>
</html>