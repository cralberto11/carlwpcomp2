# carlwpcomp2

[![CircleCI](https://circleci.com/gh/cralberto11/carlwpcomp2.svg?style=shield)](https://circleci.com/gh/cralberto11/carlwpcomp2)
[![Dashboard carlwpcomp2](https://img.shields.io/badge/dashboard-carlwpcomp2-yellow.svg)](https://dashboard.pantheon.io/sites/e61d1bc3-c2fb-4264-b8d8-708fd696ca45#dev/code)
[![Dev Site carlwpcomp2](https://img.shields.io/badge/site-carlwpcomp2-blue.svg)](http://dev-carlwpcomp2.pantheonsite.io/)

This is a 2 model repo where we only push our changes to the GitHub repo instead to Pantheon’s own git repo.

Disable all file modifications happening in the backend like the customizer, theme and plugin installation.

Updating Core files, plugins and themes should be done from composer.
1) Please refer to https://wpackagist.org/ when installing themes and plugins.
2) Update the composer.json file
3) Do 'composer update'

Replicating site to local
