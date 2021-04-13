import gulp from 'gulp';
import notify from 'gulp-notify';
import beeper from 'beeper';
import plumber from 'gulp-plumber';
import sourcemaps from 'gulp-sourcemaps';
import sass from 'gulp-sass';
import postcss from 'gulp-postcss';
import autoprefixer from 'autoprefixer';
import mqpacker from 'css-mqpacker';
import browserSync from 'browser-sync';
import csso from 'gulp-csso';
import imagemin from 'gulp-imagemin';
import wpPot from 'gulp-wp-pot';
import sort from 'gulp-sort';
import uglify from 'gulp-uglify';
import browserify from 'browserify';
import babelify from 'babelify';
import buffer from 'gulp-buffer';
import tap from 'gulp-tap';
import log from 'fancy-log';
import zip from 'gulp-zip';
import del from 'del';
import rename from 'gulp-rename';
import cache from 'gulp-cached';
import yaml from 'js-yaml';
import fs from 'fs';
import dotenv from 'dotenv';

import * as pkg from './package.json';

const _paths = {
	images: ['**/assets/src/images/**/*'],
	svg: ['**/assets/src/**/*.svg'],
	scss: ['**/assets/src/**/*.scss'],
	css: ['**/assets/**/*.css'],
	js: ['**/assets/src/**/*.js'],
	distJs: ['**/assets/**/*.js', '!**/src/**/*'],
	php: ['**/*.php']
};
const paths = {};
for (let key in _paths)
	paths[key] = [
		..._paths[key],
		'!./vendor/**/*',
		'!./releases/**/*',
		'!./tmp/**/*',
		'!node_modules/**/*',
		'!**/*.min.*',
		'!Gulpfile.babel.js',
		'!**/lib/**/*'
	];

/**
 * Handle errors and alert the user.
 */
const handleErrors = err => {
	notify.onError({
		title: 'Task Failed [<%= error.message %>',
		message: 'See console.',
		sound: 'Sosumi' // See: https://github.com/mikaelbr/node-notifier#all-notification-options-with-their-defaults
	})(err);

	beeper(); // Beep 'sosumi' again.

	// Prevent the 'watch' task from stopping.
	this.emit('end');
};

/**
 * Compile Sass and run stylesheet through PostCSS.
 *
 * https://www.npmjs.com/package/gulp-sass
 * https://www.npmjs.com/package/gulp-postcss
 * https://www.npmjs.com/package/gulp-autoprefixer
 * https://www.npmjs.com/package/css-mqpacker
 */
export function compileStyles() {
	return gulp
		.src(paths.scss)
		.pipe(cache('styles'))
		.pipe(plumber({ errorHandler: handleErrors }))
		.pipe(sourcemaps.init())
		.pipe(
			sass({
				includePaths: [],
				errLogToConsole: true,
				outputStyle: 'expanded' // Options: nested, expanded, compact, compressed
			})
		)
		.pipe(
			postcss([
				autoprefixer(),
				mqpacker({
					sort: true
				})
			])
		)
		.pipe(
			sourcemaps.write('.', {
				includeContent: false,
				sourceRoot: '.'
			})
		)
		.pipe(
			rename(path => {
				path.dirname = path.dirname
					.replace('src/', '')
					.replace('/scss', '/css');
			})
		)
		.pipe(gulp.dest('.'))
		.pipe(browserSync.stream());
}

/**
 * Minify and optimize style.css.
 *
 * https://www.npmjs.com/package/gulp-csso
 */
export function minifyStyles() {
	return gulp
		.src(paths.css)
		.pipe(cache('styles-min'))
		.pipe(plumber({ errorHandler: handleErrors }))
		.pipe(csso())
		.pipe(gulp.dest('.'));
}

/**
 * Transform ES6+ to browser JS
 *
 * @returns {*}
 */
export function compileScripts() {
	return gulp
		.src(paths.js)
		.pipe(cache('scripts'))
		.pipe(
			tap(function(file) {
				log.info('Bundling ' + file.path);
				file.contents = browserify(file.path, { debug: true })
					.transform(babelify, { presets: ['@babel/preset-env'] })
					.bundle();
			})
		)
		.pipe(buffer())
		.pipe(sourcemaps.init({ loadMaps: true }))
		.pipe(
			sourcemaps.write('.', {
				includeContent: false,
				sourceRoot: '.'
			})
		)
		.pipe(
			rename(path => {
				path.dirname = path.dirname.replace('src/', '');
			})
		)
		.pipe(gulp.dest('.'));
}

/**
 * Minify script files using UglifyJS
 * @returns {*}
 */
export function minifyScripts() {
	return gulp
		.src(paths.distJs)
		.pipe(cache('scripts-min'))
		.pipe(plumber({ errorHandler: handleErrors }))
		.pipe(uglify())
		.pipe(
			rename(path => {
				path.basename += '.min';
			})
		)
		.pipe(gulp.dest('.'));
}

/**
 * Minify, concatenate, and clean SVG icons.
 *
 * https://www.npmjs.com/package/gulp-svgmin
 * https://www.npmjs.com/package/gulp-svgstore
 * https://www.npmjs.com/package/gulp-cheerio
 */
export function copyIcons() {
	return gulp
		.src(paths.svg, { base: '.' })
		.pipe(plumber({ errorHandler: handleErrors }))
		.pipe(
			rename(path => {
				path.dirname = path.dirname.replace('src/', '');
			})
		)
		.pipe(gulp.dest('.'))
		.pipe(browserSync.stream());
}

/**
 * Copy image from src/images to images. Tend to be used in development.
 *
 * @returns {*}
 */
export function copyImages() {
	return gulp
		.src(paths.images, { since: gulp.lastRun(copyImages) })
		.pipe(plumber({ errorHandler: handleErrors }))
		.pipe(
			rename(path => {
				path.dirname = path.dirname.replace('src/', '');
			})
		)
		.pipe(gulp.dest('.'))
		.pipe(browserSync.stream());
}

/**
 * Optimize images with imagemin.
 *
 * https://www.npmjs.com/package/gulp-imagemin
 */
export function optimizeImages() {
	return gulp
		.src(paths.images)
		.pipe(plumber({ errorHandler: handleErrors }))
		.pipe(
			imagemin({
				optimizationLevel: 5,
				progressive: true,
				interlaced: true
			})
		)
		.pipe(
			rename(path => {
				path.dirname = path.dirname.replace('src/', '');
			})
		)
		.pipe(gulp.dest('.'));
}

/**
 * Scan the theme and create a POT file.
 *
 * https://www.npmjs.com/package/gulp-wp-pot
 */
export function i18n() {
	const domainName = pkg.name;
	const packageName = pkg.title;

	return gulp
		.src(paths.php)
		.pipe(plumber({ errorHandler: handleErrors }))
		.pipe(sort())
		.pipe(
			wpPot({
				domain: domainName,
				package: packageName
			})
		)
		.pipe(gulp.dest('./languages/' + domainName + '.pot'));
}

/**
 * Get WP url to use in browserSync.
 * Support Chassis and .env file
 */
function getProxyUrl() {
	let config;
	try {
		config = dotenv.parse(fs.readFileSync('.env'));
		if ('WP_DEV_URL' in config) return config.WP_DEV_URL;
	} catch (e) {
		// eslint-disable-next-line
		console.log('Local environment variable file not found! Use chassis URL.');
	}

	try {
		config = yaml.safeLoad(
			fs.readFileSync('../../../config.local.yaml', 'utf8')
		);
		if (config.hosts.length > 0) return config.hosts[0];
	} catch (e) {
		// eslint-disable-next-line
		console.log(
			'Chassis local config not found! Use `localhost` as proxy URL.'
		);
	}

	return 'localhost';
}

/**
 * Process tasks and reload browsers on file changes.
 *
 * https://www.npmjs.com/package/browser-sync
 */
export function watch() {
	// Kick off BrowserSync.
	browserSync({
		open: false, // Open project in a new tab?
		injectChanges: true, // Auto inject changes instead of full reload.
		proxy: getProxyUrl(),
		watchOptions: {
			debounceDelay: 1000 // Wait 1 second before injecting.
		}
	});

	// Run tasks when files change.
	gulp.watch(paths.images, copyImages);
	gulp.watch(paths.svg, copyIcons);
	gulp.watch(paths.scss, compileStyles);
	gulp.watch(paths.js, compileScripts);
}

/**
 * Build dist files for release
 */
export const build = gulp.parallel(
	copyIcons,
	gulp.series(compileStyles, minifyStyles),
	gulp.series(compileScripts, minifyScripts),
	optimizeImages,
	i18n
);

/**
 * Copy build files to tmp folder for creating archive
 * @returns {*}
 */
export function copyBuild() {
	return gulp
		.src(
			[
				'./assets/**/*',
				'./includes/**/*',
				'./languages/*',
				'./templates/**/*',
				'./vendor/**/*',
				'./changelog.txt',
				'./LICENSE.txt',
				'./readme.txt',
				'./README.md',
				'./wc-vendors-gateway-stripe-connect.php',
				'!**/*.map',
				'!**/assets/src/**/*',
				'!**/assets/src',
				'!**/package.json',
				'!**/Gruntfile.js',
				'!**/gulpfile.js',
				'!**/bower.json',
				'!**/bower_components/**/*',
				'!**/bower_components',
				'!**/node_modules/**/*',
				'!**/node_modules'
			],
			{
				allowEmpty: true,
				base: '.'
			}
		)
		.pipe(gulp.dest(`./tmp/${pkg.name}`));
}

/**
 * Zip the build
 * @returns {*}
 */
export function zipBuild() {
	return gulp
		.src('./tmp/**/*')
		.pipe(zip(`${pkg.name}-${pkg.version}.zip`))
		.pipe(gulp.dest('./releases'));
}

/**
 * Delete tmp folder
 * @returns {*}
 */
export function cleanBuild() {
	return del(['./tmp']);
}

/**
 * Combine three tasks above to make the release.
 */
export const release = gulp.series(copyBuild, zipBuild, cleanBuild);

export default gulp.parallel(
	copyIcons,
	copyImages,
	// i18n,
	compileStyles,
	compileScripts
);
