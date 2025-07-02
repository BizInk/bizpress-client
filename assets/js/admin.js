jQuery(function($){
	$('.admin_button').on('click', function(e){
        e.preventDefault();
        var $this = $(this);
        var action = $this.data('action');
        var data = {
            action: action,
            nonce: BizPressClient.nonce,
        };

        $.post(BizPressClient.ajax_url, data, function(response) {
            if (response.success) {
                alert(response.data.message);
                if (response.data.redirect) {
                    window.location.href = response.data.redirect;
                }
            } else {
                alert(response.data.message);
            }
        });
    }
})