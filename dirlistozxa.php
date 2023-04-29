<?php
/* dirlistozxa - Basic directory lister script written in PHP
 * Copyright (C) 2023 ROllerozxa
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * This software is best enjoyed with soused herring!
 */

// Configuration:

// List of filenames (and folder names) that should be ignored.
$ignore_file_list = [
	'.htaccess', '.htpasswd',	// Apache junk files
	'Thumbs.db', '.DS_Store',	// OS junk files
	'index.php', 'index.html',	// Potential other index files
	'.git', 'vendor',			// Dev
	'dirlistozxa.php', '.dirlistozxa', '.thumbs', 'gen-thumbs' // dirlistozxa
];

// ================

// Lazy sanitisation done if the web server somehow sends idiotic input,
// nginx with default configuration (merge_slashes) doesn't actually need this.
$_SERVER['REQUEST_URI'] = str_replace('../', '', $_SERVER['REQUEST_URI']);

$folder = str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
$path = $_SERVER['DOCUMENT_ROOT'].$folder;

if (!is_dir($path)) die('invalid folder?');

define('THUMB_FOLDER', 1);
define('THUMB_FILE', 2);
define('THUMB_IMAGE', 3);

function display_size($bytes, $precision = 2) {
	$units = ['B', 'K', 'M', 'G', 'T'];
	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);
	$bytes /= (1 << (10 * $pow));
	return round($bytes, $precision) . $units[$pow];
}

function row($name, $date, $size, $thumb) {
	$img = match ($thumb) {
		THUMB_FOLDER => '/.dirlistozxa/folder.png',
		THUMB_FILE => '/.dirlistozxa/file.png',
		THUMB_IMAGE => "/.thumbs/".$name,
	};

	return sprintf(
		'<tr>
			<td class="tum"><a href="%s"><img src="%s" loading="lazy"></td>
			<td><a href="%s">%s</a></td>
			<td>%s</td><td class="r">%s</td>
		</tr>',
	$name, $img, $name, $name, $date, $size);
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
		$rtn .= row('../', '', '', THUMB_FOLDER);

	foreach ($objects['directories'] as $dir) {
		$name = basename($dir).'/';
		$date = date('Y-m-d H:i', filemtime($path.$dir));

		$rtn .= row($name, $date, '-', THUMB_FOLDER);
	}

	foreach ($objects['files'] as $file) {
		$name = basename($file);
		$date = date('Y-m-d H:i', filemtime($path.$file));
		$size = display_size(filesize($path.$file));

		$doThumb = file_exists($_SERVER['DOCUMENT_ROOT']."/.thumbs/".$file) ? THUMB_IMAGE : THUMB_FILE;

		$rtn .= row($name, $date, $size, $doThumb);
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

.tum {
	height: 48px;
	width: 48px;
}
.tum img {
	max-width: 100%;
	max-height: 100%;
	margin: auto;
	display: block;
}
	</style>
</head>
<body>
	<h1>Index of <?=$folder ?></h1>

	<table>
		<tr><th></th><th>Name</th><th>Last modified</th><th>Size</th></tr>
		<tr><th colspan="4"><hr></th></tr>
		<?=build_blocks(scandir($path)) ?>
		<tr><th colspan="4"><hr></th></tr>
	</table>

	<address><?=$_SERVER['SERVER_SOFTWARE'] ?? 'Cool' ?> server at <?=$_SERVER['HTTP_HOST'] ?>, index powered by <a href="https://github.com/rollerozxa/dirlistozxa/">dirlistozxa</a></address>
</body>
</html>
