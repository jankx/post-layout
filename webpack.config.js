// Generated using webpack-cli https://github.com/webpack/webpack-cli

const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TerserPlugin = require("terser-webpack-plugin");


const isProduction = process.env.NODE_ENV == "production";

const stylesHandler = MiniCssExtractPlugin.loader;

const config = {
  entry: {
    "post-layout": [
      "./assets/src/post-layout.js",
      "./assets/scss/post-layout.scss"
    ]
  },
  output: {
    path: path.resolve(__dirname, "assets"),
    filename: isProduction ? "js/[name].min.js" : "js/[name].js",
    libraryTarget: "umd"
  },
  devServer: {
    open: true,
    host: "localhost",
  },
  devtool: "source-map",
  plugins: [
    new MiniCssExtractPlugin({
      filename: isProduction ? "css/[name].min.css" : "css/[name].css"
    }),

    // Add your plugins here
    // Learn more about plugins from https://webpack.js.org/configuration/plugins/
  ],
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/i,
        loader: "babel-loader",
      },
      {
        test: /\.s[ac]ss$/i,
        use: [stylesHandler, "css-loader", "sass-loader"],
      },
      {
        test: /\.(eot|svg|ttf|woff|woff2|png|jpg|gif)$/i,
        type: "asset",
      },

      // Add your rules for custom modules here
      // Learn more about loaders from https://webpack.js.org/loaders/
    ],
  },
  optimization: {
    minimize: isProduction,
    minimizer: [new TerserPlugin()],
  }
};

module.exports = () => {
  config.mode = "production";

  return config;
};
