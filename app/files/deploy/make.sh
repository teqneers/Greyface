#!/usr/bin/env bash

# ERROR HANDLING
#set -o pipefail  # trace ERR through pipes
set -o errexit  # set -e : exit the script if any statement returns a non-true return value
set -o errtrace # trace ERR through 'time command' and other functions
set -o nounset  ## set -u : exit the script if you try to use an uninitialised variable

# trap and and call function (e.g. cleanup or something like that)
trap "handleError" SIGHUP SIGINT SIGQUIT SIGABRT SIGTERM

#
# FUNCTIONS
#
handleError() {
    echo >&2 -e "\n\nAn error occured. Build was interupted"
    exit 99
}

scriptPath="$(cd "$(dirname "$0")" && pwd -P)"
public_path=${1}


if [ -z "${public_path}" ]; then
    public_path="/greyface/build"
fi

#
# Greyface
#
cd "${scriptPath}/../../"
if [ -d public/build ]; then
    rm -rf public/build/*
fi
if [ -d node_modules ]; then
    rm -rf node_modules/*
fi

PUBLIC_PATH=${public_path} npm install  --verbose && PUBLIC_PATH=${public_path} npm run  build

# vim: syntax=sh ts=4 sw=4 sts=4 sr noet
