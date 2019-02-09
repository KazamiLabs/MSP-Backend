let mix = require('laravel-mix');

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

mix.setPublicPath('../public/')
    .styles([
        'node_modules/vue-material/dist/vue-material.min.css'
    ], './../public/css/core.css')
    .sass('assets/sass/material-kit.scss', 'css')
    .js('assets/js/app.js', 'js')
    .sass('assets/sass/app.scss', 'css')
   // .css('node_modules/material-kit/assets/css/material-kit.min.css', 'css')
   // .scripts([
   //    'node_modules/material-kit/assets/js/core/jquery.min.js',
   //    'node_modules/material-kit/assets/js/core/popper.min.js',
   // ], './../public/js/core.js')
   // .js('node_modules/material-kit/assets/js/core/jquery.min.js', 'js')
   // .js('node_modules/material-kit/assets/js/core/popper.min.js', 'js')
   // .js('node_modules/material-kit/assets/js/material-kit.js', 'js')
