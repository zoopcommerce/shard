echo "Havok layer complete"

#!/bin/bash
#Build a havok layer for the havok docs to use
#Run this script from shard/docs/build directory

echo "Making temp directory"
mkdir -p ../temp/

echo "Removing old build"
rm -rf ../temp/*

node ../../../havok/build/buildconfig.js load=havok/build/preprocess --profile havokdocs.profile.js

node ../../../havok/build/buildconfig.js load=build --profile havokdocs.profile.preprocessed.js

cp ../temp/havok/havokdocs.js ../dist/js
cp ../temp/havok/havokdocs.css ../dist

rm -rf ../temp/*
rmdir ../temp

echo "build havok layer complete"