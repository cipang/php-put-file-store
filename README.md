# php-put-file-store
A small script for a constrained PHP web hosting environment, allowing to send files to the server via PUT requests. I use this script to backup my files to my web server and make use of the unused space.

Normally people will use `rsync` for this but it is not possible with my web hosting server because of a number of restrictions. This serves a cheap and simple alternative to do the same thing.

Though the client script here is written in Python, it is not difficult to rewrite it in another language.

# Setup
1. Upload both `server.php` and `pass.ini` to server.
  * Each line in `pass.ini` defines a directory to store a set of files.
  * The password is for authenticate PUT (file store) requests.
2. Modify variables `backup_client.py` to point to your server.
3. Whenever you want to send files to the server, run `backup_client.py`.

*Note: For simpicity no directory structure is preserved in this script, i.e. storing only files in a single directory is recommended.*

# How Does It Work?
1. The server verifies the area name and password.
2. The client sends a local file list to `server.php` for checking which files are outdated (determined using the filename and the size of the file *only*).
3. The server returns a list of files the client should upload.
4. The client uploads files with PUT requests one-by-one.

# Requirements
* PHP
* Python 3

# Disclaimer
These scripts are experimenting with uploading with HTTP PUT requests (sending with Python and receiving with PHP). For scenarios requiring security and performance, please use other sophisticated methods instead.
