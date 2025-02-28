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

function setFolderDetails(event) {
    const folderIdElement = document.querySelector("input[name='folder_id']");
    const folderNameElement = document.querySelector("input[name='folder_name']");
    folderIdElement.value = event.detail.docs[0].id;
    folderNameElement.value = event.detail.docs[0].name;
}

function setTokenValue(event) {
    const tokenElement = document.querySelector("input[name='token']");
    tokenElement.value = event.detail.token;
}

window.initiateGoogleDriveModal = function(workflow_id) {
    if(document.querySelector("custom-drive-picker")) {
        const pickerElement = document.querySelector("custom-drive-picker");
        pickerElement.visible = true;
    } else {
        const rootElement = document.getElementById('kycdd-google-picker-ui');
        const clientId = rootElement.getAttribute('data-client-id');
        const appId = rootElement.getAttribute('data-app-id');

        rootElement.innerHTML = `<custom-drive-picker 
            client-id="${clientId}"
            app-id="${appId}"
            mine-only="true"
            multiselect="false"
            nav-hidden="true"
            title="Select a folder">
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