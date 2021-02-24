const path = require('path');

const PATHS = {
  app: path.resolve(__dirname, 'resources/js'),
  base: path.resolve(__dirname, 'resources'),
  entry: path.resolve(__dirname, 'resources/js/app.js'),
  font: path.resolve(__dirname, 'resources/fonts'),
  image: path.resolve(__dirname, 'resources/images'),
  sass: path.resolve(__dirname, 'resources/sass/app.scss'),
  public: path.resolve(__dirname, 'public/assets'),
};

module.exports = env => ({
  entry: PATHS.entry,
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        loader: 'babel-loader',
        include: PATHS.app,
      },
      {
        test: /\.m.scss$/,
        use: [
          'style-loader',
          {
            loader: 'css-loader',
            options: { importLoaders: 1, modules: true, localIdentName: '[local]--[hash:base64:5]' },
          },
          'resolve-url-loader',
          {
            loader: 'sass-loader',
            options: { sourceMap: true },
          },
        ],
        include: PATHS.base,
      },
      {
        test: /^((?!\.m).)*scss/,
        use: [
          'style-loader',
          {
            loader: 'css-loader',
            options: { importLoaders: 1 },
          },
          'resolve-url-loader',
          {
            loader: 'sass-loader',
            options: { sourceMap: true },
          },
        ],
        include: PATHS.base,
      },
      {
        test: /\.(png|jpe?g|gif|svg|woff2?|eot|ttf|otf)(\?.*)?$/,
        use: [
          {
            loader: 'url-loader',
            options: {
              limit: 500,
              name: 'media/[path][name].[ext]',
            },
          },
        ],
        include: [PATHS.font, PATHS.image],
      },
    ],
  },
  resolve: {
    alias: {
      style: PATHS.sass,
    },
    extensions: ['*', '.js', '.jsx'],
  },
  output: {
    path: PATHS.public,
    filename: 'bundle.js',
    publicPath: '/assets/',
  },
  devtool: 'cheap-module-eval-source-map',
  devServer: {
    disableHostCheck: true,
    port: 3000,
    public: env ? env.DOMAIN : 'localhost',
    overlay: true,
  },
});
