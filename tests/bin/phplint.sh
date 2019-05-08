#!/usr/bin/env bash

find -L . -path ./vendor -prune -o -path ./node_modules -prune -o -name '*.php' -print0 | xargs -0 -n 1 -P 4 php -l
