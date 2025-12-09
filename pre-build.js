const fs = require('fs');
const path = require('path');

const packageJson = require('./package.json');

const readmeFile = path.join(__dirname, 'readme.txt');
const readmeContent = fs.readFileSync(readmeFile, 'utf8');
if (!readmeContent.match(new RegExp(`= ${packageJson.version} =`))) {
    console.error("Changelog is missing in readme.txt");
    process.exit(1);
}
const newReadmeContent = readmeContent.replace(
    /Stable tag: \d+\.\d+\.\d+/,
    `Stable tag: ${packageJson.version}`
);
fs.writeFileSync(readmeFile, newReadmeContent);

const pluginFile = path.join(__dirname, 'cpt-commands.php');
const pluginContent = fs.readFileSync(pluginFile, 'utf8');
const newPluginContent = pluginContent.replace(
    /Version:(\s+)\d+\.\d+\.\d+/,
    `Version:$1${packageJson.version}`
);
fs.writeFileSync(pluginFile, newPluginContent);
