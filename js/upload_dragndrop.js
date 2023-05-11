const dragDropIndicator = document.querySelector('#drag-drop-indicator');

const startTimeoutToCancelDragDrop = debounce(200, () =>
{
  dragDropIndicator.classList.add('hidden');
});

on(document, 'dragover', e =>
{
  e.dataTransfer.dropEffect = 'copy';
  dragDropIndicator.classList.remove('hidden');
  startTimeoutToCancelDragDrop();
});

on(document, 'drop', async e =>
{
  dragDropIndicator.classList.add('hidden');
  cleanupPreviousUploadItems();

  let fileList = [];
  const supportsFileSystemAccessAPI = 'getAsFileSystemHandle' in DataTransferItem.prototype;
  const supportsWebkitGetAsEntry = 'webkitGetAsEntry' in DataTransferItem.prototype;
  if (supportsFileSystemAccessAPI || supportsWebkitGetAsEntry) {
    const fileHandlesPromises = [...e.dataTransfer.items]
      .filter(item => item.kind === 'file')
      .map(item =>
        supportsFileSystemAccessAPI
          ? item.getAsFileSystemHandle()
          : item.webkitGetAsEntry(),
      );
    for await (const fileHandle of fileHandlesPromises) {
      if (fileHandle.kind === 'file' || fileHandle.isFile)
        fileList.push(await fileHandle.getFile());
      else
        console.log('Skip directory:', fileHandle.name);
    }
  }
  else
    fileList = e.dataTransfer.files;

  if (prepareFilesForUpload(fileList))
    uploadFiles();
});