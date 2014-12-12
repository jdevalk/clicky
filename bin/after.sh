#!/bin/sh
CODECLIMATE_REPO_TOKEN="a1ed4996e9da5703ae6483f5a3294cb9c5729dfae8820c453c768f9f31044246"
./bin/test-reporter --stdout > codeclimate.json
curl -X POST -d @codeclimate.json -H 'Content-Type: application/json' -H 'User-Agent: Code Climate (PHP Test Reporter v0.1.1)' https://codeclimate.com/test_reports