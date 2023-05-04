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
  document.querySelector('#messages').prepend(div);

  setTimeout(() => div.remove(), 10000);
}