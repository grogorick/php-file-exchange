const ls_buffer = {};

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
    if (path.length > 0 && path.substr(-1) === '/') {
      if (path in ls_buffer) {
        update_dir_datalist(input, input.nextElementSibling, path, ls_buffer[path]);
      }
      else
        xhRequestGet('./?action-json=ls&dir=' + toUrl(path), responseText =>
        {
          const responseList = JSON.parse(responseText);
          if (responseList === false)
            return;
          ls_buffer[path] = responseList;
          update_dir_datalist(input, input.nextElementSibling, path, responseList);
        });
    }
    checkDir(path);
  });
}
document.querySelectorAll('input[name="dir"]').forEach(prepareDirectorySelector);

function update_dir_datalist(input, datalist, path, ls)
{
  datalist.innerHTML = '';
  ls.forEach(entry =>
  {
    const opt = document.createElement('option');
    opt.value = path + entry;
    datalist.appendChild(opt);
  });
  input.focus();
}

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