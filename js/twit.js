var twShareObject =
{
    vars: {
        popupWidth: 600,
        popupHeight: 400,
        text: 'Поделиться в Твиттере',
        url: document.location.href
    },

    $shareButton: null,

    openPopup: function (a, url) {
        var width = a.vars.popupWidth;
        var height = a.vars.popupHeight;
        var left = (window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width) / 2 - width / 2 + (void 0 !== window.screenLeft ? window.screenLeft : screen.left);
        var top = (window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height) / 3 - height / 3 + (void 0 !== window.screenTop ? window.screenTop : screen.top);
        a.$shareButton.hide();
        try {
            window.open(
                url,
                "_blank",
                "scrollbars=yes, width=" + width + ", height=" + height + ", top=" + top + ", left=" + left)
                .focus();
        } catch (e) {
        }
    },

    onMouseUp: function (a) {
        var b = a.data.share,
            selectedText = b.getSelectedText();
        if (selectedText.length < 3) {
            b.$shareButton.hide();
            return;
        }
        var coords = b.mouseShowHandler(a);
        a = jQuery(a.delegateTarget);
        b.$shareButton
            .css({position: 'absolute', left: coords.x + 43, top: coords.y - 43})
            .data("url", b.vars.url).show();
    },

    shareClick: function (a) {
        a.preventDefault();
        a = a.data.share;
        var b = a.getSelectedText();
        a.openPopup(a, "https://twitter.com/intent/tweet?url=" + encodeURIComponent(a.$shareButton.data("url")) + "&text=" + encodeURIComponent(b))
        a.$shareButton.hide();
    },

    mouseShowHandler: function (e) {
        e = e || window.event;
        if (e.pageX == null && e.clientX != null) {
            var html = document.documentElement;
            var body = document.body;
            e.pageX = e.clientX + (html && html.scrollLeft || body && body.scrollLeft || 0) - (html.clientLeft || 0);
            e.pageY = e.clientY + (html && html.scrollTop || body && body.scrollTop || 0) - (html.clientTop || 0);
        }
        return {x: e.pageX, y: e.pageY};
    },

    getSelectedText: function () {
        var txt;
        if (window.getSelection)
            txt = window.getSelection().toString();
        else if (document.getSelection)
            txt = document.getSelection();
        else if (document.selection)
            txt = document.selection.createRange().text;
        return txt;
    },

    init: function () {
        var a = this;
        var $body = jQuery('body');
        this.$shareButton = jQuery("<a/>")
            .addClass("share-button-tw")
            .attr({
                id: 'share-button-tw',
                href: document.location.href,
                title: this.vars.text
            })
            .text(this.vars.text)
            .hide()
        ;
        this.$shareButton.appendTo($body);
        $body.on("mouseup", {share: this}, this.onMouseUp);
        this.$shareButton.on("click", {share: this}, this.shareClick);
    }
};

jQuery(function ($) {
    twShareObject.init();
});

