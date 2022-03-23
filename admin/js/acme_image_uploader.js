jQuery(function ($) {
    //'use strict';
    const plugin_name = 'feedaty-rating-for-woocommerce';

    // IMAGE UPLOADER
    var upl = '.' + plugin_name + '-img-upl',
        rmv = '.' + plugin_name + '-img-rmv';
    // on upload button click
    $('body').on('click', upl, function (e) {
        e.preventDefault();

        var button = $(this), $in = $('#' + $(this).attr('data-target')), $rmv = $(this).parent().find(rmv).first(),
            custom_uploader = wp.media({
                title: 'Insert image',
                library: {
                    // uploadedTo : wp.media.view.settings.post.id, // attach to the current post?
                    type: 'image'
                },
                button: {
                    //text: 'Use this image' // button label text
                },
                multiple: false
            }).on('select', function () { // it also has "open" and "close" events
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                var myUrl;
                //console.log ( { attachment } );
                if (undefined === attachment.sizes.thumbnail) {
                    myUrl = attachment.sizes.full.url;
                } else {
                    myUrl = attachment.sizes.thumbnail.url;
                }
                button.html('<img src="' + myUrl + '">');
                $in.val(attachment.id);
                $rmv.show();
            }).open();

    });

    // on remove button click
    $('body').on('click', rmv, function (e) {

        e.preventDefault();

        var button = $(this), $in = $('#' + $(this).attr('data-target'));
        $in.val(''); // emptying the hidden field
        button.hide().prev().html('Upload image');
    });
    //### END OF IMAGE UPLOADER


});
