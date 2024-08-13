#!/usr/bin/env bash

# ERROR HANDLING
#set -o pipefail  # trace ERR through pipes
set -o errexit  # set -e : exit the script if any statement returns a non-true return value
set -o errtrace # trace ERR through 'time command' and other functions
set -o nounset  ## set -u : exit the script if you try to use an uninitialised variable

# trap and and call function (e.g. cleanup or something like that)
trap "handleError" SIGHUP SIGINT SIGQUIT SIGABRT SIGTERM

#
# CONFIG
#
repository="https://github.com/teqneers/Greyface.git"
clone=/tmp/greyface-$$
readonly NOW=$(date +'%Y-%m-%d %H:%M:%S')
pushd "$(dirname $0)" >/dev/null
SCRIPT_PATH=$(pwd -P)
popd >/dev/null
dryRun=false
local=0

source "${SCRIPT_PATH}/../../docker/common/_env_loader.sh"
loadEnv

# define color variable to be used in echo, cat, ...
if [[ -n "${TERM:-}" && ${TERM} != "dumb" ]]; then
    readonly C_BLACK="$(tput setaf 0)"          # Black
    readonly C_RED="$(tput setaf 1)"            # Red
    readonly C_GREEN="$(tput setaf 2)"          # Green
    readonly C_YELLOW="$(tput setaf 3)"         # Yellow
    readonly C_BLUE="$(tput setaf 4)"           # Blue
    readonly C_PURPLE="$(tput setaf 5)"         # Purple
    readonly C_CYAN="$(tput setaf 6)"           # Cyan
    readonly C_WHITE="$(tput setaf 7)"          # White
    readonly C_UNDERLINE="$(tput smul)"         # Underline
    readonly C_RESET="$(tput sgr0)$(tput rmul)" # Text Reset
else
    # NO TERM NO COLOR
    readonly C_BLACK=""     # Black
    readonly C_RED=""       # Red
    readonly C_GREEN=""     # Green
    readonly C_YELLOW=""    # Yellow
    readonly C_BLUE=""      # Blue
    readonly C_PURPLE=""    # Purple
    readonly C_CYAN=""      # Cyan
    readonly C_WHITE=""     # White
    readonly C_UNDERLINE="" # Underline
    readonly C_RESET=""     # Text Reset
fi

#
# FUNCTIONS
#
handleError() {
    echo >&2 -e "\n\nAn error occured. Build was interupted"
    cleanup
    exit 99
}

cleanup() {
    rm -rf "${clone}"
}

usage() {
    cat <<EOF
usage: $0 [options] -- source tag

Start build process and create a deployment tag with it.

A tag 3.2.1 will create a tag releases/3.2.1-NEXT and deploy/3.2.1-NEXT,
where NEXT is the previous tag extension number + 1.

OPTIONS:
   -d   Dry run will not create tags
   -l   Stay local, no extra checkout, use current dir (e.g. Jenkins)
   -h   Show this message
EOF
}

#
# Print out available tags
#
function availableTags() {
    echo -e "\nAvailable tags are"
    git ls-remote --heads --tags "${repository}" 2>/dev/null | grep -E "\\/(tags|heads)\\/" | grep -vE "(\\/deploy\\/|\\^\\{\\}$)" | sed -E 's/[[:alnum:]]+[[:blank:]]+refs(\/heads)?\///g' | sort | column
    echo
}

#
# ARGUMENT PARSING
#
while getopts "dhl" opt; do
    case "${opt}" in
    d)
        dryRun=true
        ;;
    l)
        local=1
        clone="${SCRIPT_PATH}/../.."
        ;;
    h | *)
        usage
        exit 1
        ;;
    esac
done
shift $((OPTIND - 1))

#
# CHECK INPUT
#
if [[ $# -lt 2 ]]; then
    echo >&2 -e "ERROR: Missing arguments\n"
    usage
    availableTags
    exit 1
fi

source=${1}
version=${2}

if [[ ! "${version}" =~ [.0-9]+ ]]; then
    echo >&2 "ERROR: invalid tag name ${version}.\n"
    exit 2
fi

#
# MAIN
#
${dryRun} && echo -e "\nRUNNING IN DRY MODE SIMULATION\n"
echo -e "\nSTARTING BUILD PROCESS\n"

# get last extension number for tag and create next number
last=$(git ls-remote --tags "${repository}" -l "releases/${version}-*" | grep -v "\^{}" | sed -E 's/[[:alnum:]]+[[:blank:]]+refs\/tags\/releases\///g' | sort -V | tail -n 1)
if [[ "${last}" == "" ]]; then
    next=0
else
    if [[ ! "${last}" =~ ^[.0-9]+-[0-9]+$ ]]; then
        echo >&2 -e "ERROR: unable to find previous version\n"
        exit 3
    fi
    next=$((${last/${version}-/} + 1))
fi

tag="${version}-${next}"
release="releases/${tag}"
deploy="deploy/${tag}"

echo -e "\n-- new tag name is ${tag}"

if [[ "${local}" -eq 0 ]]; then
    echo -e "\n-- cloning source ${source}"
    git clone --single-branch --branch "${source}" "${repository}" "${clone}"
fi

cd "${clone}"

echo -e "\n-- initialize submodules ${source}"
git submodule init
git submodule update --recursive --remote

appVersion=$(php -r "include '${clone}/app/version.php'; echo PRODUCT_VERSION;")
if [[ ! ${version} =~ ${appVersion/[^.0-9]*/}.* ]]; then
    echo -e "\nThe app version in ${C_YELLOW}${source}${C_RESET} is ${C_YELLOW}${appVersion}${C_RESET} and differs from given verion ${C_YELLOW}${version}${C_RESET}."
    read -p "Are you sure you want to create tag name ${C_YELLOW}${tag}${C_RESET} (y/N)? " -n 1 -r
    echo
    if [[ ! "${REPLY}" =~ ^[Yy]$ ]]; then
        cleanup
        echo >&2 "Action aborted"
        exit 4
    fi
fi

echo -e "\n-- updating version file"
perl -pi -e "s/(PRODUCT_VERSION\',[[:space:]]+)\'[^']+\'/\\1\'${version}\'/" "${clone}/app/version.php"
perl -pi -e "s/(PRODUCT_BUILD\',[[:space:]]+)\'[^']+\'/\\1\'${next}\'/" "${clone}/app/version.php"
git add "${clone}/app/version.php"
# ignore error, if nothing changed version file
git commit -m "updated product version and build number" || true

hash=$(git rev-parse --verify HEAD)

if [[ ! -e "${clone}/app/vendor" ]]; then
    echo -e "\n-- composer install"
    composer install -d "${clone}/app"
fi

echo -e "\n- generate new build artifacts"
"${clone}"/app/files/deploy/make.sh

echo -e "\n-- creating release tag"
${dryRun} || git tag -a "${release}" -m "created version ${tag} @ ${hash}"
${dryRun} || git push origin "${release}"

echo -e "\n- commiting and pushing artifacts"
git add -v -f "${clone}"/app/public/build

git commit -a -m "adds built javascript application for version ${tag} (${NOW})"

echo -e "\n- remove all files not needed for production"
rm -rf "${clone}/app/tests/"
rm -rf "${clone}/app/files/"
git commit -a -m "removed all files not needed for production for version ${tag} (${NOW})"

echo -e "\n- creating deploy tag"
${dryRun} || git tag -a "${deploy}" -m "created build ${deploy} for version ${tag}"
${dryRun} || git push origin "${deploy}"

if [[ "${local}" -eq 0 ]]; then
    echo -e "\n- cleaning up"
   # cleanup
fi

${dryRun} && echo -e "\n${C_YELLOW}This was a DRY-RUN and no tags have been created!${C_RESET}"

echo -e "\n${C_GREEN}DONE${C_RESET} in ${SECONDS}sec"

# vim: syntax=sh ts=4 sw=4 sts=4 sr noet
