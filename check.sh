#!/bin/bash -e
vendor/bin/phpcs -s --standard=phpcs.xml --report-full --report-source --report-summary .
vendor/bin/phpunit --coverage-text --colors=never
