# `dirlistozxa`
A simple directory lister script in PHP that will show the files and directories in a directory, along with their modification date and size. Image thumbnail previews are also supported.

## Setting up
To set up, set the PHP file to be the index file, if no `index.html` or `index.php` exists in the directory. An example for nginx would be:

```nginx
index index.html index.php /dirlistozxa.php;
```

You need to also move the `.dirlistozxa/` folder into the root of the site, such that it is accessible at `/.dirlistozxa/`.

To optionally generate image thumbnails, use the `gen-thumbs` script. Requires Imagemagick to be installed, it will generate thumbnail files into `/.thumbs/`. If the script can't find any thumbnail for an image in `/.thumbs/`, it will just fall back to the generic file image.

## License
`dirlistozxa` is licensed under the AGPLv3 license.
