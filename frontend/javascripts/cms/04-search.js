(function () {
    var searchIndexProgressBar = document.getElementById('search_index_progress_bar'),
        searchIndexProgressBarProgress,
        searchIndexStatus = document.getElementById('search_index_status'),
        searchIndexLog = document.getElementById('search_index_log'),
        startTime;
    if (searchIndexProgressBar !== null && searchIndexStatus !== null) {
        searchIndexProgressBarProgress = searchIndexProgressBar.getElementsByTagName('div')[0];
        updateSearchIndex();
    }

    function updateSearchIndex() {
        startTime = + new Date();
        updateLog('Indexing start.');
        updateLog('Clearing index.');
        resetIndex();
    }

    function updateLog(message) {
        var currentTime = + new Date(),
            currentDate = new Date(),
            logMessage = addZero(currentDate.getHours()) + ':' + addZero(currentDate.getMinutes()) + ':' + addZero(currentDate.getSeconds()) + ' - ' + message,
            li = document.createElement('li');
            li.innerHTML = logMessage;
        searchIndexStatus.innerText = message;
        searchIndexLog.appendChild(li);
    }

    function addZero(i) {
        if (i < 10) {
            i = "0" + i;
        }
        return i;
    }

    function abortIndexing (responseText, status) {
        updateLog('Error occured.');
        searchIndexStatus.className = 'index-status error';
        updateProgress(0);
        console.log(responseText);
    }

    function httpGetAsync(theUrl, callback, errorCallback) {
        "use strict";
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.onreadystatechange = function () {
            if (xmlHttp.readyState === 4 && xmlHttp.status === 200) {
                callback(xmlHttp.responseText);
            } else if (xmlHttp.readyState === 4 && xmlHttp.status !== 200) {
                errorCallback(xmlHttp.responseText, xmlHttp.status);
            }
        };
        xmlHttp.open("GET", theUrl, true); // true for asynchronous
        xmlHttp.send(null);
    }

    function resetIndex() {
        httpGetAsync(cmsSubfolders + '/search/ajax-update-index?step=resetIndex', function (result) {
            updateProgress(10);
            createDocumentTermCount();
        }, abortIndexing);
    }

    function createDocumentTermCount() {
        updateLog('Creating Document Term Count');
        httpGetAsync(cmsSubfolders + '/search/ajax-update-index?step=createDocumentTermCount', function (result) {
            updateProgress(30);
            createDocumentTermFrequency();
        }, abortIndexing);
    }

    function createDocumentTermFrequency() {
        updateLog('Creating Document Term Frequency');
        httpGetAsync(cmsSubfolders + '/search/ajax-update-index?step=createDocumentTermFrequency', function (result) {
            updateProgress(50);
            createTermFieldLengthNorm();
        }, abortIndexing);
    }

    function createTermFieldLengthNorm() {
        updateLog('Calculating Term Field Length Norm');
        httpGetAsync(cmsSubfolders + '/search/ajax-update-index?step=createTermFieldLengthNorm', function (result) {
            updateProgress(70);
            createInverseDocumentFrequency();
        }, abortIndexing);
    }

    function createInverseDocumentFrequency() {
        updateLog('Creating Inverse Document Frequency');
        httpGetAsync(cmsSubfolders + '/search/ajax-update-index?step=createInverseDocumentFrequency', function (result) {
            updateProgress(90);
            replaceOldIndex();
        }, abortIndexing);
    }

    function replaceOldIndex() {
        updateLog('Replacing old index');
        setTimeout(function (){
            httpGetAsync(cmsSubfolders + '/search/ajax-update-index?step=replaceOldIndex', function (result) {
                updateProgress(100);
                updateLog('Done indexing');
            }, abortIndexing);
        }, 2000);
    }

    function updateProgress(percentage) {
        searchIndexProgressBar.setAttribute('data-progress', percentage);
        searchIndexProgressBarProgress.style.width = percentage + '%';
        if (percentage === 100) {
            searchIndexProgressBar.className = 'progress-bar';
        }
    }
})();