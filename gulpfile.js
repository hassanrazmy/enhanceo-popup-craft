const { src, dest, series } = require('gulp');
const zip = require('gulp-zip').default;
const fs = require('fs');
const path = require('path');

const PLUGIN_SLUG = 'elevoire-popup-craft';
const buildDir = path.join(__dirname, '.build');
const zipOut = path.join(__dirname, '..');

function clean(done) {
  fs.rmSync(buildDir, { recursive: true, force: true });
  done();
}

function copyFiles() {
  return src([
    'elevoire-popup-craft.php',
    'readme.txt',
    'includes/**/*',
    'assets/**/*'
  ], { base: __dirname })
    .pipe(dest(path.join(buildDir, PLUGIN_SLUG)));
}

function createZip() {
  return src(`${buildDir}/${PLUGIN_SLUG}/**/*`, { base: buildDir })
    .pipe(zip(`${PLUGIN_SLUG}.zip`))
    .pipe(dest(zipOut));
}

function cleanup(done) {
  fs.rmSync(buildDir, { recursive: true, force: true });
  done();
}

exports.zip = series(clean, copyFiles, createZip, cleanup);
exports.clean = clean;
exports.default = exports.zip;
