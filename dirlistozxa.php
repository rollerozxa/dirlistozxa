<?php
$folder = str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
$path = $_SERVER['DOCUMENT_ROOT'].$folder;

$ignore_file_list = ['.htaccess', 'Thumbs.db', '.DS_Store', 'index.php', 'index.html', 'dirlistozxa.php'];

function display_size($bytes, $precision = 2) {
	$units = ['B', 'K', 'M', 'G', 'T'];
	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);
	$bytes /= (1 << (10 * $pow));
	return round($bytes, $precision) . $units[$pow];
}

function row($name, $date, $size) {
	return sprintf(
		'<tr><td><a href="%s">%s</a></td><td>%s</td><td class="r">%s</td></tr>',
	$name, $name, $date, $size);
}

function build_blocks($items) {
	global $ignore_file_list, $path, $folder;

	$rtn = '';

	$objects = [ 'directories' => [], 'files' => [] ];

	foreach ($items as $item) {
		if ($item == '..' || $item == '.' || in_array($item, $ignore_file_list)) continue;

		if (is_dir($path.$item))
			$objects['directories'][$item] = $item;
		else
			$objects['files'][$item] = $item;
	}

	// SORT
	natsort($objects['directories']);
	natsort($objects['files']);

	if ($folder != '/')
		$rtn .= row('../', '-', '-');

	foreach ($objects['directories'] as $dir) {
		$name = basename($dir).'/';
		$date = date('Y-m-d H:i', filemtime($path.$dir));

		$rtn .= row($name, $date, '-');
	}

	foreach ($objects['files'] as $file) {
		$name = basename($file);
		$date = date('Y-m-d H:i', filemtime($path.$file));
		$size = display_size(filesize($path.$file));

		$rtn .= row($name, $date, $size);
	}

	return $rtn;
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Index of <?=$folder ?></title>
	<meta charset="utf-8">
	<style>
body {
	background-color: #111;
	color: #eee;
	font-family: monospace;
	font-size: 12pt;
	max-width: 1440px;
	margin: auto;
	padding: 0 5px;
}
td { padding: 5px; }
th { padding: 0 5px; }
.r { text-align: right }

a {
	color: lime;
	text-decoration: none;
}
	</style>
</head>
<body>
<h1>Index of <?=$folder ?></h1>

<table>
	<tr><th>Name</th><th>Last modified</th><th>Size</th></tr>
	<tr><th colspan="3"><hr></th></tr>
	<?=build_blocks(scandir($path)) ?>
	<tr><th colspan="3"><hr></th></tr>
</table>
<address><?=$_SERVER['SERVER_SOFTWARE'] ?? 'Cool' ?> server at <?=$_SERVER['HTTP_HOST'] ?>, index powered by <a href="https://github.com/rollerozxa/dirlistozxa/">dirlistozxa</a></address>

</body>
</html>
