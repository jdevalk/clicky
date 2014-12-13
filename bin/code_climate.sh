#!/bin/sh
composer require-dev
phpunit -c phpunit.xml --coverage-clover build/logs/clover.xml
./bin/test-reporter --stdout > codeclimate.json
curl -X POST -d @codeclimate.json -H "Content-Type\: application/json" -H 'User-Agent: Code Climate (PHP Test Reporter v0.1.1)' https://codeclimate.com/test_reports