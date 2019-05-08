#!/usr/bin/env bash
vendor/bin/phpunit --coverage-clover build/logs/clover.xml
./bin/test-reporter --stdout > codeclimate.json
curl -X POST -d @codeclimate.json -H "Content-Type\: application/json" -H 'User-Agent: Code Climate (PHP Test Reporter v0.1.1)' https://codeclimate.com/test_reports
