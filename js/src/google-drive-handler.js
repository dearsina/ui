import {
    DrivePickerElement,
    DrivePickerDocsViewElement,
} from "@googleworkspace/drive-picker-element/drive-picker";
customElements.define("custom-drive-picker", DrivePickerElement);
customElements.define(
    "custom-drive-picker-docs-view",
    DrivePickerDocsViewElement,
);

const rootElement = document.getElementById('kycdd-google-picker-ui');
const clientId = rootElement.getAttribute('data-client-id');
const appId = rootElement.getAttribute('data-app-id');

rootElement.innerHTML = `<drive-picker client-id="${clientId}" app-id="${appId}"></drive-picker>`;

const pickerElement = document.querySelector("drive-picker");

