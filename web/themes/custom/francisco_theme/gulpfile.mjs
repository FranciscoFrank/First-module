// Import the required dependencies.
import gulp from 'gulp';
import * as dartSass from 'sass';
import gulpSass from 'gulp-sass';
import concat from 'gulp-concat';

const sass = gulpSass(dartSass);

// Paths for style files.
const paths = {
  styles: {
    src: 'scss/*.scss',
    dest: 'css'
  }
};

// Function to process styles.
export function styles() {
  return gulp.src(paths.styles.src)
    .pipe(sass().on('error', sass.logError))
    .pipe(concat('styles.css'))
    .pipe(gulp.dest(paths.styles.dest));
}

// Function to watch for changes in style files.
export function watchFiles() {
  gulp.watch(paths.styles.src, styles);
}

// Set the default Gulp task to run styles first, then watchFiles.
export default gulp.series(styles, watchFiles);