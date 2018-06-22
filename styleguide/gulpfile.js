var gulp            = require('gulp');
var sass            = require('gulp-ruby-sass');
var util            = require('gulp-util');
var rename          = require('gulp-rename');
var map             = require('map-stream');
var livereload      = require('gulp-livereload');
var concat          = require('gulp-concat');
var uglify          = require('gulp-uglify');
var nano            = require('gulp-cssnano');
var watch           = require('gulp-watch');
var plumber         = require('gulp-plumber');
var replace         = require('gulp-replace');
var notify          = require('gulp-notify');
var sourcemaps      = require('gulp-sourcemaps');
var autoprefixer    = require('gulp-autoprefixer');
var jshint          = require('gulp-jshint');
var args            = require('yargs').argv;

var onError = function (err) {
    console.log(err);
};
var environment = 'RADIO';
var systems = {
    "TYPO3" : {'external': '../typo3/fileadmin/lombego/layout' , 'templatePath' : ''},
    "CONTAO_3_5" : {'external': '../contao/files/lombego' , 'templatePath' : ''},
    "MAGENTO_1" : {'external': "../magento/skin/frontend/lombego" , 'templatePath' : ''},
    "CROSS" : {'external': "../_cdn/layout" , 'templatePath' : '../_cdn/templates'},
    "CDN"  : {'external': "../_cdn/layout" , 'templatePath' : '../_cdn/templates'},
    "RADIO" : {'external': '../web/lombego/layout' , 'templatePath' : ''},
};

var targetRoot = systems[environment].external;
if(typeof targetRoot === 'undefined'){
    throw Error('Given enviorment does not exist.');
    process.exit();
}

var productionStylesOutput      = targetRoot + '/css';
var productionTargetPath = targetRoot +  '/js';

var styleguideStylesCollector   = './source/scss/styleguide.scss';
var styleguideStylesPartials    = './source/scss/**/*.scss';
var styleguideStylesOutput      = './source/css';

var styleguideSpecStylesCollector   = './source/scss/styleguide-specific.scss';
var styleguideSpecStylesPartials    = './source/scss/**/*.scss';
var styleguideSpecStylesOutput      = './public/css';

var productionStylesCollector   = './source/scss/styles.scss';
var productionStylesPartials    = './source/scss/**/*.scss';


var jsPath    = 'source/js';
var patternJsPath    = 'source/js';

var jsFiles = [
    jsPath + '/jquery-3.1.0.min.js',
    jsPath + '/plugins/*.js',
    jsPath + '/vendor/*.js',
    jsPath + '/__basicFunctions.js',
    jsPath + '/parts/*.js'

];


var jsHintFiles = [
    jsPath + '/__basicFunctions.js',
    jsPath + '/parts/*.js'
];


var patternSource  = 'source/_patterns/**/**/*.twig';


/**
 * Build styleguide CSS
 */

function createSassStyleguide(){

    livereload.listen();

    return sass(styleguideStylesCollector, {
        // styleguide css is not compressed
        style: 'expanded',
        sourcemap: true,
        loadPath: [styleguideStylesPartials,'./soruce/css/photoswipe.css']
    })
        .pipe(autoprefixer('last 4 version'))
        .pipe(plumber({
            errorHandler: onError
        }))
       // .pipe(nano())
        .pipe(rename('style.css'))
        .pipe(sourcemaps.write('maps'))
        .pipe(gulp.dest(styleguideStylesOutput))
        .pipe(livereload())
        .pipe(notify({
            message: 'Styleguide Sass compiled successfully.'
        }));
}

gulp.task('sass-styleguide', function () {
    createSassStyleguide();
    return gulp.watch(styleguideStylesPartials, createSassStyleguide);
});



/**
 * Build styleguide specific CSS
 */

function createSpecificSassStyleguide(){
    livereload.listen();

    return sass(styleguideSpecStylesCollector, {
        // do not used "compressed", it removes the @charset directive
        // we compress output via cssnano
        style: 'expanded',
        loadPath: [styleguideSpecStylesPartials]
    })
        .pipe(autoprefixer('last 4 version'))
        .pipe(plumber({
            errorHandler: onError
        }))
        .pipe(rename('pattern-scaffolding.css'))
        .pipe(gulp.dest(styleguideSpecStylesOutput))
        .pipe(livereload())
        .pipe(notify({
            message: 'Styleguide specific Sass compiled successfully.'
        }));

}

gulp.task('sass-styleguide-spec', function () {
    createSpecificSassStyleguide();
    return gulp.watch(styleguideSpecStylesPartials, createSpecificSassStyleguide);
});

function createSassProduction(){
    livereload.listen();
    return sass(productionStylesCollector, {
        // do not used "compressed", it removes the @charset directive
        // we compress output via cssnano
        style: 'expanded',
        sourcemap: true,
        loadPath: [productionStylesPartials,'./soruce/css/photoswipe.css']
    })
        .pipe(autoprefixer('last 4 version'))
        .pipe(plumber({
            errorHandler: onError
        }))
        .pipe(nano())
        .pipe(rename('styles.min.css'))
        .pipe(sourcemaps.write('maps'))
        .pipe(replace('../../','../'))
        .pipe(gulp.dest(productionStylesOutput))
        .pipe(livereload())
        .pipe(notify({
            message: 'Production Sass compiled successfully.'
        }));

}

gulp.task('sass-production-watch', function () {
    return gulp.watch(productionStylesPartials,createSassProduction);
});

gulp.task('sass-production-create', function(){
    return createSassProduction();
});




/**
 * Build styleguide JS
 */

function createStyleguideJS() {
    return gulp.src(jsFiles)
        .pipe(sourcemaps.init())
        .pipe(concat('styleguide.concat.js'))
        .pipe(gulp.dest(patternJsPath + '/tmp'))
        .pipe(rename('styleguide.js'))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(patternJsPath + '/min'))
        .pipe(notify({
            message: 'Successfully built styleguide JS.'
        }));
}

gulp.task('js-styleguide', function() {
    createStyleguideJS();
    gulp.watch(jsFiles, createStyleguideJS);
});


/**
 * Build production JS
 */
function createProductionJS(){
    return gulp.src(jsFiles)
        .pipe(concat('main.concat.js'))
        .pipe(gulp.dest(jsPath + '/tmp'))
        .pipe(rename('main.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest(productionTargetPath))
        .pipe(notify({
            message: 'Successfully built production JS.'
        }));
}

gulp.task('js-production-watch', function() {
    createProductionJS();
    gulp.watch(jsFiles, createProductionJS);
});

gulp.task('js-production-create', function() {
    return createProductionJS();
});



/**
 * JS Hint
 */
function JSHint(){
    return gulp.src(jsHintFiles)
        .pipe(jshint('.jshintrc'))
        .pipe(jshint.reporter('default'));
}

gulp.task('js-hint', function() {
    JSHint();
    gulp.watch(jsHintFiles,JSHint);
});


/**
 *  Function to rewrite the Environment for the Gulpfile without open this file in the Editor
 *  Usage: gulp rewrite-environment --env %ENVIRONMENT%
 */

gulp.task('rewrite-environment', function(){

    if(typeof(systems[args.env]) === 'undefined'){
        util.log('Error:',util.colors.red('Given environment does not exist.'));
        util.log('Use one of the following Environments:');
        var keys = Object.keys(systems);
        for(var i=0;i<keys.length;i++){
            util.log(keys[i]);
        }
        return -1;
    }
    return gulp.src('gulpfile.js')
        .pipe(replace(/var environment =.*/,'var environment = \''+args.env+'\';'))
        .pipe(gulp.dest(''));

});


gulp.task('export-lab', function(){
    return gulp.src(patternSource)
        .pipe(rename(function(opt){
            opt.dirname = opt.dirname.split('\\')[0];
            opt.dirname = opt.dirname.replace(/\d{2}-/,'');
            opt.basename = opt.basename.replace(/\d{2}-/,'');
            return opt;
        }))
        .pipe(replace(/{% (include|extend) "(atoms|molecules|organisms|templates|pages)-/g,'{% $1 "$2/'))
        .pipe(replace(/{% (include|extend) "(\w*-?\/?)*/g,'$&.twig'))
        //.pipe(notify({message:'ok'}))
        .pipe(gulp.dest(systems[environment].templatePath))

});


/**
 * handle over to 'default' task
 */

gulp.task('default', [
    'sass-styleguide',
    'sass-styleguide-spec',
    'sass-production-watch',
    'sass-production-create',
    'js-styleguide',
    'js-production-watch',
    'js-production-create',
    'js-hint'
]);

gulp.task('autoGenerate', ['sass-production-create','js-production-create'], function(){
    process.exit();
});
