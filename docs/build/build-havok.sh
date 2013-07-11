#!/bin/bash
#Build a havok layer for the mystique docs to use

echo "Making temp directory"
mkdir -p ./temp/

echo "Removing old build"
rm -rf ./temp/*

node ./build/buildconfig.js load=havok/build/preprocess --profile ./build/havok.profile.js

node ./build/buildconfig.js load=build --profile ./build/havok.profile.preprocessed.js

echo >> ./temp/dojo/dojo.js
echo >> ./temp/havok/nls/havok_en-us.js
cat ./temp/dojo/dojo.js ./temp/havok/nls/havok_en-us.js ./temp/havok/havok.js > ./src/js/havok.js

echo "Havok layer complete"