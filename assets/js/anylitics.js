
let siteID = document.head.querySelector("[property~=bizpresssiteid][content]").content;
const startTime = Date.now();
window.addEventListener("beforeunload", function (e) {
    if(siteID != "" || siteID != null || siteID != undefined){

        const dataEllement = document.getElementById('bizpress-data');
        const single = dataEllement.dataset.single ? dataEllement.dataset.single : false;
        const xmlhttp = new XMLHttpRequest();
        xmlhttp.setRequestHeader("Content-Type", "application/json");
        xmlhttp.setRequestHeader("Accept", "application/json");
        xmlhttp.open("POST", "https://anylitics.biz.press/api/v1/report", true);
        xmlhttp.send(JSON.stringify({
            siteID: siteID,
            bizpressType: dataEllement.dataset.posttype,
            ...(single & {
                resourceSlug: dataEllement.dataset.slug,
                resourceTopic: dataEllement.dataset.topics,
                resourceType: dataEllement.dataset.types
            }),
            startTime: startTime,
            stopTime: Date.now(),
            userAgent: window.navigator.userAgent,
            screenHeight: screen.height,
            screenWidth: screen.width,
            availHeight: screen.availHeight,
            availWidth: screen.availWidth,
            referrer: document.referrer
        }));
    }
    else{
        console.log("No Bizpress Site ID Found...");
    }
});