# `dirlistozxa`
A simple directory lister script in PHP that will show the files and directories in a directory, along with their modification date and size.

To set up, set the PHP file to be the index file, if no `index.html` or `index.php` exists in the directory. An example for nginx would be:

```nginx
index index.html index.php /dirlistozxa.php;
```
