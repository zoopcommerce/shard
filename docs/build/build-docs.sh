#!/bin/bash
#Do a complete build of the shard docs into the docs/dist directory

echo "building php files"
php build-php-files.php

echo "building havok layer"
sh build-havokdocs-layer.sh

echo "docs build complete"
