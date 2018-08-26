var path = require("path");
var webpack = require("webpack");
var ExtractTextPlugin = require("extract-text-webpack-plugin");

module.exports = {
    entry: [
        "./input_templates/new/assets/js/main.js",
        "./input_templates/new/assets/scss/_main.scss",
    ],
    output: {
        filename: "./input_templates/new/assets/js/main.min.js"
    },
    module: {
        rules: [
            {
                test: /\.(sass|scss)$/,
                loader: ExtractTextPlugin.extract([{ loader: 'css-loader', options: { minimize: true }}, "sass-loader"])
            },
            {
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                use: {
                    loader: 'babel-loader'
                }
            }
        ]
    },
    plugins: [
        new ExtractTextPlugin({
            filename: "./input_templates/new/assets/css/style.css"
        })
    ]
};
