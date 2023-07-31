const { __ } = wp.i18n;
jQuery(function ($) {
    console.log('Fields JS loaded');
    if ($(".cx-color-picker").length > 0) $(".cx-color-picker").wpColorPicker();
    if ($(".cx-select2").length > 0) $(".cx-select2").select2({ width: "100%" });
    if ($(".cx-chosen").length > 0) $(".cx-chosen").chosen({ width: "100%" });
    if (localStorage.getItem("active_cx_tab") == "undefined" || localStorage.getItem("active_cx_tab") == null || $(localStorage.getItem("active_cx_tab")).length <= 0) {
        localStorage.setItem("active_cx_tab", $(".cx-nav-tab:first-child a").attr("href"));
    }
    if (typeof localStorage != "undefined") {
        active_cx_tab = localStorage.getItem("active_cx_tab");
    }
    if (window.location.hash) {
        active_cx_tab = window.location.hash;
        if (typeof localStorage != "undefined") {
            localStorage.setItem("active_cx_tab", active_cx_tab);
        }
    }
    $(".cx-section").hide();
    $(".cx-nav-tab").removeClass("cx-active-tab");
    $('[href="' + localStorage.getItem("active_cx_tab") + '"]')
        .parent()
        .addClass("cx-active-tab");
    $(localStorage.getItem("active_cx_tab")).show();
    $(".cx-nav-tab").click(function (e) {
        e.preventDefault();
        $(".cx-section").hide();
        $(".cx-nav-tab").css("background", "inherit").removeClass("cx-active-tab");
        $(this).addClass("cx-active-tab").css("background", $(this).data("color"));
        $(".cx-nav-tab a").removeClass("cx-active-tab");
        $(".cx-nav-tab a").each(function (e) {
            $(this).css("color", $(this).parent().data("color"));
        });
        $("a", this).css("color", "#fff");
        var target = $("a", this).attr("href");
        $(target).show();
        localStorage.setItem("active_cx_tab", target);
    });
    $(".cx-createpage").click(function (e) {
        e.preventDefault();
        var button = $(this);
        $(button).text("Creating Page...");
        $(button).attr("disabled", "disabled");

        $.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "JSON",
            data: { 
                action: "cx-createpage",
                post_title: $(this).data("post_title"),
                post_content: $(this).data("post_content"),
                post_type: $(this).data("post_type"),
                post_status: $(this).data("post_status"),
                _wpnonce: $(this).data("nonce")
            },
            success: function (ret) {
                if (ret.status == 1) {
                    $(button).hide();
                    $(button).before("<div class='cx-plugin-install-grid-item-status cx-plugin-install-grid-item-status-green'>"+__('Page Created')+"</div>");
                    $($(button).data("select")).append($('<option>', {
                        value: ret.page_id,
                        text: ret.page.post_title
                    }));
                    //$($(button).data("select")).append("<option value='"+ret.page_id+"'>"+ret.page.post_title+"</option>");
                    $($(button).data("select")).val(ret.page_id);

                }
                else{
                    $(button).before("<div class='cx-plugin-install-grid-item-status cx-plugin-install-grid-item-status-red'>Error: "+ret.message+"</div>");
                }
            },
            error: function (ret) {
                console.log(ret);
                alert("Something went wrong creating the page. Please try again later.");
            }
        });
    });
    $(".cx-plugin-install-grid-item-button").click(function (e) {
        e.preventDefault();
        var button = $(this);
        $(button).text("Installing...");
        $(button).attr("disabled", "disabled");
        $.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "JSON",
            data: { 
                action: "cx-installplugin",
                pluginUrl: $(this).data("url"),
                plugin: $(this).data("plugin"),
                _wpnonce: $(this).data("nonce")
            },
            success: function (ret) {
                if (ret.status == 1) {
                    $(button).hide();
                    $(button).before("<div class='cx-plugin-install-grid-item-status cx-plugin-install-grid-item-status-green'>"+ret.message+"</div>");
                }
                else{
                    $(button).before("<div class='cx-plugin-install-grid-item-status cx-plugin-install-grid-item-status-red'>Error: "+ret.message+"</div>");
                }
            },
            error: function (ret) {
                console.log(ret);
                alert("Something went wrong installing the plugin. Please try again later.");
            }
        });
    });
    $(".cx-form").submit(function (e) {
        e.preventDefault();
        if (typeof tinyMCE != "undefined") tinyMCE.triggerSave();
        var $form = $(this);
        var $submit = $(".cx-submit", $form);
        $submit.attr("disabled", !0);
        $(".cx-message", $form).hide();
        $.ajax({
            url: ajaxurl,
            data: $form.serialize(),
            type: "POST",
            dataType: "JSON",
            success: function (ret) {
                if (ret.status == 1 || ret.status == 0) $(".cx-message", $form).text(ret.message).show().fadeOut(3000);
                $submit.attr("disabled", !1);
                if (ret.page_load == 1)
                    setTimeout(function () {
                        window.location.href = "";
                    }, 1000);
            },
            erorr: function (ret) {
                $submit.attr("disabled", !1);
            },
        });
    });
    $(".cx-reset-button").click(function (e) {
        var $this = $(this);
        var $option_name = $this.data("option_name");
        var $_nonce = $this.data("_nonce");
        $this.attr("disabled", !0);
        $("#cx-message-" + $option_name).hide();
        $.ajax({
            url: ajaxurl,
            data: { action: "cx-reset", option_name: $option_name, _wpnonce: $_nonce },
            type: "POST",
            dataType: "JSON",
            success: function (ret) {
                $("#cx-message-" + $option_name)
                    .text(ret.message)
                    .show();
                setTimeout(function () {
                    window.location.href = "";
                }, 1000);
            },
            erorr: function (ret) {
                $this.attr("disabled", !1);
            },
        });
    });
    $(".cx-browse").on("click", function (event) {
        event.preventDefault();
        var self = $(this);
        var file_frame = (wp.media.frames.file_frame = wp.media({ title: self.data("title"), button: { text: self.data("select-text") }, multiple: !1 }));
        file_frame.on("select", function () {
            attachment = file_frame.state().get("selection").first().toJSON();
            $(".cx-file").val(attachment.url);
            $(".supports-drag-drop").hide();
        });
        file_frame.open();
    });
    $("#cx-submit-top").click(function (e) {
        $(".cx-message").hide();
        $(".cx-form:visible").submit();
    });
    $("#cx-reset-top").click(function (e) {
        $(".cx-form:visible .cx-reset-button").click();
    });
    $('a[href="' + localStorage.active_cx_tab + '"]').click();
});