// Load the dependencies
var gulp = require('gulp'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    minifycss = require('gulp-clean-css'),
    jshint = require('gulp-jshint'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat'),
    notify = require('gulp-notify'),
    cache = require('gulp-cache'),
    livereload = require('gulp-livereload'),
    del = require('del'),
    wpPot = require('gulp-wp-pot'),
    sort = require('gulp-sort'),
    pump = require('pump');


// Styles
gulp.task('styles', function(cb) {
   pump([
        gulp.src( 'assets/css/*.scss' ),
        sass( { 'sourcemap=none': true, outputStyle: 'compact' } ),
        autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'),
        gulp.dest('assets/css'),
        rename({suffix: '.min'}),
        minifycss(),
        gulp.dest('assets/css')
    ], cb);
});

// i18n files
gulp.task('build-i18n-pot', function () {
    return gulp.src([ 'classes/**/*.php', 'templates/**/*.php', '*.php' ] )
        .pipe( sort() )
        .pipe( wpPot( {
            domain: 'wc-vendors',
            package: 'WC Vendors Marketplace',
            bugReport: 'https://www.wcvendors.com',
            lastTranslator: 'Jamie Madden <translate@wcvendors.com>',
            team: 'WC Vendors <translate@wcvendors.com>'
        } ) )
        .pipe( gulp.dest('languages/wc-vendors.pot') );
});


gulp.task( 'default', [ 'styles', 'build-i18n-pot' ] );
