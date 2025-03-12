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

const rootElement = document.getElementById('kycdd-google-picker-ui');
const KEY_DATA = JSON.parse(atob(rootElement.getAttribute('data-object')));

function setFolderDetails(event) {
    console.log(event)
    window.ajaxCall(KEY_DATA.action,
        KEY_DATA.rel_table,
        KEY_DATA.rel_id,
        {
            session_id: KEY_DATA.session_id,
            action:     KEY_DATA.action,
            subscription_id:     KEY_DATA.subscription_id,
            workflow_id:     KEY_DATA.workflow_id,
            folder_id:  event.detail.docs[0].id,
        }
    );
    window.close();
}

function setTokenValue(event) {
    console.log(event)
    window.ajaxCall(KEY_DATA.action,
        KEY_DATA.rel_table,
        KEY_DATA.rel_id,
        {
            session_id: KEY_DATA.session_id,
            action:     KEY_DATA.action,
            subscription_id:     KEY_DATA.subscription_id,
            workflow_id:     KEY_DATA.workflow_id,
            token:      event.detail.token,
        }
    );
}

function initiateGoogleDriveModal() {
    if(document.querySelector("custom-drive-picker")) {
        const pickerElement = document.querySelector("custom-drive-picker");
        pickerElement.visible = true;
    } else {
        rootElement.innerHTML = `<custom-drive-picker 
            client-id="${KEY_DATA.client_id}"
            app-id="${KEY_DATA.app_id}"
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
        pickerElement.addEventListener("picker:oauth:response", console.log);
        pickerElement.addEventListener("picker:oauth:error", console.log);
    }
}

initiateGoogleDriveModal();