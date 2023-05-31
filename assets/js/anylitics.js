
//let siteID = document.head.querySelector("[property~=bizpresssiteid][content]").content;
let startTime = new Date( Date.now() );
window.addEventListener("beforeunload", function (e) {
    const dataEllement = document.getElementById('bizpress-data');
    const siteID = dataEllement.dataset.siteid;
    if(siteID != "" || siteID != null || siteID != undefined){
        let endTime = new Date( Date.now() );
        const single = dataEllement.dataset.single ? dataEllement.dataset.single : false;
        // http://bizpressanylitics.localhost/
        // https://anylitics.biz.press
            fetch("http://bizpressanylitics.localhost/api/v1/report",{
                method: 'POST',
                headers:{
                    "Content-Type":"application/json",
                    "Accept":"application/json"
                },
                body: JSON.stringify({
                    site_id: siteID,
                    bizpressType: dataEllement.dataset.posttype,
                    ...(single & {
                        resourceTopic: dataEllement.dataset.topics ? dataEllement.dataset.topics : (dataEllement.dataset.types ? dataEllement.dataset.types : null),
                    }),
                    resourceSlug: dataEllement.dataset.slug ? dataEllement.dataset.slug : window.location.pathname,
                    resourceType: single ? 'resource':'page',
                    startTime: startTime.toISOString(),
                    stopTime: endTime.toISOString(),
                    screenHeight: screen.height,
                    screenWidth: screen.width,
                    availHeight: screen.availHeight,
                    availWidth: screen.availWidth,
                    referrer: document.referrer
                })
            })
            .catch(error => {
                //console.log(error);
            });
    }
    else{
        console.log("No Bizpress Site ID Found...");
    }
});