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
	'dirlistozxa.php', 'gen-thumbs' // dirlistozxa
];

// ================

// Lazy sanitisation done if the web server somehow sends idiotic input,
// nginx with default configuration (merge_slashes) doesn't actually need this.
$_SERVER['REQUEST_URI'] = str_replace('../', '', $_SERVER['REQUEST_URI']);

// Allow spaces and other heretical stuff in folder names.
$_SERVER['REQUEST_URI'] = urldecode($_SERVER['REQUEST_URI']);

$folder = str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
$path = $_SERVER['DOCUMENT_ROOT'].$folder;

if (!is_dir($path)) die('invalid folder?');

define('THUMB_FOLDER', 1);
define('THUMB_FILE', 2);
define('THUMB_IMAGE', 3);

function row($name, $thumb) {
	$img = match ($thumb) {
		THUMB_FOLDER => '/.dirlistozxa/folder.png',
		THUMB_FILE => '/.dirlistozxa/file.png',
		THUMB_IMAGE => "/.thumbs/".$name,
	};

	return <<<HTML
		<div class="item"><a href="$name">
			<div class="top"><img src="$img" loading="lazy"></div>
			<div class="bottom">$name</div>
		</a></div>
	HTML;
}

function build_blocks($items) {
	global $ignore_file_list, $path, $folder;

	$rtn = '';

	$objects = [ 'directories' => [], 'files' => [] ];

	foreach ($items as $item) {
		if (in_array($item, $ignore_file_list) || str_starts_with($item, '.')) continue;

		if (is_dir($path.$item))
			$objects['directories'][$item] = $item;
		else
			$objects['files'][$item] = $item;
	}

	// SORT
	natsort($objects['directories']);
	natsort($objects['files']);

	if ($folder != '/')
		$rtn .= row('../', THUMB_FOLDER);

	foreach ($objects['directories'] as $dir) {
		$name = basename($dir).'/';

		$rtn .= row($name, THUMB_FOLDER);
	}

	foreach ($objects['files'] as $file) {
		$name = basename($file);

		$doThumb = file_exists($_SERVER['DOCUMENT_ROOT']."/.thumbs/".$file) ? THUMB_IMAGE : THUMB_FILE;

		$rtn .= row($name, $doThumb);
	}

	return $rtn;
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Index of <?=$folder ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="stylesheet" href="/.dirlistozxa/style.css">
</head>
<body>
	<h1>Index of <?=$folder ?></h1>

	<div class="dirlist">
		<?=build_blocks(scandir($path)) ?>
	</div>
</body>
</html>
