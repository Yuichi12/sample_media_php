#!/bin/sh

touch index.html
mkdir src dist img
cd dist
mkdir css js
cd ../src
mkdir scss js
cd js
touch main.js
cd ../scss
touch style.scss
mkdir foundation layout object
cd foundation
touch _variable.scss _base.scss _reset.scss
cd ../layout
touch _layout.scss
cd ../object
mkdir compornent project utility
cd compornent
touch _container.scss _menu-trigger.scss
cd ../project
touch _header.scss _hero.scss _form.scss _footer.scss
cd ../utility
touch _color.scss _margin.scss