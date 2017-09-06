#!/bin/bash -e
vendor/bin/phpcs --standard=PSR2 --report-full --report-source --report-summary src/
vendor/bin/phpunit --coverage-text --colors=never
