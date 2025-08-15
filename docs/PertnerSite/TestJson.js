/*
console.log("Попытка загрузить jquery");
include("jquery-3.7.1.js");
if (window.jQuery) 
{
    var vJq = jQuery.fn.jquery;
    console.log(vJq);
    console.log("Действия для подключённого jQuery");
    }
     else {
      console.log("Действия для неподключённого jQuery");
    }
*/
console.log("Попытка загрузить json");
fetch("c:/Region/PertnerSite/user.json")
.then(response => {
  if (!response.ok) {
    throw new Error('Ой, ошибка в fetch: ' + response.statusText);
  }
  return response.json();
})
.then(jsonData => console.log(jsonData))
.catch(error => console.error('Ошибка при исполнении запроса: ', error));
