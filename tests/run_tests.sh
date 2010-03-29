#!/bin/bash

if [[ -d coverage ]] ; then
	rm -rf coverage
fi

phpunit --bootstrap ./bootstrap.php --process-isolation --coverage-html coverage library
echo

