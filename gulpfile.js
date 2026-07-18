const { src, dest, series } = require('gulp');
const zip = require('gulp-zip').default;
const fs = require('fs');
const path = require('path');

const PLUGIN_SLUG = 'elevoire-popup-craft';

const distDir = path.join(__dirname, 'dist');
const zipName = `${PLUGIN_SLUG}.zip`;
const tempDir = path.join(distDir, PLUGIN_SLUG);

function clean(done) {
  fs.rmSync(distDir, { recursive: true, force: true });
  done();
}

function copyFiles() {
  return src([
    'elevoire-popup-craft.php',
    'readme.txt',
    'includes/**/*',
    'assets/**/*'
  ], { base: __dirname })
    .pipe(dest(tempDir));
}

function createZip() {
  return src(`${tempDir}/**/*`, { base: distDir })
    .pipe(zip(zipName))
    .pipe(dest(distDir));
}

function cleanupTemp(done) {
  fs.rmSync(tempDir, { recursive: true, force: true });
  done();
}

exports.zip = series(clean, copyFiles, createZip, cleanupTemp);
exports.clean = clean;
exports.default = exports.zip;
