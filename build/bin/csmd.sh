#!/bin/bash

./vendor/bin/phpmd --exclude ClassMetadataTrait.php ./lib/ text ./tests/ruleset.xml