const LiveReloadPlugin = require('webpack-livereload-plugin');

require('dotenv').config()

const
  mix = require('laravel-mix'),
  webpackConfig = require('./webpack.config'),
  mxOptions = {
    hmrOptions: {},
  },

  buildModule = process.env.APP,

  files = require('./vue.src')

if (
  process.env.mode == 'hot' &&
  process.env.HOT_HOST &&
  process.env.HOT_PORT
) {
  mxOptions.hmrOptions.port = process.env.HOT_PORT
  mxOptions.hmrOptions.host = process.env.HOT_HOST
}

mix.override(webpackConfig => {
  // BUG: vue-loader doesn't handle file-loader's default esModule:true setting properly causing
  // <img src="[object module]" /> to be output from vue templates.
  // WORKAROUND: Override mixs and turn off esModule support on images.
  // FIX: When vue-loader fixes their bug AND laravel-mix updates to the fixed version
  // this can be removed
  webpackConfig.module.rules.forEach(rule => {
    if (rule.test.toString() === '/(\\.(png|jpe?g|gif|webp)$|^((?!font).)*\\.svg$)/') {
      if (Array.isArray(rule.use)) {
        rule.use.forEach(ruleUse => {
          if (ruleUse.loader === 'file-loader') {
            ruleUse.options.esModule = false;
          }
        });
      }
    }
  });
});


mix.webpackConfig(webpackConfig)
  .extract(['vue'])
  .options(mxOptions) //  modules to extract to vendor.js
  
if (process.env.NODE_ENV == 'production') production(files)
else buildAll(files)

function production(files) {
  buildAll(files)
  mix.version()
  .options({
    postCss: [
      require('postcss-custom-properties')
    ],
    esModule: false,
  })
}

function buildAll(files) {
  /**
   * @param {Object} files 
   * javascript and scss fiels to be build.
   */
  Object.values(files).forEach(({
    js,
    sass
  }) => {
    if (js)
      mix.js(js.from, js.to).vue({ version: 2 });
    if (sass)
      mix.sass(sass.from, sass.to,{
        sassOptions: {
        }
      });
  })
}

if (process.env.NODE_ENV === "testing") {
  const nodeExternals = require('webpack-node-externals')
  mix.webpackConfig((webpack) => {
    return {
      devtool: "inline-cheap-module-source-map",
      externals: [nodeExternals()],
      module: {
        rules: [
          {
            test: /\.scss/,
            use: "null-loader",
          }
        ]
      }
    }
  })
}

const h_port = process.env.HMR_PORT
const h_host = process.env.HMR_HOST

if(h_port && h_host){
  mix.options({
    hmrOptions: {
      host: 'localhost',
      port: 8080
    }
  })
} else {
  webpackConfig.plugins.push(
    new LiveReloadPlugin()
  )
}