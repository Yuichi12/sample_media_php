'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var changed = require('gulp-changed');
var browser = require('browser-sync');
var autoprefixer = require('gulp-autoprefixer');
var plumber = require('gulp-plumber');
var sourcemaps = require('gulp-sourcemaps');
var browserify = require('browserify');
// バンドルしたjsファイルをgulpで使える形に変換するためのプラグイン
var source = require('vinyl-source-stream');
var imagemin = require('gulp-imagemin');
var svgmin = require('gulp-svgmin');
var uglify = require('gulp-uglify');
var jshint = require('gulp-jshint');
var rename = require('gulp-rename');
var progeny = require('gulp-progeny');


// webサーバーを立ち上げる
gulp.task('server', function () {
  browser({
    proxy: 'localhost:8888'
    /*server: {
        baseDir: './'
    }*/
  });
});

// ブラウザをリロードする
gulp.task('reload', function () {
  browser.reload();
});

// sassをコンパイルしてリロードする
gulp.task('sass', function () {
  gulp.src('./src/scss/**/*.scss')
    .pipe(plumber())
    .pipe(progeny())
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded'
    }))
    .pipe(autoprefixer())
    .pipe(sourcemaps.write('./maps/'))
    .pipe(gulp.dest('./dist/css/'))
    .pipe(browser.reload({
      stream: true
    }));
});

// jsファイルを結合する
gulp.task('browserify',
  function () {
    browserify({
        entries: ['src/js/main.js']
      })
      .bundle()
      .on('error', function (err) {
        console.log(err.message);
        this.emit('end');
      })
      .pipe(source('bundle.js'))
      .pipe(plumber())
      .pipe(jshint())
      .pipe(jshint.reporter('default'))
      .pipe(gulp.dest('./dist/js/'));
  });

// jsファイルを圧縮してリロードする
gulp.task('uglify', function () {
  gulp.src(['dist/js/bundle.js', '!./dist/js/min/'])
    .pipe(plumber())
    .pipe(uglify())
    .pipe(rename('bundle.min.js'))
    .pipe(gulp.dest('./dist/js/min/'))
    .pipe(browser.reload({
      stream: true
    }));
});

// 画像を圧縮する
gulp.task('imagemin', function () {
  gulp.src('./src/img/*.+(jpg|jpeg|png|gif)')
    .pipe(changed('./dist/img/'))
    .pipe(imagemin([
        imagemin.gifsicle({
        interlaced: true
      }),
        imagemin.jpegtran({
        progressive: true
      }),
        imagemin.optipng({
        optimizationlevel: 5
      })
    ]))
    .pipe(gulp.dest('./dist/img/'))
    .pipe(browser.reload({
      stream: true
    }));

  // svgファイルの圧縮
  gulp.src('./src/img/*.svg')
    .pipe(changed('./dist/img/'))
    .pipe(svgmin())
    .pipe(gulp.dest('./dist/img/'))
    .pipe(browser.reload({
      stream: true
    }));
});

// 変更を監視
gulp.task('watch', ['server', ], function () {
  gulp.watch('**/*.php', ['reload']);
  gulp.watch('**/*.html', ['reload']);
  gulp.watch('src/scss/**/*.scss', ['sass']);
  gulp.watch('src/js/**/*.js', ['browserify']);
  gulp.watch('./dist/js/bundle.js', ['uglify']);
  gulp.watch('src/img/**/*.+(jpg|jpeg|png|gif|svg)', ['imagemin']);
});
