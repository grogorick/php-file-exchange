let dragDropIndicator = document.querySelector('#drag-drop-indicator');
let fileInput = document.querySelector('#file-input');
let fileUploadForm = document.querySelector('#file-upload-form');
let fileUploadButton = fileUploadForm.querySelector('input[type="submit"]');
let fileList = document.querySelector('#file-list');
let fileItemTemplate = fileList.querySelector('#file-item-template');

on(document, 'dragover', e =>
{
  e.dataTransfer.dropEffect = 'copy';
  dragDropIndicator.classList.remove('hidden');

  debounce(500, () =>
  {
    dragDropIndicator.classList.add('hidden');
  });
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
  fileInput.value = null;
});

let isUploading = false;
window.onbeforeunload = () =>
{
  if (isUploading) {
    return L('warning_close_while_uploading');
  }
};

function cleanupPreviousUploadItems()
{
  fileUploadButton.classList.add('hidden');
  fileList.querySelectorAll('.item-row.success').forEach(el => el.classList.remove('success'));
  fileList.querySelectorAll('.item-row.prepared, .item-row.error').forEach(el => el.remove());
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
      fileError = L('upload_failed_file_size', fileSizeStr(file.size));
    }
    else {
      let fileExt = file.name.substring(file.name.lastIndexOf('.'));
      if (!allowedFileExtensions.includes(fileExt)) {
        fileError = L('upload_failed_file_type', fileExt);
      }
    }

    let newFileItem = fileItemTemplate.cloneNode(true);
    newFileItem.removeAttribute('id');
    newFileItem.classList.remove('hidden');
    fileList.prepend(newFileItem);

    let newSourceName = newFileItem.querySelector('.file-name');
    newSourceName.innerHTML = file.name;

    let newFileDetails = newFileItem.querySelector('.file-details');
    if (fileError) {
      newFileItem.classList.add('error');
      newFileDetails.innerHTML = fileError;
    }
    else {
      newFileItem.classList.add('prepared');
      newFileDetails.innerHTML = fileSizeStr(file.size);
      approvedFiles.push([i, newFileItem]);
    }
  }
  return approvedFiles.length;
}

function uploadFiles()
{
  if (!approvedFiles.length)
    return;

  isUploading = true;

  let formData = new FormData();
  for (let i = 0; i < approvedFiles.length; ++i) {
    const [si, fileItem] = approvedFiles[i];
    formData.append('files[]', selectedFiles[si]);
    fileItem.classList.remove('prepared');
    fileItem.classList.add('uploading');
  }

  xhRequestPost('./?action=upload', formData, responseText =>
  {
    isUploading = false;

    if (responseText.length) {
      let responseList = JSON.parse(responseText);
      if (responseList[0] === null) {
        const [uploadError, ...errorArgs] = responseList[2];
        let errorMsg = L(uploadError, errorArgs);

        for (const [_, fileItem] of approvedFiles) {
          fileItem.classList.remove('uploading');
          fileItem.classList.add('error');
          fileItem.querySelector('.file-details').innerHTML = errorMsg;
        }
        return;
      }
      for (const [ai, _, [fileError, ...errorArgs]] of responseList) {
        let fileItem = approvedFiles[ai][1];
        fileItem.classList.remove('uploading');

        let fileDetails = fileItem.querySelector('.file-details');
        if (fileError) {
          fileItem.classList.add('error');
          fileDetails.innerHTML = L(fileError, errorArgs);
        }
        else {
          fileItem.classList.add('success');
        }
      }
    }
  }, true);
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

function xhRequestPost(url, data, responseCallback = null, log = true)
{
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = () =>
  {
    if (xhttp.readyState === 4 && xhttp.status === 200) {
      if (log) {
        console.log(xhttp.responseText);
      }
      if (responseCallback !== null) {
        responseCallback(xhttp.responseText);
      }
    }
  };
  xhttp.open('POST', url, true);
  xhttp.send(data);
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