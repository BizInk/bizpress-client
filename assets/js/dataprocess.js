
let myIPInfo = null;
window.addEventListener("onload", function (e) {
    let ipinfo = localStorage.getItem('bizpress_ipinfo');
    if(ipinfo){
        myIPInfo = JSON.parse(ipinfo);
    }
    else{
        fetch("https://ipinfo.io/json")
        .then(response => response.json())
        .then(data => {
            myIPInfo = data;
            localStorage.setItem('bizpress_ipinfo', JSON.stringify(data));
        })
        .catch(error => {
            console.log(error);
        });
    }
});

//let siteID = document.head.querySelector("[property~=bizpresssiteid][content]").content;
let startTime = new Date( Date.now() );
window.addEventListener("beforeunload", function (e) {
    const dataEllement = document.getElementById('bizpress-data');
    const siteID = dataEllement.dataset.siteid;
    if(siteID != "" || siteID != null || siteID != undefined){
        let endTime = new Date( Date.now() );
        let single = dataEllement.dataset.single ? dataEllement.dataset.single : false;
        if(single == 'false')single = false;
        let resourceType = single ? 'resource':'page';
        if(single == false && dataEllement.dataset.topics){
            resourceType = 'taxonomy';
        }
            let regionData = null;
            if(myIPInfo){
                regionData = {
                    city: myIPInfo.city,
                    region: myIPInfo.region,
                    country: myIPInfo.country.toLowerCase(),
                };
            }
            fetch("https://analytics.biz.press/api/v1/report",{
                method: 'POST',
                headers:{
                    "Content-Type":"application/json",
                    "Accept":"application/json"
                },
                body: JSON.stringify({
                    site_id: siteID,
                    bizpressType: dataEllement.dataset.posttype,
                    resourceTopic: dataEllement.dataset.topics ? dataEllement.dataset.topics : null,
                    resourceSlug: dataEllement.dataset.slug ? dataEllement.dataset.slug : window.location.pathname,
                    resourceType,
                    startTime: startTime.toISOString(),
                    stopTime: endTime.toISOString(),
                    screenHeight: screen.height,
                    screenWidth: screen.width,
                    availHeight: screen.availHeight,
                    availWidth: screen.availWidth,
                    referrer: document.referrer,
                    ...(regionData & { ...regionData })
                })
            })
            .catch(error => {
                console.log(error);
            });
    }
    else{
        console.log("No Bizpress Site ID Found...");
    }
});