# carlwpcomp2

[![CircleCI](https://circleci.com/gh/cralberto11/carlwpcomp2.svg?style=shield)](https://circleci.com/gh/cralberto11/carlwpcomp2)
[![Dashboard carlwpcomp2](https://img.shields.io/badge/dashboard-carlwpcomp2-yellow.svg)](https://dashboard.pantheon.io/sites/e61d1bc3-c2fb-4264-b8d8-708fd696ca45#dev/code)
[![Dev Site carlwpcomp2](https://img.shields.io/badge/site-carlwpcomp2-blue.svg)](http://dev-carlwpcomp2.pantheonsite.io/)

This is a 2 model repo where we only push our changes to the GitHub repo instead to Pantheon’s own git repo.

WordPress Composer workflow is best suited if all the configs happening in the database are disabled like the customizer, theme and plugin installation. Code changes will be happening from the GitHub repo, then to dev, test and live. Changing config via live is not recommended unless you are familiar with Configuration Management via the wp-cfm plugin.

Updating Core files, plugins and themes should be done from composer.
1) Please refer to https://wpackagist.org/ when installing themes and plugins.
2) Update the composer.json file
3) Do 'composer update'
4) Git add, commit and push to master. Wait for the CircCI to finish its job and be successful.
5) Push from dev, to Test then Live from the Pantheon dashboard or via Terminus.

Replicating site to local
This will be depending on your local dev environment (Chassis, Vagrant, Lando, XAMPP, etc). Basic concept:
1) Git clone this repo and put it in the relative folder to match your local development environment.
2) Composer update. (Composer must be installed).
3) Search replace the db to match the localhost url
