// ==UserScript==
// @name         Prognosis for double game
// @namespace    http://tampermonkey.net/
// @version      0.1
// @description  Prognosis for csgofast.com double game
// @author       Dr1N (drn.exp@gmail.com)
// @match        http://csgofast.com/
// @grant        GM_log
// ==/UserScript==
(function() {
    'use strict';
    GM_log('Start Prognosis');
    var issetContainer = false;
    var prevNumber;
    var container = createContainer();
    setInterval(run, 500);
    function run() {
        if(window.location.href == 'http://csgofast.com/#game/double' || window.location.href == 'https://csgofast.com/#game/double') {
            if (!issetContainer) {
                container.style.display = 'block';
                issetContainer = true;
            }
            var number = getNumber();
            if (number === null || prevNumber == number) {
                return;
            }
            var numContainer = document.getElementById('number');
            var messageContainer = document.getElementById('message');
            if (numContainer) {
                numContainer.innerHTML = number;
            }
            if (messageContainer) {
                messageContainer.innerHTML = 'Запрос прогноза...';
            }
            getPrognosis(number, showResult);
            prevNumber = number;

        } else {
            if (issetContainer) {
                removeContainer();
                issetContainer = false;
            }
        }
    }
    function getNumber() {
        var numSpan = document.getElementById('randNum');
        if (numSpan !== null) {
            var number = parseFloat(numSpan.innerText);
            if (!isNaN(number)) {
                return number;
            }
        }
        return null;
    }
    function getPrognosis(number, callback) {
        var server = 'http://localhost:8800';
        var xhr = new XMLHttpRequest();
        var response = null;
        xhr.open('POST', server, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('number=' + number);
        xhr.onreadystatechange = function() {
            if (this.readyState != 4) return;
            if (this.status != 200) {
                console.error('Error: ' + (this.status ? this.statusText : 'request error'));
                return null;
            }
            response = JSON.parse(xhr.responseText);
            callback(number, response);
        };
    }
    function showResult(number, response) {
        console.log('Show Result');
        console.log(response);
        var messageContainer = document.getElementById('message');
        if (response) {
            if (response.prec100.success == 'false' && response.prec1000.success == 'false') {
                if (messageContainer) {
                    messageContainer.innerHTML = 'Ошибка! (см. консоль)';
                    return;
                }
            }
            setResult(number, response.prec100, 100);
            setResult(number, response.prec1000, 1000);
        } else {
            if (messageContainer) {
                messageContainer.innerHTML = 'Ошибка запроса (см. консоль)';
            }
        }
    }
    function setResult(number, result, prec) {
        console.log('Set Result');
        console.log(result);
        console.log('Prec: ' + prec);
        var messageContainer = document.getElementById('message');
        if (result.success === true) {
            console.log('Success');
            var countContainer = document.getElementById('num-' + prec.toString());
            if (countContainer) {
                var num = (parseInt(number * prec)) / prec;
                countContainer.innerHTML = num + ' (' + result.count + '): ';
            }
            var redContainer = document.getElementById('red-' + prec.toString());
            if (redContainer) {
                redContainer.innerHTML = result.red + ' %';
            }
            var blackContainer = document.getElementById('black-' + prec.toString());
            if (blackContainer) {
                blackContainer.innerHTML = result.black + ' %';
            }
            var greenContainer = document.getElementById('green-' + prec.toString());
            if (greenContainer) {
                greenContainer.innerHTML = result.green + ' %';
            }
            messageContainer.innerHTML = 'Готово';
        } else {
            if (messageContainer) {
                if (result.message !== 'undefined') {
                    messageContainer.innerHTML = '\n' + messageContainer.innerHTML + '\n' + result.message;
                }
            }
        }
    }
    function createContainer() {
        var divContainer = document.createElement('div');
        divContainer.setAttribute('id', 'prog-container');
        divContainer.style.display = 'none';
        divContainer.style.position = 'fixed';
        divContainer.style.top = '80px';
        divContainer.style.right = '20px';
        divContainer.style.backgroundColor = 'cornsilk';
        divContainer.style.width = '380px';
        divContainer.style.height = '220px';
        divContainer.style.borderWidth = '2px';
        divContainer.style.borderColor = 'blueviolet';
        divContainer.style.borderStyle = 'solid';
        divContainer.style.borderRadius = '8px';
        divContainer.style.padding = '5px';
        divContainer.style.zIndex = '999';

        var divNumber = document.createElement('div');
        divNumber.setAttribute('id', 'number');
        divNumber.style.fontWeight = 'bold';
        divNumber.innerHTML = 'Number';
        divContainer.appendChild(divNumber);
        var hr1 = document.createElement('hr');
        divContainer.appendChild(hr1);

        var div100 = createRow('100');
        divContainer.appendChild(div100);
        var hr2 = document.createElement('hr');
        divContainer.appendChild(hr2);

        var div1000 = createRow('1000');
        divContainer.appendChild(div1000);
        var hr3 = document.createElement('hr');
        divContainer.appendChild(hr3);

        var divMessage = document.createElement('div');
        divMessage.setAttribute('id', 'message');
        divMessage.style.wordWrap = 'break-word';
        divMessage.style.overflow = 'auto';
        divMessage.style.height = '50px';
        divMessage.style.minHeight = '50px';
        divContainer.appendChild(divMessage);

        document.body.appendChild(divContainer);
        return divContainer;
    }
    function createRow(prec) {
        var div = document.createElement('div');
        div.setAttribute('id', prec);
        var numSpan = document.createElement('span');
        numSpan.setAttribute('id', 'num-'+ prec);
        numSpan.style.display = 'inline-block';
        numSpan.style.width = '80px';
        numSpan.style.marginRight = '5px';
        numSpan.innerHTML = 'n/a:';
        var redSpan = document.createElement('span');
        redSpan.setAttribute('id', 'red-'+ prec);
        redSpan.style.display = 'inline-block';
        redSpan.style.width = '90px';
        redSpan.style.fontSize = '24px';
        redSpan.style.fontWeight = 'bold';
        redSpan.style.color = 'red';
        redSpan.style.marginRight = '5px';
        redSpan.innerHTML = 'n/a %';
        var blackSpan = document.createElement('span');
        blackSpan.setAttribute('id', 'black-'+ prec);
        blackSpan.style.display = 'inline-block';
        blackSpan.style.width = '90px';
        blackSpan.style.fontSize = '24px';
        blackSpan.style.fontWeight = 'bold';
        blackSpan.style.color = 'black';
        blackSpan.style.marginRight = '5px';
        blackSpan.innerHTML = 'n/a %';
        var greenSpan = document.createElement('span');
        greenSpan.setAttribute('id', 'green-'+ prec);
        greenSpan.style.display = 'inline-block';
        greenSpan.style.width = '90px';
        greenSpan.style.fontSize = '24px';
        greenSpan.style.fontWeight = 'bold';
        greenSpan.style.color = 'green';
        greenSpan.innerHTML = 'n/a %';

        div.appendChild(numSpan);
        div.appendChild(redSpan);
        div.appendChild(blackSpan);
        div.appendChild(greenSpan);

        return div;
    }
    function removeContainer() {
        if(container) {
            container.style.display = 'none';
        }
    }
})();
