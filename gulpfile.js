var gulp       = require('gulp');                   // Gulp!

var sass       = require('gulp-sass');              // Sass
var prefix     = require('gulp-autoprefixer');      // Autoprefixr
var minifycss  = require('gulp-minify-css');        // Minify CSS
var concat     = require('gulp-concat');            // Concat files
var jshint     = require('gulp-jshint');            // JS Hinting
var uglify     = require('gulp-uglify');            // Uglify javascript
var rename     = require('gulp-rename');            // Rename files
var util       = require('gulp-util');              // Writing stuff

// Create our paths to do stuff
var paths = {
    adminScripts: [
        'bower_components/Sortable/Sortable.js',
        'resources/scripts/admin_*.js'
    ],
    frontScripts: [
        'resources/scripts/components/*.js',
        'resources/scripts/_*.js',
    ],
    jshint: [
        'gulpfile.js',
        'resources/scripts/*.js',
        '!bower_components/Sortable/Sortable.js',
        '!resources/scripts/components/*.js',
        '!resources/scripts/scripts.min.js',
        '!resources/scripts/admin.min.js'
    ],
    frontSass: 'resources/style/front/base.scss',
    adminSass: 'resources/style/admin/admin.scss'
};



//
//      Compile all CSS for the site
//
//////////////////////////////////////////////////////////////////////
gulp.task('frontSass', function (){
    gulp.src(paths.frontSass)                                              // Build Our Stylesheet
        .pipe(sass({style: 'compressed', errLogToConsole: true}))     // Compile scss
        .pipe(rename('main.min.css'))                                 // Rename it
        .pipe(minifycss())                                            // Minify the CSS
        .pipe(gulp.dest('resources/style/'));                         // Set the destination to assets/css
    util.log(util.colors.green('Front-end Sass compiled & minified'));          // Output to terminal
});

gulp.task('adminSass', function (){
    gulp.src(paths.adminSass)                                              // Build Our Stylesheet
        .pipe(sass({style: 'compressed', errLogToConsole: true}))     // Compile scss
        .pipe(rename('admin.min.css'))                                 // Rename it
        .pipe(minifycss())                                            // Minify the CSS
        .pipe(gulp.dest('resources/style/'));                         // Set the destination to assets/css
    util.log(util.colors.green('Admin Sass compiled & minified'));          // Output to terminal
});



//
//      JS Hint
//
//////////////////////////////////////////////////////////////////////
gulp.task('jshint', function() {
    return gulp.src(paths.jshint)
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'));
});



//
//      Combine and Minify JS
//
//////////////////////////////////////////////////////////////////////
gulp.task('frontJS', ['jshint'], function() {
    gulp.src(paths.frontScripts)
        .pipe(concat('scripts.js'))
        .pipe(rename({suffix: '.min'}))
        .pipe(uglify())
        .pipe(gulp.dest('resources/scripts/'));
    util.log(util.colors.green('Front-end Javascript compiled and minified'));
});

gulp.task('adminJS', ['jshint'], function() {
    gulp.src(paths.adminScripts)
        .pipe(concat('admin.js'))
        .pipe(rename({suffix: '.min'}))
        .pipe(uglify())
        .pipe(gulp.dest('resources/scripts/'));
    util.log(util.colors.green('Admin Javascript compiled and minified'));
});



//
//      Default gulp task.
//
//////////////////////////////////////////////////////////////////////
gulp.task('watch', function(){

    gulp.watch('**/*.php').on('change', function(file) {
        util.log(util.colors.yellow('PHP file changed' + ' (' + file.path + ')'));
    });

    gulp.watch('**/*.phtml').on('change', function(file) {
        util.log(util.colors.yellow('PHTML file changed' + ' (' + file.path + ')'));
    });

    gulp.watch("resources/style/**/*.scss", ['frontSass', 'adminSass']);              // Watch and run sass on changes
    gulp.watch("resources/scripts/_*.js", ['jshint', 'frontJS']);        // Watch and run js on changes
    gulp.watch("resources/scripts/admin_*.js", ['jshint', 'adminJS']);        // Watch and run js on changes
    gulp.watch("resources/scripts/components/*.js", ['jshint', 'frontJS']);        // Watch and run js on changes

});

if(process.env.deploy){
    gulp.task('default', ['frontSass', 'adminSass', 'frontJS', 'adminJS']);
} else {
    gulp.task('default', ['frontSass', 'adminSass', 'jshint', 'frontJS', 'adminJS', 'watch']);
}