function hello() {
    var url = "http://172.16.223.9/rrd2/index.php";
    var fetchData = {
        method: 'GET',
        mode: 'cors',
        headers: {
        "Accept": "application/json"
        }
    };

    fetch(url,fetchData)
    .then(function(response) {
        console.log(response);
    if (response.ok)
        return response.json();
    else
        throw new Error('Temperatur konnte nicht geladen werden');
    })
    .then(function(json) {
    console.log(json);
        var date = new Date(json.timestamp * 1000);
        document.querySelector("#code").innerHTML = JSON.stringify(json, undefined, 2);
        document.querySelector("#logTemp").innerHTML = ("!!!!!" + (Number(json.temperature ).toFixed(1))).slice(-5);
        document.querySelector("#logHum").innerHTML = ("!!!" + (Number(json.humidity).toFixed(0))).slice(-3);
        document.querySelector("#logTime").innerHTML = ("0" + (date.getHours())).slice(-2)  + ":" + ("0" + (date.getMinutes())).slice(-2);
        document.querySelector("#logDate").innerHTML = ("0" + (date.getDate())).slice(-2) + "." + ("0" + (date.getMonth() + 1)).slice(-2)+ "." + date.getFullYear();
    })
    .catch(function(err) {
    console.log(err);
    });
}

hello();

setInterval(hello, 60 * 1000);
