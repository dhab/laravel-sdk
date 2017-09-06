#!/bin/bash -e
vendor/bin/phpcs -n --standard=PSR2 --report-full --report-source --report-summary src/
vendor/bin/phpunit --coverage-text --colors=never
