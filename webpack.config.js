const path = require('path'),
   webpack = require('webpack'),
   factory = require('./env/webpack.factory'),
   { CleanWebpackPlugin } = require('clean-webpack-plugin'),
   CopyWebpackPlugin = require('copy-webpack-plugin'),
   MiniCssExtractPlugin = require('mini-css-extract-plugin');

const { mode, entries } = factory({
   pagesSrc: './src/Pages/',
});

console.log(`MODE: ${mode}\n`);

const config = {
   entry: entries, //entry points of project
   resolve: {
      fallback: { // webpack 5 util errors fix
         util: require.resolve("util/"),
         path: require.resolve("path-browserify"),
         crypto: require.resolve("crypto-browserify"),
         buffer: require.resolve("buffer/"),
         https: require.resolve("https-browserify"),

         "assert": require.resolve("assert/"),
         "stream": require.resolve("stream-browserify"),
         "url": require.resolve("url/"),
         "http": require.resolve("stream-http"),

         "vm": require.resolve("vm-browserify"),
         "os": require.resolve("os-browserify/browser"),
         "constants": require.resolve("constants-browserify"),
      },
   },
   output: {
      filename: (pathData, assetInfo) => {
         //console.log('path data:', pathData);

         //console.log('asset info:', assetInfo);
         if (mode == 'development') {
            const targetFolder = './src/Pages/[name]/dist/';
            return targetFolder + '[name].bundle.js';
         } else {
            return './dist/[name]/[name].bundle.js';
         }
      },
      path: path.resolve(__dirname), //target folder
   },
   plugins: [
      new CleanWebpackPlugin({
         cleanOnceBeforeBuildPatterns: ['./dist/**/*'], //dist folder clean up
      }),
      new CopyWebpackPlugin({
         patterns: [
            //{ from: 'src/Attach', to: 'dist/Attach' },
            { from: 'php', to: 'dist/php' },
            { from: 'src/Root', to: 'dist' },
         ],
      }),
      new MiniCssExtractPlugin({ //scss compilation //./dist/index.css
         filename: ({ name }) => {
            if (mode == 'development') {
               return './src/Pages/[name]/dist/[name].css';
            } else {
               return './dist/[name]/[name].css';
            }
         },
      }),
      new webpack.ProvidePlugin({ //connecting jquery
         $: 'jquery',
         jQuery: 'jquery',
      }),
   ],
   module: {
      rules: [
         {
            test: /\.css$/, //prosessing of sass
            use: [
               'style-loader', //put css text as style tags on page, css won't be applied without it
               {
                  loader: MiniCssExtractPlugin.loader, // store css to files in ./dist
                  options: {
                     //  // only enable hot reloading in development
                     //  hmr: mode === 'development',
                     //  // if hmr does not work, this is a forceful method.
                     //  reloadAll: true,
                     esModule: false, // fix warnings
                  },
               },
               {
                  loader: 'css-loader', //CSS to CommonJS, make possible require and import css files in js files
                  options: {
                     //url: false, //don't resolve url links in css files
                  }
               },
               'postcss-loader', //added to use autoprefixer
            ],
         },
         {
            test: /\.s[ac]ss$/, //prosessing of sass
            use: [
               'style-loader', //put css text as style tags on page, css won't be applied without it
               {
                  loader: MiniCssExtractPlugin.loader, // store css to files in ./dist
                  options: {
                     //  // only enable hot reloading in development
                     //  hmr: mode === 'development',
                     //  // if hmr does not work, this is a forceful method.
                     //  reloadAll: true,
                     esModule: false, // fix warnings
                  },
               },
               {
                  loader: 'css-loader', //CSS to CommonJS, make possible require and import css files in js files
                  options: {
                     //url: false, //don't resolve url links in css files
                  }
               },
               'postcss-loader', //added to use autoprefixer
               'sass-loader', //complie SASS to CSS
            ]
         },
         {
            test: /\.(png|jpe?g|gif|ttf|svg|webp)$/,
            use: [
               {
                  loader: 'file-loader',
                  options: {
                     name: '[path][name].[ext]',
                     context: path.resolve(__dirname, 'src/Attach'),
                     outputPath: 'dist/Attach',
                     publicPath: (mode == 'development' ? './../../../Attach/' : '../Attach/'),
                     useRelativePaths: true,
                  },
               },
            ],
         },
      ]
   }
};

const development = {
   devtool: 'inline-source-map', //js debugger
   devServer: {
      contentBase: './dist', //localhost root folder
      open: true, //open in this browser
      host: '0.0.0.0',
   },
   watch: true,
};

const production = {

};

if (mode == 'development')
   factory.objectMerge(config, development);
else {
   factory.objectMerge(config, production);
   config.module.rules.push({
      test: /\.m?js$/,
      loader: 'babel-loader',
   });
}

factory.exclude(config, ['node_modules', 'dist']);

//console.log(config);

module.exports = config; 