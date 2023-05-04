let fileInput = document.querySelector('#file-input');
let fileInputLabel = document.querySelector('#file-input + label');
let fileUploadForm = document.querySelector('#file-upload-form');
let fileUploadButton = fileUploadForm.querySelector('input[type="submit"]');
let fileList = document.querySelector('#file-list');
let fileItemTemplate = fileList.querySelector('#file-item-template');

fileInput.classList.add('hidden');
fileInputLabel.classList.remove('hidden');
fileUploadButton.classList.add('hidden');

let isUploading = false;
let selectedFiles = [];
let approvedFiles = [];

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

window.onbeforeunload = () =>
{
  if (isUploading) {
    return L('warning_close_while_uploading');
  }
};

function cleanupPreviousUploadItems()
{
  fileUploadButton.classList.add('hidden');
  fileList.querySelectorAll('.row.item.success').forEach(el => el.classList.remove('success'));
  fileList.querySelectorAll('.row.item.prepared, .row.item.error').forEach(el => el.remove());
}

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