const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

 // Global assets
 mix.scripts([
     'public/assets/js/lib/vendor.js',
     'public/assets/js/lib/jquery.js',
     'public/assets/js/lib/app.js',
     'public/assets/js/lib/filters.js',
     'public/assets/js/lib/directives.js',
     'public/assets/js/lib/ctrls/auth.js',
     'public/assets/js/lib/all.js'
 ], 'public/assets/js/main.js');
