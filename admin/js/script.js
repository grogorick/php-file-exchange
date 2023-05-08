function prepareDirectorySelector(input)
{
  const checkDir = debounce(2000, path =>
  {
    xhRequestGet('./?action-json=check&dir=' + toUrl(path), responseText =>
    {
      const dirError = JSON.parse(responseText);
      const statusTag = input.parentNode.querySelector('.dir-check')
      if (dirError === true) {
        statusTag.innerHTML = '&check;';
      }
      else {
        statusTag.innerHTML = L(...dirError);
      }
    });
  });

  input.addEventListener('input', e =>
  {
    const path = input.value;
    checkDir(path);
  });
}
document.querySelectorAll('input[name="dir"]').forEach(prepareDirectorySelector);

function xhRequestGet(url, finishedCallback = null, log = true)
{
  const xhr = new XMLHttpRequest();
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
  xhr.open('GET', url, true);
  xhr.send();
}

function toUrl(str)
{
  return encodeURIComponent(btoa(str));
}