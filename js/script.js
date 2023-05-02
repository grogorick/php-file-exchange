let messagesBox = document.querySelector('#messages');
let dragDropIndicator = document.querySelector('#drag-drop-indicator');
let fileInput = document.querySelector('#file-input');
let fileInputLabel = document.querySelector('#file-input + label');
let fileUploadForm = document.querySelector('#file-upload-form');
let fileUploadButton = fileUploadForm.querySelector('input[type="submit"]');
let fileList = document.querySelector('#file-list');
let fileItemTemplate = fileList.querySelector('#file-item-template');


fileInput.classList.add('hidden');
fileInputLabel.classList.remove('hidden');

let startTimeoutToCancelDragDrop = debounce(200, () =>
{
  dragDropIndicator.classList.add('hidden');
});
on(document, 'dragover', e =>
{
  e.dataTransfer.dropEffect = 'copy';
  dragDropIndicator.classList.remove('hidden');
  startTimeoutToCancelDragDrop();
});

on(document, 'drop', e =>
{
  dragDropIndicator.classList.add('hidden');
  cleanupPreviousUploadItems();
  if (prepareFilesForUpload(e.dataTransfer.files))
    uploadFiles();
});

fileUploadButton.classList.add('hidden');
on(fileInput, 'change', () =>
{
  cleanupPreviousUploadItems();
  if (prepareFilesForUpload(fileInput.files))
    fileUploadButton.classList.remove('hidden');
});

on(fileUploadForm, 'submit', () =>
{
  fileUploadButton.classList.add('hidden');
  uploadFiles();
});

let isUploading = false;
window.onbeforeunload = () =>
{
  if (isUploading) {
    return L('warning_close_while_uploading');
  }
};

fileList.querySelectorAll('.row.item').forEach(prepareFileDeleteForm);
function prepareFileDeleteForm(fileItem)
{
  on(fileItem.querySelector('.file-delete form'), 'submit', () =>
  {
    deleteFiles([fileItem]);
  });
}

function deleteFiles(fileItems)
{
  let formData = new FormData();
  let deleteFileItems = [];
  for (const fileItem of fileItems) {
    let fileName = fileItem.querySelector('.file-delete input[name="file"]').value;
    formData.append('files[]', fileName);
    deleteFileItems.push([fileItem, fileName]);
    fileItem.classList.add('processing');
  }
  xhRequestPost('./?action=delete', formData, null,
    responseText =>
    {
      if (responseText.length) {
        let responseList = JSON.parse(responseText);
        for (const [di, serverFile, [fileError, ...errorArgs]] of responseList) {
          const [fileItem, fileName] = deleteFileItems[di];
          fileItem.classList.remove('processing');

          if (fileError) {
            fileItem.classList.add('error');
            fileItem.querySelector('.file-details').innerHTML = L(fileError, ...errorArgs);
          }
          else {
            fileItem.remove();
            showMessage(L('file_deleted') + ' `' + serverFile.name + '`');
          }
        }
      }
    },
    true);
}

function cleanupPreviousUploadItems()
{
  fileUploadButton.classList.add('hidden');
  fileList.querySelectorAll('.row.item.success').forEach(el => el.classList.remove('success'));
  fileList.querySelectorAll('.row.item.prepared, .row.item.error').forEach(el => el.remove());
}

let selectedFiles = [];
let approvedFiles = [];
function prepareFilesForUpload(files)
{
  selectedFiles = files;
  approvedFiles = [];

  for (let i = 0; i < files.length; ++i) {
    let file = files[i];

    let fileError = false;
    if (file.size === 0) {
      fileError = L('upload_failed_file_empty_or_directory');
    }
    else if (file.size > maxFileSize) {
      fileError = L('upload_failed_file_size', fileSizeStr(file.size), fileSizeStr(maxFileSize));
    }
    else {
      let fileExt = file.name.substring(file.name.lastIndexOf('.'));
      if (allowedFileExtensions.length && !allowedFileExtensions.includes(fileExt)) {
        fileError = L('upload_failed_file_type', fileExt);
      }
      else if (prohibitedFileExtensions.includes(fileExt)) {
        fileError = L('upload_failed_file_type', fileExt);
      }
    }

    let newFileItem = fileItemTemplate.cloneNode(true);
    newFileItem.removeAttribute('id');
    newFileItem.classList.remove('hidden');
    fileList.prepend(newFileItem);

    newFileItem.querySelector('.file-name a').innerHTML = file.name;

    if (fileError) {
      newFileItem.classList.add('error');
      newFileItem.querySelector('.file-details').innerHTML = fileError;
    }
    else {
      newFileItem.classList.add('prepared');
      newFileItem.querySelector('.file-size').innerHTML = fileSizeStr(file.size);
      approvedFiles.push([i, newFileItem]);
    }
  }
  return approvedFiles.length;
}

function uploadFiles()
{
  if (!approvedFiles.length) {
    fileInput.value = null;
    return;
  }

  isUploading = true;

  let formData = new FormData();
  let uploadFileItems = [];
  let accuFileSize = 0;
  let i = 0;
  for (; i < approvedFiles.length; ++i) {
    const [si, fileItem] = approvedFiles[i];
    let file = selectedFiles[si];
    if ((accuFileSize += file.size) < maxFileSize) {
      formData.append('files[]', file);
      uploadFileItems.push([fileItem, file]);
      fileItem.classList.remove('prepared');
      fileItem.classList.add('processing');
    }
    else
      break;
  }
  approvedFiles.splice(0, i);

  xhRequestPost('./?action=upload', formData,
    progress =>
    {
      let percent = (100 * progress.loaded / progress.total) + '%';
      for (const [fileItem, _] of uploadFileItems) {
        fileItem.style.setProperty('--progress', percent);
      }
    },
    responseText =>
    {
      isUploading = false;

      if (responseText.length) {
        let responseList = JSON.parse(responseText);
        if (responseList[0] === null) {
          const [uploadError, ...errorArgs] = responseList[2];
          let errorMsg = L(uploadError, ...errorArgs);

          for (const [fileItem, _] of uploadFileItems) {
            fileItem.classList.remove('processing');
            fileItem.classList.add('error');
            fileItem.querySelector('.file-details').innerHTML = errorMsg;
          }
        }
        else {
          for (const [ai, serverFile, [fileError, ...errorArgs]] of responseList) {
            const [fileItem, _] = uploadFileItems[ai];
            fileItem.classList.remove('processing');

            if (fileError) {
              fileItem.classList.add('error');
              fileItem.querySelector('.file-details').innerHTML = L(fileError, ...errorArgs);
            }
            else {
              fileItem.classList.add('success');
              fileItem.querySelector('.download').href += serverFile.url_name;
              fileItem.querySelector('.file-time').innerHTML = serverFile.time;
              fileItem.querySelector('.file-delete input[name="file"]').value = serverFile.url_name;
              prepareFileDeleteForm(fileItem);
            }
          }
        }
      }

      uploadFiles(); // remaining/delayed files
    },
    true);
}

function fileSizeStr(numBytes)
{
  for (prefix of ['', 'K', 'M']) {
    if (numBytes < 1024) {
      return numBytes + ' ' + prefix + 'B';
    }
    numBytes = numBytes >> 10;
  }
  return numBytes + ' GB';
}

function xhRequestPost(url, data, progressCallback = null, finishedCallback = null, log = true)
{
  let xhr = new XMLHttpRequest();
  if (progressCallback !== null)
    xhr.upload.addEventListener('progress', progressCallback);
  xhr.onreadystatechange = () =>
  {
    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
      if (log) {
        console.log(xhr.responseText);
      }
      if (finishedCallback !== null) {
        finishedCallback(xhr.responseText);
      }
    }
  };
  xhr.open('POST', url, true);
  xhr.send(data);
}

function debounce(timeout, func)
{
  let timer;
  return (...args) =>
  {
    clearTimeout(timer);
    timer = setTimeout(() => { func.apply(this, args); }, timeout);
  };
}

function on(element, event_name, callback)
{
  element.addEventListener(event_name, evt =>
  {
    evt.stopPropagation();
    evt.preventDefault();
    callback(evt);
  });
}

function showMessage(msg)
{
  let div = document.createElement('div');
  div.innerHTML = msg;
  messagesBox.prepend(div);

  setTimeout(() => div.remove(), 10000);
}