#
# Script to Backup to Remote Server via HTTP
# Version: 1.2 (2 Oct 2015)
# Copyright (C) 2013-2015 Patrick Pang (mail@patrickpang.net).
#
import os
import http.client
import urllib.parse
import json
import sys


backup_dirs = ["/local/directory/to/upload"]
area = "area name defined in pass.ini"
pin = "password defined in pass.ini"
host = "www.yourserver.com"
path = "/path/server.php"


def post(host, path, params, json):
    headers = {"Content-type": "application/json"}
    conn = http.client.HTTPConnection(host)
    try:
        conn.request("POST", path + "?" + urllib.parse.urlencode(params), json, headers)
        response = conn.getresponse()
        s = response.read().decode("utf-8")
        if response.status == 200:
            return s
        else:
            raise Exception(response.status, s)
    finally:
        conn.close()


def put(host, path, params, filename):
    conn = http.client.HTTPConnection(host)
    try:
        conn.request("PUT", path + "?" + urllib.parse.urlencode(params), open(filename, "rb"))
        response = conn.getresponse()
        if response.status != 200:
            s = response.read().decode("utf-8")
            raise Exception(response.status, s, filename)
    finally:
        conn.close()


def generate_file_list(file_list, path):
    for f in os.listdir(path):
        full_name = os.path.join(path, f)
        if os.path.isdir(full_name):
            generate_file_list(file_list, full_name)
        elif os.path.isfile(full_name) and not f.startswith("."):
            file_list.append({"name": f, "size": os.stat(full_name).st_size, "path": full_name})


# Main script starts here.
script_name = os.path.basename(__file__)

# Generate a file list of local files.
file_list = list()
for d in backup_dirs:
    generate_file_list(file_list, d)

# Obtain a list of files that the server accepts.
try:
    accepted_list = json.loads(post(host, path, {"area": area, "pin": pin}, json.dumps(file_list)))
    del file_list
except Exception as e:
    sys.exit("{0}: error listing file: {1}".format(script_name, e))

try:
    for af in accepted_list:
        put(host, path, {"area": area, "pin": pin, "filename": af["name"]}, af["path"])
except Exception as e:
    sys.exit("{1}: error putting file: {1}".format(script_name, e))
