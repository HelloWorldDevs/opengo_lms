#!/bin/bash

while IFS= read -r line; do

    # Extract package name and version
    package_name=$(echo "$line" | awk '{print $1}')
    version=$(echo "$line" | awk '{print $2}')

    # Remove "drupal/" prefix from package name if present
    short_name=${package_name#drupal/}
    project_name=$(echo $short_name | sed 's/opigno/opengo/g')

    if [ ! -d "modules/$short_name" ]; then
        echo "Package directory not found: $short_name. Skipping..."
        continue
    fi

    echo "Tagging package: $project_name @ $version"

    repo_url="git@github.com:HelloWorldDevs/$project_name.git"

    echo "Repository URL: $repo_url"

    jq --arg url "$repo_url" '.repositories += [{"type": "vcs", "url": $url}]' composer.json >temp.json && mv temp.json composer.json

done <"packages.txt"
