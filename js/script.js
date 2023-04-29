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
  prepareFilesForUpload(e.dataTransfer.files);
  uploadFiles();
});

fileUploadButton.classList.add('hidden');
on(fileInput, 'change', e =>
{
  prepareFilesForUpload(fileInput.files);
  fileUploadButton.classList.remove('hidden');
});

on(fileUploadForm, 'submit', e =>
{
  uploadFiles();
});

let selectedFiles = [];
let approvedFiles = [];
function prepareFilesForUpload(files)
{
  console.log('(PREPARE)');
  selectedFiles = files;
  approvedFiles = [];

  for (let i = 0; i < files.length; ++i) {
    let file = files[i];
    console.log(file);

    let fileError = false;
    let fileExt = file.name.substring(file.name.lastIndexOf('.'));
    if (!allowedFileExtensions.includes(fileExt)) {
      fileError = L('upload_failed_file_type', fileExt);
    }
    else if (file.size > maxFileSize) {
      fileError = L('upload_failed_file_size', fileSizeStr(file.size));
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
      console.log(file.name, fileError);
    }
    else {
      newFileItem.classList.add('pending');
      newFileDetails.innerHTML = fileSizeStr(file.size);
      approvedFiles.push([i, newFileItem]);
      console.log(file.name, 'APPROVED FOR UPLOAD');
    }
  }
}

function uploadFiles()
{
  console.log('(UPLOAD)');
  let formData = new FormData();
  for (let i = 0; i < approvedFiles.length; ++i) {
    let si = approvedFiles[i][0];
    formData.append('files[]', selectedFiles[si]);
    console.log(selectedFiles[si].name, 'QUEUED FOR UPLOAD');
  }

  xhRequestPost('./?action=upload', formData, responseText =>
  {
    console.log('(RESPONSE)');
    if (responseText.length) {
      let responseList = JSON.parse(responseText);
      for (const [ai, fileName, fileError] of responseList) {
        console.log(fileName, fileError);
        let newFileItem = approvedFiles[ai][1];
        newFileItem.classList.remove('pending');

        let newFileDetails = newFileItem.querySelector('.file-details');
        if (fileError) {
          newFileItem.classList.add('error');
          newFileDetails.innerHTML = fileError;
        }
        else {
          newFileItem.classList.add('success');
        }
      }
    }
  }, true);

  fileInput.value = null;
  fileUploadButton.classList.add('hidden');
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