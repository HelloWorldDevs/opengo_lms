#!/bin/bash
version=$1

if [ -z "$version" ]; then
    echo "Version not provided. Exiting..."
    exit 1
fi

gh release create $version -t "Release $version" -n "Release @ $version"
