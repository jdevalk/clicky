#!/bin/sh
find -L . -name '*.php' -print0 | xargs -0 -n 1 -P 4 php -l
/tmp/wordpress/phpcs/scripts/phpcs -p -s -v -n . --standard=./ruleset.xml --extensions=php