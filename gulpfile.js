// Load the dependencies
var gulp = require('gulp'),
    wpPot = require('gulp-wp-pot'),
    sort = require('gulp-sort');

// i18n files
gulp.task('build-i18n-pot', function () {
    return gulp.src([ 'classes/**/*.php', 'templates/**/*.php', '*.php' ] )
        .pipe( sort() )
        .pipe( wpPot( {
            domain: 'wc-vendors',
            destFile:'default.pot',
            package: 'wc-vendors',
            bugReport: 'https://www.wcvendors.com',
            lastTranslator: 'Jamie Madden <support@wcvendors.com>',
            team: 'WC Vendors <support@wcvendors.com>'
        } ) )
        .pipe( gulp.dest('languages') );
});


gulp.task( 'default', [ 'build-i18n-pot' ] );
