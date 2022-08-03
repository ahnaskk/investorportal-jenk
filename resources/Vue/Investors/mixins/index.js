import camelCase from 'lodash/camelCase';
const requireMixins = require.context(
    './src',
    false,
    /\.js$/
);

const mixins = {};

requireMixins.keys().forEach(filename => {
    const segment = camelCase(filename.replace(/(\.\/|\.js)/g,''));
    mixins[segment] = requireMixins(filename).default || requireModule(filename);
});

export default mixins;
