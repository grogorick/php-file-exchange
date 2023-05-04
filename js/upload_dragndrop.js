let dragDropIndicator = document.querySelector('#drag-drop-indicator');

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