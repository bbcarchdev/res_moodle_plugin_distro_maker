#!/bin/bash

# liblod-php - a Linked Open Data client library for PHP
# Copyright (C) 2017 Elliot Smith
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

# script to make it a bit easier to run tests etc.
function unit() {
  echo "Running unit test suite"
  php tools/phpunit.phar --bootstrap vendor/autoload.php tests/unit
}

function int() {
  echo "Running integration test suite"
  php tools/phpunit.phar --bootstrap vendor/autoload.php tests/integration
}

function cov() {
  echo "Running unit test suite with code coverage reporting"
  php tools/phpunit.phar --bootstrap vendor/autoload.php --whitelist src --coverage-html cov tests/unit
  echo "Coverage report generated; see cov/index.html"
}

function mess() {
  echo "Running code quality analysis with PHPMD"
  result=`php vendor/bin/phpmd src text cleancode,codesize,design,naming,unusedcode`
  echo
  if [ "x" = "x$result" ] ; then
    echo "*** No code quality problems found"
  else
    echo "!!! Problems found:"
    echo $result
  fi
}

function style() {
  echo "Checking code style using phpcheckstyle"
  rm -Rf style-report
  php vendor/phpcheckstyle/phpcheckstyle/run.php --src src/ --config phpcheckstyle-config.xml
  echo "Code style report generated; see style-report/index.html"
}

function docs() {
  echo "Generating API documentation in apidocs/ using phpDocumentor"
  rm -Rf apidocs/
  php tools/phpDocumentor.phar -d src -t apidocs --template=responsive-twig
  echo "API documentation generated in apidocs/ directory"
}

case "$1" in
  install)
    echo "Installing dependencies"
    php tools/composer.phar install
  ;;
  unit)
    unit
  ;;
  int)
    int
  ;;
  cov)
    cov
  ;;
  mess)
    mess
  ;;
  style)
    style
  ;;
  docs)
    docs
  ;;
  *)
    echo "./build.sh install|unit|int|cov|mess|style"
  ;;
esac
