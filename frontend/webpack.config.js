var webpack = require('webpack');
var HtmlWebpackPlugin = require('html-webpack-plugin');
var ExtractTextPlugin = require("extract-text-webpack-plugin");
var path = require('path');
var ENV = process.env.NODE_ENV;

module.exports = {
    entry: {
        main: [
            './src/main.ts'
        ],
        vendor: [
            './src/vendor.ts'
        ]
    },
    resolve: {
        extensions: [".ts", ".js", ".tsx", ".jsx"],
        alias: {
            vue: 'vue/dist/vue.common.js',
            'vee-validate': 'vee-validate/dist/vee-validate.js',
            jquery: 'jquery/dist/jquery.js',
            'datatables.net': 'datatables.net/js/jquery.dataTables.js',
            'datatables.net-bs': 'datatables.net-bs/js/dataTables.bootstrap.js',
            moment: 'moment/moment.js',
            axios: 'axios/dist/axios.js',
            'timepicker': 'timepicker/jquery.timepicker.js',
            'jquery.timepicker': 'timepicker/jquery.timepicker.js'
        }
    },
    output: {
        filename: ENV == 'production' ? '[name].[chunkhash].js' : '[name].[hash].js',
        path: __dirname + '/web',
        publicPath: '/'
    },
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                loader: 'ts-loader'
            },
            {
                test: /\.css$/,
                loader: ExtractTextPlugin.extract({
                    use: 'css-loader?{\"sourceMap\":false,\"importLoaders\":1,\"minimize\":true}',
                    fallback: 'style-loader'
                })
            },
            {
                test: /\.(scss|sass)$/,
                loader: ExtractTextPlugin.extract({
                    use:['css-loader?{\"sourceMap\":false,\"importLoaders\":1,\"minimize\":true}', 'sass-loader'],
                    fallback: 'style-loader'
                })
            },
            {
                test: /\.less$/,
                loader: ExtractTextPlugin.extract({
                    use:['css-loader?{\"sourceMap\":false,\"importLoaders\":1,\"minimize\":true}', 'less-loader'],
                    fallback: 'style-loader'
                })
            },

            // изображения внутри css
            {
                test: /\.(jpg|png|gif|cur|ani)$/,
                loader: 'url-loader?name=[name].[hash:20].[ext]&limit=10000'
            },

            // Конфигурация для font-awesome
            {test: /\.svg(\?v=\d+\.\d+\.\d+)?$/, loader: "file-loader?mimetype=image/svg+xml"},
            {test: /\.woff(\?v=\d+\.\d+\.\d+)?$/, loader: "file-loader?mimetype=application/font-woff"},
            {test: /\.woff2(\?v=\d+\.\d+\.\d+)?$/, loader: "file-loader?mimetype=application/font-woff"},
            {test: /\.ttf(\?v=\d+\.\d+\.\d+)?$/, loader: "file-loader?mimetype=application/octet-stream"},
            {test: /\.eot(\?v=\d+\.\d+\.\d+)?$/, loader: "file-loader"},

            // шаблоны компонентов
            {
                test: /.*\.html$/,
                loader: 'raw-loader!html-minifier-loader'
            }
        ]
    },
    plugins: [
        new ExtractTextPlugin({
            filename: '[name].[contenthash].css',
            disable: false,
            allChunks: true
        }),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'vendor'
        }),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'meta',
            chunks: ['vendor']
        }),
        new HtmlWebpackPlugin({
            template: 'src/index.html'
        }),
        // фикс бага при инклюде moment.js
        new webpack.ContextReplacementPlugin(/\.\/locale$/, null, false, /js$/),
        new webpack.ProvidePlugin({
            jQuery: 'jquery',
            $: 'jquery',
            DataTable: 'datatable.net',
            DataTableBs: 'datatable.net-bs'
        }),
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: ENV == 'production' ? '"production"' : '"development"'
            }
        })
    ],
    devServer: {
        proxy: {
            '/api': {
                target: 'http://localhost:8000',
                secure: false
            },
            '/comet': {
                taget: 'http://localhost:3001',
                secure: false
            }
        }
    }
};

if (ENV == 'production') {
    // приложение сжимается для prod-окружения
    module.exports.plugins.push(new webpack.optimize.UglifyJsPlugin({
        beautify: false,
        output: {
            comments: false
        },
        mangle: {
            screw_ie8: true
        },
        compress: {
            screw_ie8: true,
            warnings: false,
            conditionals: true,
            unused: true,
            comparisons: true,
            sequences: true,
            dead_code: true,
            evaluate: true,
            if_return: true,
            join_vars: true
        }
    }));
}
