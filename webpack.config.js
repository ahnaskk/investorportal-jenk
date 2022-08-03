const resolve = (dir) => require("path").resolve(__dirname,dir);
// Alias Path Base
const investorsSrc = "resources/Vue/Investors";
const investorsTemplate = `${investorsSrc}/template`;

const adminSrc = "resources/Vue/Admin";
const adminTemplate = `${adminSrc}/template`;

const merchantSrc = 'resources/Vue/Merchants';

module.exports = {
    module:{
        rules:[
            {
                test: /\.(mp4|pdf)$/,
                use:'file-loader'
            },
            {
                test: /\.tsx?$/,
                use: 'ts-loader',
                exclude: /node_modules/,
            },
        ]
    },
    resolve:{
        extensions: ['.js','.vue','.scss', '.json','.ts'],
        alias:{
            "@": resolve(investorsSrc),
            "@data": resolve(`${investorsSrc}/data`),
            "@directive": resolve(`${investorsSrc}/directives`),
            "@v": resolve(`${investorsTemplate}/views`),
            "@h":resolve(`${investorsTemplate}/helpers`),
            "@c": resolve(`${investorsTemplate}/components`),
            "@l": resolve(`${investorsTemplate}/layout`),
            "~": resolve(`${investorsTemplate}/styles`),
            "~c": resolve(`${investorsTemplate}/styles/components`),
            "~l": resolve(`${investorsTemplate}/styles/layout`),
            "~v": resolve(`${investorsTemplate}/styles/views`),
            "@asset": resolve(`${investorsSrc}/assets`),
            "@image": resolve(`${investorsSrc}/assets/images`),
            "@n": resolve("node_modules"),

            "@a": resolve(adminSrc),
            "@a/data": resolve(`${adminSrc}/data`),
            "@av": resolve(`${adminTemplate}/views`),
            "@ac": resolve(`${adminTemplate}/components`),
            "@al": resolve(`${adminTemplate}/layout`),
            "~a": resolve(`${adminTemplate}/styles`),
            "~ac": resolve(`${adminTemplate}/styles/components`),
            "~al": resolve(`${adminTemplate}/styles/layout`),
            "~av": resolve(`${adminTemplate}/styles/views`),

            "@merchant": resolve(merchantSrc),
            "@merchantData":resolve(`${merchantSrc}/data`),
            "@merchantViews":resolve(`${merchantSrc}/views`),
            "@merchantComponents":resolve(`${merchantSrc}/components`),
            "~merchant":resolve(`${merchantSrc}/styles`),
            "~merchantComponents":resolve(`${merchantSrc}/styles/components`),
        }
    },
    plugins: [ ]
}