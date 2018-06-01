# BootStrapFast WordPress Child Theme basic structure
Child theme structure

## Setting up local development

### Option 1

1. git clone this repo in your themes folder
2. You must have the BootStrapFast parent theme
3. use the startchild.sh bash script
4. Lando init and lando start, pull if necessary
5. Update 
	- src/manifest.json 
	- style.css
	- bower.json
6. gulp && gulp watch

### Option 2

1. git clone this repo in your themes folder
2. You must have the BootStrapFast parent theme
3. Install Node.js https://nodejs.org/download/ and Lando https://docs.devwithlando.io/installation/installing.html
4. Clone this repo to your wp-content/themes folder
5. Run npm install -g npm@latest
6. Run npm install -g gulp bower
7. Run npm install
8. Run bower install
9. Lando init and lando start, pull if necessary
10. Update 
	- src/manifest.json 
	- style.css
	- bower.json
11. gulp 

### Option 3
npm install -g npm@latest && npm install -g gulp bower && npm install && bower install

### Troubleshooting
