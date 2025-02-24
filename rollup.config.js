export default {
    input: {
        googleDriveHandler: 'js/src/google-drive-handler.js'
    },
    output: [
        {
            dir: 'js/dist',
            format: 'umd',
            name: 'googleDriveHandler',  // Global name for your library
            globals: {
                '@googleworkspace/drive-picker-element/drive-picker': 'drivePicker',  // Global name for the external dependency
            }
        }
    ],
    external: ['@googleworkspace/drive-picker-element/drive-picker'],  // Mark the module as external
};
