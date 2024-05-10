#!/bin/bash

cd modules

while IFS= read -r line; do

    # Extract package name and version
    package_name=$(echo "$line" | awk '{print $1}')
    version=$(echo "$line" | awk '{print $2}')

    # Remove "drupal/" prefix from package name if present
    short_name=${package_name#drupal/}

    if [ ! -d "$short_name" ]; then
        echo "Package directory not found: $short_name. Skipping..."
        continue
    fi

    pushd $short_name >/dev/null

    echo "Tagging package: $short_name @ $version"

    git tag $version

    pwd

    echo "Pushing tag to remote repository..."

    git push origin $version

    gh release create $version -t "Release $version" -n "Initial release @ $version"

    popd >/dev/null

done <"packages.txt"

# Push all tags at once to the remote repository (optional)
# git push origin --tags

echo "All packages tagged successfully."
