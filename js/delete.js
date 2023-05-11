fileList.querySelectorAll('.row.item').forEach(prepareFileDeleteForm);

function prepareFileDeleteForm(fileItem)
{
  on(fileItem.querySelector('.file-delete form'), 'submit', () =>
  {
    if (confirm(L('sure_to_delete_file', fileItem.querySelector('.file-name').getAttribute('data-value'))))
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
    deleteFileItems.push(fileItem);
    fileItem.classList.add('processing');
  }

  const url = new URL(location);
  url.searchParams.set('action', 'delete');
  xhRequestPost(url.href, formData, null,
    responseText =>
    {
      if (responseText.length) {
        const responseList = JSON.parse(responseText);
        let deleteFilesSize = 0;
        for (const [di, serverFile, [fileError, ...errorArgs]] of responseList) {
          const fileItem = deleteFileItems[di];
          fileItem.classList.remove('processing');

          if (fileError) {
            fileItem.classList.add('error');
            fileItem.querySelector('.file-details').innerHTML = L(fileError, ...errorArgs);
          }
          else {
            const fileSizeTag = fileItem.querySelector('.file-size');
            deleteFilesSize += parseInt(fileSizeTag.getAttribute('data-value'));
            fileItem.remove();
            showMessage(L('file_deleted') + ' `' + serverFile.name + '`');
          }
        }
        updateUsedDiskSpace(-deleteFilesSize);
      }
    },
    true);
}