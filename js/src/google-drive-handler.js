// noinspection CssInvalidHtmlTagReference

import {
    DrivePickerElement,
    DrivePickerDocsViewElement,
} from "@googleworkspace/drive-picker-element/drive-picker";
customElements.define("custom-drive-picker", DrivePickerElement);
customElements.define(
    "custom-drive-picker-docs-view",
    DrivePickerDocsViewElement,
);


function checkElementLoaded(selector, callback) {
    const element = document.querySelector(selector);
    if (element) {
        callback(element); // If the element is found, run the callback function
    } else {
        // If the element is not found, set a timeout to check again
        setTimeout(() => checkElementLoaded(selector, callback), 100); // Check every 100ms
    }
}

function formatDriveView() {
    const iframeElement = document.querySelector("iframe.picker.shr-bb-shr-cb-shr-cc.picker-dialog-bg");
    const dialogBackgroundElement = document.querySelector("div.picker.shr-bb-shr-cb-shr-cc.picker-dialog-bg");
    const pickerDialogElement = document.querySelector("div.picker.shr-bb-shr-cb.picker-dialog");
    const parentElement = document.getElementById("kycdd-google-picker-ui-holder");

    parentElement.appendChild(iframeElement);
    parentElement.appendChild(dialogBackgroundElement);
    parentElement.appendChild(pickerDialogElement);
}

function setFolderDetails(event) {
    console.log(event);
    const folderIdElement = document.querySelector("input[name='folder_id']");
    const folderNameElement = document.querySelector("input[name='folder_name']");
    folderIdElement.value = event.detail.docs[0].id;
    folderNameElement.value = event.detail.docs[0].name;
}

function setTokenValue(event) {
    console.log(event);
    const tokenElement = document.querySelector("input[name='token']");
    tokenElement.value = event.detail.token;

    checkElementLoaded("div.picker.shr-bb-shr-cb.picker-dialog", formatDriveView);
}

window.initiateGoogleDriveModal = function(workflow_id) {
    if(document.querySelector("custom-drive-picker")) {
        const pickerElement = document.querySelector("custom-drive-picker");
        pickerElement.visible = true;
        formatDriveView();
    } else {
        const rootElement = document.getElementById('kycdd-google-picker-ui');
        const clientId = rootElement.getAttribute('data-client-id');
        const appId = rootElement.getAttribute('data-app-id');
        const tokenElement = document.querySelector("input[name='token']");

        rootElement.innerHTML = `<custom-drive-picker 
            client-id="${clientId}"
            app-id="${appId}"
            mine-only="true"
            multiselect="false"
            nav-hidden="true"
            hide-title-bar="true">
            <custom-drive-picker-docs-view 
                view-id="FOLDERS"
                include-folders="true"
                select-folder-enabled="true"
                mode="LIST"
                owned-by-me="true"
                parent="root"
            ></custom-drive-picker-docs-view>
        </custom-drive-picker>`;

        const pickerElement = document.querySelector("custom-drive-picker");
        pickerElement.addEventListener("picker:authenticated", setTokenValue);
        pickerElement.addEventListener("picker:picked", setFolderDetails);
        pickerElement.addEventListener("picker:canceled", console.log);
    }
}