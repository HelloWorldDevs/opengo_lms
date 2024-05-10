#!/bin/bash

cd modules

for i in $(ls -d ./opigno_*); do
    pushd $i >/dev/null

    project_name=$(echo $i | cut -d'/' -f2 | sed 's/opigno/opengo/g')

    git init

    git checkout -b main

    git add .

    git commit -m "chore(*): Initial commit 🚀"

    gh repo create HelloWorldDevs/$project_name --private --source=. --remote=upstream

    git remote add origin git@github.com:HelloWorldDevs/$project_name.git

    git push -u origin main

    popd >/dev/null
done
