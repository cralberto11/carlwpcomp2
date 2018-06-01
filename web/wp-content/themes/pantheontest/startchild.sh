#script used for child theming

npm install -g npm@latest
npm install -g gulp bower
npm install
bower install

#cleanup
rm README.md
rm .gitignore
rm .gitattributes
rm .jscsrc
rm .jshintignore
rm .travis.yml
rm -R .git



#setup



gulp

# delete this file
rm startchild.sh
