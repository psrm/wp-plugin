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
    scripts: [
        'resources/scripts/_*.js'
    ],
    jshint: [
        'gulpfile.js',
        'resources/scripts/*.js',
        '!resources/scripts/scripts.min.js',
    ],
    sass: 'resources/style/base.scss'
};



//
//      Compile all CSS for the site
//
//////////////////////////////////////////////////////////////////////
gulp.task('sass', function (){
    gulp.src(paths.sass)                                              // Build Our Stylesheet
        .pipe(sass({style: 'compressed', errLogToConsole: true}))     // Compile scss
        .pipe(rename('main.min.css'))                                 // Rename it
        .pipe(minifycss())                                            // Minify the CSS
        .pipe(gulp.dest('resources/style/'));                         // Set the destination to assets/css
    util.log(util.colors.green('Sass compiled & minified'));          // Output to terminal
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
gulp.task('js', ['jshint'], function() {
    gulp.src(paths.scripts)
        .pipe(concat('scripts.js'))
        .pipe(rename({suffix: '.min'}))
        .pipe(uglify())
        .pipe(gulp.dest('resources/scripts/'));
    util.log(util.colors.green('Javascript compiled and minified'));
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

    gulp.watch("resources/style/**/*.scss", ['sass']);              // Watch and run sass on changes
    gulp.watch("resources/scripts/_*.js", ['jshint', 'js']);        // Watch and run js on changes

});

if(process.env.deploy){
    gulp.task('default', ['sass', 'js']);
} else {
    gulp.task('default', ['sass', 'jshint', 'js', 'watch']);
}