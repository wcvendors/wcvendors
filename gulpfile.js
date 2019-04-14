// Load the dependencies 
const autoprefixer = require('gulp-autoprefixer');
const minifycss = require('gulp-clean-css'); 
const gulp = require('gulp'); 
const sass = require('gulp-ruby-sass');
const sort = require('gulp-sort'); 
const pump = require('pump');
const rename = require('gulp-rename');
const wpPot = require('gulp-wp-pot'); 

// i18n files 
gulp.task('build-pot', function ( cb ) {
    pump([
        gulp.src([ 'classes/**/*.php', 'templates/**/*.php', '*.php' ] ), 
        sort(), 
        wpPot( {
            domain: 'wcvendors',
            package: 'wcvendors',
            bugReport: 'https://www.wcvendors.com',
            lastTranslator: 'Jamie Madden <support@wcvendors.com>',
            team: 'WC Vendors <support@wcvendors.com>'
        } ), 
        gulp.dest('languages/wcvendors.pot')
    ], cb ); 
});

// Sass file 
gulp.task('styles', function(cb) {
    pump([
        sass( 'assets/css/*.scss', { 'sourcemap=none': true, style: 'compact' } ),
        // autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'),
        gulp.dest('assets/css'),
        rename({suffix: '.min'}),
        minifycss(),
        gulp.dest('assets/css')
    ], cb);
});


// Watch 

// Watch 
gulp.task( 'watch', function() {
    gulp.watch('assets/css/*.scss', [ 'styles' ] );
});

gulp.task( 'default', [ 'build-pot', 'styles' ] );