// Rollup config with 'import' statements for ES module compatibility

import nodeResolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';

export default {
    input: {
        "google-drive-handler": 'js/src/google-drive-handler.js'
    },
    output: [
        {
            dir: '../../../html/app/js',
            format: 'umd',
            name: 'google-drive-handler',  // Global name for your library
            globals: {
                '@googleworkspace/drive-picker-element/drive-picker': 'drivePicker',  // Global name for the external dependency
            }
        }
    ],
    external: [],  // Don't mark this as external, it should be bundled
    plugins: [
        nodeResolve({
            mainFields: ['module', 'main'],  // Resolve the correct field from the package.json
        }),
        commonjs(),  // Handle CommonJS modules
    ]
};
