(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(require('@googleworkspace/drive-picker-element/drive-picker')) :
    typeof define === 'function' && define.amd ? define(['@googleworkspace/drive-picker-element/drive-picker'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.drivePicker));
})(this, (function (drivePicker) { 'use strict';

    customElements.define("custom-drive-picker", drivePicker.DrivePickerElement);
    customElements.define(
        "custom-drive-picker-docs-view",
        drivePicker.DrivePickerDocsViewElement,
    );

}));
