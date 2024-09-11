#!/usr/bin/env bash
readonly DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../" && pwd )";
cd ${DIR};
set -eE # We need the big `E` for this to apply to functions https://stackoverflow.com/a/35800451
set -u
set -o pipefail
standardIFS="$IFS"
IFS=$'\n\t'

# Just Static check the local module directory
directoryToTest="app/code/"
themeDir="app/design/frontend/"

action=${1:-all}

case ${action} in
"Frontend PHP Code Sniffer")
    php ${DIR}/vendor/bin/phpcs \
        --standard=${DIR}/vendor/magento/magento-coding-standard/Magento2/ruleset.xml \
        ${themeDir}
    ;;
"Frontend Mess Detector")
    php ${DIR}/vendor/bin/phpmd \
        ${themeDir} \
        text \
        ${DIR}/dev/tests/static/testsuite/Magento/Test/Php/_files/phpmd/ruleset.xml
    ;;
"Frontend LESS Code Sniffer")
    php ${DIR}/vendor/bin/phpcs \
        --standard=dev/tests/static/testsuite/Magento/Test/Less/_files/lesscs/ruleset.xml \
        --extensions="less/css" \
        ${themeDir}
    ;;
"Backend Copy Paste Detector")
    php ${DIR}/vendor/bin/phpcpd \
        ${directoryToTest}
    ;;
"Backend Mess Detector")
    php ${DIR}/vendor/bin/phpmd \
        ${DIR}/${directoryToTest} \
        text \
        ${DIR}/dev/tests/static/testsuite/Magento/Test/Php/_files/phpmd/ruleset.xml
    ;;
"Backend Code Sniffer")
    php ${DIR}/vendor/bin/phpcs \
        --standard=${DIR}/vendor/magento/magento-coding-standard/Magento2/ruleset.xml \
        ${directoryToTest}
    ;;
"test command")
    echo "Its works"
    ;;
*)
    echo "Unknown Test"
    exit 5
      ;;
esac
