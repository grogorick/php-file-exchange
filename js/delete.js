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
  const formData = new FormData();
  const deleteFileItems = [];
  for (const fileItem of fileItems) {
    const fileName = fileItem.querySelector('.file-delete input[name="file"]').value;
    formData.append('files[]', fileName);
    deleteFileItems.push([fileItem, fileName]);
    fileItem.classList.add('processing');
  }
  xhRequestPost('./?action=delete', formData, null,
    responseText =>
    {
      if (responseText.length) {
        const responseList = JSON.parse(responseText);
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