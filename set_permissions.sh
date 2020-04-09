#!/bin/bash

# Modified from https://github.com/AdiechaHK/laravel-permission-script

find ./ -type d -exec sudo chmod 755 {} \;
find ./ -type d -exec sudo chmod ug+s {} \;
find ./ -type f -exec sudo chmod 644 {} \;
sudo chown -R $USER:www-data ./
sudo chmod -R 777 ./storage
sudo chmod -R 777 ./bootstrap/cache/