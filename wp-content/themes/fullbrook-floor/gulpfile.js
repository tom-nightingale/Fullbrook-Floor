/**************************
 * Gulpfile Dependencies
**************************/

  let gulp = require("gulp"),
    terser = require("gulp-terser"), // Uglify, but does ES6
    gulpIf = require("gulp-if"),
    rename = require("gulp-rename"),
    browserSync = require("browser-sync").create(), // Requires the browser-sync plugin
    argv = require("yargs").argv,
    // fs = require('fs'),
    // CSS plugins
    postcss = require("gulp-postcss"),
    cssImport = require("postcss-import"),
    tailwindcss = require("tailwindcss"),
    nested = require("postcss-nested"),
    cssvars = require("postcss-simple-vars"),
    // CSS plugins used in production
    autoprefixer = require("autoprefixer"),
    cssnano = require("cssnano"),
    source = require("vinyl-source-stream"),
    buffer = require("vinyl-buffer"),
    rollup          = require('@rollup/stream'),

    // Allows for multiple input files for Rollup
    multi           = require('@rollup/plugin-multi-entry'),
    
    // Add support for require() syntax
    commonjs        = require('@rollup/plugin-commonjs'),

    // Add support for importing from node_modules folder like import x from 'module-name'
    nodeResolve     = require('@rollup/plugin-node-resolve');


class TailwindExtractor {
  static extract(content) {
    return content.match(/[A-z0-9-:\/]+/g);
  }
}

// /**************************
//  * Task Styles
// **************************/
// gulp.task('styles', function () {
//   return gulp.src('*/**.scss')
//   .pipe(sass())
//   .pipe(gulpIf(argv.production, 
//     postcss([
//       require('tailwindcss'),
//       autoprefixer(),
//       cssnano()
//     ]),
//   ))
//   .pipe(gulpIf(
//     argv.production,
//     purgecss({
//       content: ['**/*.twig'],
//       whitelistPatterns: [/nf/, /wp/, /leaflet/],
//       whitelist: [
//         'buckets--num-4',
//         'textarea',
//         'ld-area',
//         'ld-location',
//         'closed', 
//         'open', 
//         'sub-menu', 
//         'mob-nav--active',
//         'mob-nav-underlay', 
//         'sub-arrow', 
//         'mob-nav-close', 
//         'ninja-forms-field', 
//         'active',
//         'icon-angle-down',
//         'wp-caption-text',
//         'lg:w-4/5',  
//         'property-search-form',
//         'control',
//         'propertyhive-ordering',
//         'flexslider',
//         'flex-direction-nav',
//         'flex-next',
//         'flex-prev',
//         'pl-6',
//         'top-4',
//         'pl-5',
//         '2xl:w-1/3',
//         'md:flex',
//         'py-3',
//         'border',
//         'border-secondary',
//         'cf7mls_next',
//         'cf7mls_back',
//         'action-button',
//         'lg:pr-2',
//         'lg:pl-2',
//         'alignleft',
//         'aligncenter',
//         'alignright',
//         'select',
//         '2xl:p-2',
//         '3xl:p-4',
//         '2xl:w-1/4'
//       ],
//       extractors: [
//         {
//           extractor: TailwindExtractor,
//           extensions: [".twig", ".js"]
//         }
//       ]
//     })
//   ))
//   .pipe(rename('main.min.css'))
//   .pipe(gulp.dest('./dist'))
//   .pipe(browserSync.reload({
//     stream: true
//   }))
// })

/**************************
 * Task Styles
 **************************/
gulp.task("styles", function () {
  return gulp
    .src('*/**.scss')
    // Lets pipe the CSS through the below plugins
    // cssImport allows us to use @import inside CSS
    // nested allows for nesting in CSS
    // cssvars allows for variables in CSS
    .pipe(postcss([cssImport, tailwindcss, nested, cssvars]))
    // If production (i.e. on our servers) pipe that returned CSS through autoprefixer to allow for older browsers, as well as cssnano which will minify our CSS
    .pipe(gulpIf(argv.production, postcss([autoprefixer, cssnano])))
    // Now take that CSS and rename it
    .pipe(rename("main.min.css"))
    // And place the renamed file into this folder
    .pipe(gulp.dest("dist/"))
    .pipe(
      browserSync.reload({
        stream: true,
      })
    );
});


/**************************
 * Scripts using rollup.js
 * https://stackoverflow.com/questions/47632435/es6-import-module-with-gulp/59786169#59786169
**************************/

var cache;

gulp.task('scripts', function() {
  return rollup({
      // Point to the entry folder for all JS files
      input: 'js/*.js',
      // Apply plugins
      plugins: [commonjs(), nodeResolve(), multi()],
      // Use cache for better performance
      cache: cache,
      // Output bundle is intended for use in browsers
      format: 'iife',
    })
    .on('bundle', function(bundle) {
      // Update cache data after every bundle is created
      cache = bundle;
    })
    // Name of the output file.
    .pipe(source('production-dist.js'))
    .pipe(buffer())
    .pipe(gulpIf(argv.production, terser()))
    .pipe(gulp.dest('./dist'))
    .pipe(browserSync.reload({
      stream: true
    }))
});

/**************************
 * Task Watch
**************************/
gulp.task('watch', () => {
  gulp.watch(`styles/**/*.scss`, gulp.series('styles'));
  gulp.watch(`js/**/*.js`, gulp.series('scripts'));
});


/**************************
 * Task Serve
**************************/
gulp.task('serve', () => {
    browserSync.init({
    proxy: `fullbrook-floor.vm`,
    files: `**/*`,
    ghostMode : false
  })
})


/**************************
 * Gulp Automation
**************************/
gulp.task('default', gulp.parallel('styles', 'scripts', 'watch', 'serve'));
gulp.task('build', gulp.parallel('styles', 'scripts'));
