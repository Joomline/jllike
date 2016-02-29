var socialButtonCountObjects = {};
var jllikeproShareUrls = {
    mail: {},
    pinteres: {},
    linkedin: {}
};

jQuery.noConflict();
(function ($, w, d, undefined) {

    function getParam(key) {
        if (key) {
            var pairs = top.location.search.replace(/^\?/, '').split('&');

            for (var i in pairs) {
                var current = pairs[i];
                var match = current.match(/([^=]*)=(\w*)/);
                if (match[1] === key) {
                    return decodeURIComponent(match[2]);
                }
            }
        }
        return false;
    }

    var ButtonConfiguration = function (params) {
        if (params) {
            return $.extend(true, ButtonConfiguration.defaults, params)
        }
        return ButtonConfiguration.defaults;
    }

    ButtonConfiguration.defaults = {
        selectors: {
            facebookButton: '.l-fb',
            twitterButton: '.l-tw',
            vkontakteButton: '.l-vk',
            odnoklassnikiButton: '.l-ok',
            gplusButton: '.l-gp',
            mailButton: '.l-ml',
            linButton: '.l-ln',
            pinteresButton: '.l-pinteres',
            count: '.l-count',
            ico: '.l-ico',
            shareTitle: 'h2:eq(0)',
            shareSumary: 'p:eq(0)',
            shareImages: 'img[src]'
        },
        buttonDepth: 2,
        alternativeImage: '',
        alternativeSummary: '',
        alternativeTitle: '',
        forceAlternativeImage: false,
        forceAlternativeSummary: false,
        forceAlternativeTitle: false,

        classes: {
            countVisibleClass: 'like-not-empty'
        },

        keys: {
            shareLinkParam: 'href'
        },

        popupWindowOptions: [
            'left=0',
            'top=0',
            'width=500',
            'height=250',
            'personalbar=0',
            'toolbar=0',
            'scrollbars=1',
            'resizable=1'
        ]
    };

    var Button = function () {
    };
    Button.lastIndex = 0;

    Button.prototype = {
        /*@methods*/
        init: function ($context, conf, index) {
            this.config = conf;
            this.index = index;
            this.id = $($context).attr('id');
            this.$context = $context;
            this.$count = $(this.config.selectors.count, this.$context);
            this.$ico = $(this.config.selectors.ico, this.$context);

            this.collectShareInfo();
            this.bindEvents();
            this.ajaxRequest = this.countLikes();
        },

        bindEvents: function () {
            this
                .$context
                .bind('click', Button.returnFalse);

            this
                .$ico
                .bind('click', this, this.openShareWindow);

        },

        setCountValue: function (count) {
            this
                .$context
                .addClass(this.config.classes.countVisibleClass);

            this
                .$count
                .text(count);
        },

        getCountLink: function (url) {
            return this.countServiceUrl + encodeURIComponent(url);
        },

        collectShareInfo: function () {
            var
                $parent = this.$context,
                button = this;

            $parent = $parent.parents(jllickeproSettings.parentContayner).parent();


            var
                $tmpParent,
                href = $('input.link-to-share', $parent).val(),
                title = $('input.share-title', $parent).val(),
                image = $('input.share-image', $parent).val(),
                origin = jllickeproSettings.url,
                $title = $(this.config.selectors.shareTitle, $parent),
                $summary;



            if(!$title.length){
                $title = $(this.config.selectors.shareTitle, $tmpParent);
            }

            $summary = $('input.share-desc', $parent).val();

            if(!$summary.length){
                $summary = $(this.config.selectors.shareSumary, $parent).text();
            }
            if(!$summary.length){
                $summary = $parent.text();
            }
            if(!$summary.length){
                $summary = $parent.parent().text();
            }
            
            this.domenhref = w.location.protocol + "//" + w.location.host;

            this.linkhref = jllickeproSettings.url + w.location.pathname + w.location.search;

            this.linkToShare = (!href) ? this.linkhref : href;

            //если заголовок в скрытомполе не пуст, то берем его, если пуст, то первый заголовок родителя
            this.title = (title != '') ? title : $title.text();

            if (this.config.forceAlternativeTitle){
                this.title = this.config.alternativeTitle;
            }
            else if (this.title == '' && this.config.alternativeTitle){
                this.title = this.config.alternativeTitle;
            }
            else if(this.title == ''){
                this.title = d.title;
            }

            if ($summary.length > 0)
            {
                this.summary = $summary;
            }
            else
            {
                this.summary = this.config.alternativeSummary ? this.config.alternativeSummary : '';
            }

            this.summary = (this.summary.length > 200) ? cropText(this.summary, 200) + '...' : this.summary;

            this.images = [];

            if(typeof(image) == 'undefined' || !image.length)
            {
                var $images = $(this.config.selectors.shareImages, $parent);

                $tmpParent = $parent;
                if(!$images.length){
                    var $i = 0;
                    while($images.length == 0 && $i < 20){
                        $i++;
                        $tmpParent = $tmpParent.parent();
                        $images = $(this.config.selectors.shareImages, $tmpParent).not('#waitimg');
                    }
                }

                if ($images.length > 0 & !this.config.forceAlternativeImage) {
                    $images.each(function (index, element) {
                        button.images[index] = element.src;
                    });
                    this.images = button.images;
                } else {
                    this.images[0] = this.config.alternativeImage ? this.config.alternativeImage : undefined;
                }
            }
            else
            {
                this.images[0] = image;
            }
        },

        getPopupOptions: function () {
            return this.config.popupWindowOptions.join(',');
        },

        plusOne: function () {
            var parent = $('#'+this.id),
                counter = $('span.l-count', parent),
                count = counter.text();
            count = (count == '') ? 0 : parseInt(count);
            parent.addClass('like-not-empty');
            counter.text(count + 1);
        },

        disableMoreLikes: function () {
            if(jllickeproSettings.disableMoreLikes){
                var parent = $('#'+this.id).parents('.jllikeproSharesContayner');
                var id = parent.children('.share-id').val();
                var date = new Date( new Date().getTime() + 60*60*24*30*1000 );
                document.cookie="jllikepro_article_"+id+"=1; path=/; expires="+date.toUTCString();
                var div = $('<div/>').addClass('disable_more_likes');
                parent.prepend(div);
            }
        },

        openShareWindow: function (e) {
            var
                button = e.data,
                shareUri = button.getShareLink(),
                windowOptions = button.getPopupOptions();

            var
                newWindow = w.open(shareUri, '', windowOptions);

            button.plusOne();
            button.disableMoreLikes();

            if (w.focus) {
                newWindow.focus()
            }
        },

        /*@properties*/
        linkToShare: null,
        title: d.title,
        summary: null,
        images: [],

        countServiceUrl: null,
        $context: null,
        $count: null,
        $ico: null
    };

    Button = $.extend(Button, {
        /*@methods*/
        returnFalse: function (e) {
            return false;
        }

        /*@properties*/

    });

    var cropText = function (text, length) {
        var result = '';
        text
            .split(' ')
            .every( function(item)
            {
                var tmp = $.trim(item);
                if((result.length + tmp.length) <= length)
                {
                    if(tmp != '')
                    {
                        result += ' '+tmp;
                    }
                    return true;
                }
                return false;
            });
        return result;
    };

    var FacebookButton = function ($context, conf, index) {
        this.init($context, conf, index);
        this.type = 'facebook';
    };
    FacebookButton.prototype = new Button;
    FacebookButton.prototype
        = $.extend(FacebookButton.prototype,
        {
            /*@methods*/
            countLikes: function () {
                var serviceURI = this.getCountLink(this.linkToShare);
                var id = this.id;

                return $.ajax({
                        url: serviceURI,
                    dataType: 'jsonp',
                    success: function (data, status, jqXHR) {
                        if (status == 'success' && typeof data.shares != 'undefined') {
                            if (data.shares > 0) {
                                var elem = $('#'+id);
                                elem.addClass('like-not-empty');
                                $('span.l-count', elem).text(data.shares);
                                jllikeproAllCouner(elem);
                            }
                        }
                    }
                });
            },

            getCountLink: function (url) {
                return this.countServiceUrl + encodeURIComponent(url);
            },

            getShareLink: function ()
            {
                var url = 'https://www.facebook.com/sharer/sharer.php?app_id=114545895322903&sdk=joey&u='
                    + encodeURIComponent(this.linkToShare)
                    +'&display=popup&ref=plugin&src=share_button';
                //var url = 'https://www.facebook.com/sharer/sharer.php?s=100';
                //url += '&p[url]=' + encodeURIComponent(this.linkToShare);
                //url += '&p[title]=' + encodeURIComponent(this.title);
                //url += '&p[images][0]=' + encodeURIComponent(this.images[0]);
                //url += '&p[summary]=' + encodeURIComponent(this.summary);
                return url;
            },

            /*@properties*/
            countServiceUrl: 'https://graph.facebook.com/'
        });

    var TwitterButton = function ($context, conf, index) {
        this.init($context, conf, index);
        this.type = 'twitter';
    };
    TwitterButton.prototype = new Button;
    TwitterButton.prototype
        = $.extend(TwitterButton.prototype,
        {
            /*@methods*/
            countLikes: function () {
                var serviceURI = this.getCountLink(this.linkToShare);
                var id = this.id;
                return $.ajax({
                    url: serviceURI,
                    dataType: 'jsonp',
                    success: function (data, status, jqXHR) {
                        if (status == 'success' & data.count > 0) {
                            var elem = $('#'+id);
                            elem.addClass('like-not-empty');
                            $('span.l-count', elem).text(data.count);
                            jllikeproAllCouner(elem);
                        }
                    }
                });
            },

            getShareLink: function () {
                var text = cropText(this.summary,(140 - this.title.length - this.linkToShare.length));
                return 'https://twitter.com/intent/tweet'
                    + '?url=' + encodeURIComponent(this.linkToShare)
                    + '&text=' + encodeURIComponent(this.title+ '. ' + text);
            },

            /*@properties*/
         //   countServiceUrl: 'https://urls.api.twitter.com/1/urls/count.json?url='
        });


    var VkontakteButton = function ($context, conf, index) {
        this.init($context, conf, index);
        this.type = 'vkontakte';
    };
    VkontakteButton.prototype = new Button;
    VkontakteButton.prototype
        = $.extend(VkontakteButton.prototype,
        {
            /*@methods*/
            countLikes: function () {
                w.socialButtonCountObjects[this.index] = this;
                var serviceURI = this.getCountLink(this.linkToShare) + '&index=' + this.index;

                function vkShare(index, count) {
                    if (count > 0) {
                        var id = w.socialButtonCountObjects[index].id;
                        var elem = $('#'+id);
                        elem.addClass('like-not-empty');
                        $('span.l-count', elem).text(count);
                        jllikeproAllCouner(elem);
                    }
                }

                if(typeof w.VK == 'undefined')
                    w.VK = {};
                if(typeof w.VK.Share == 'undefined')
                    w.VK.Share = {};
                if(typeof w.VK.Share.count == 'undefined'){
                    w.VK.Share.count = function (index, count) {
                        vkShare(index, count);
                    };
                } else {
                    var originalVkCount = w.VK.Share.count;

                    w.VK.Share.count = function (index, count) {
                        vkShare(index, count);
                        originalVkCount.call(w.VK.Share, index, count);
                    };
                }

                return $.ajax({
                    url: serviceURI,
                    dataType: 'jsonp'
                });
            },

            getShareLink: function () {
//                return 'http://vkontakte.ru/share.php?'
                return 'http://vk.com/share.php?'
                    + 'url=' + encodeURIComponent(this.linkToShare)
                    + '&title=' + encodeURIComponent(this.title)
                    + '&image=' + encodeURIComponent(this.images[0])
                    + (this.summary ? '&description=' + encodeURIComponent(this.summary) : '');
            },

            /*@properties*/
            countServiceUrl: 'https://vk.com/share.php?act=count&url='
        });


// +++++++++

    /***odnoklassniki ***/////
    var odnoklassnikiButton = function ($context, conf, index) {
        this.init($context, conf, index);
        this.type = 'odnoklassniki';
    };
    odnoklassnikiButton.prototype = new Button;
    odnoklassnikiButton.prototype
        = $.extend(odnoklassnikiButton.prototype,
        {
            /*@methods*/

            countLikes: function ()
            {

                function odklShare(elementId, count)
                {
                    if (count > 0)
                    {
                        var elem = $('#'+elementId);
                        elem.addClass('like-not-empty');
                        $('span.l-count', elem).text(count);
                        jllikeproAllCouner(elem);
                    }
                }

                var serviceURI = this.getCountLink(this.id, this.linkToShare);

                if (!w.ODKL)
                {
                    w.ODKL = {
                        updateCount: function(elementId, count){
                            odklShare(elementId, count);
                        }
                    }
                }
                else
                {
                    var originalOdCount = ODKL.updateCount;

                    ODKL.updateCount = function (elementId, count)
                    {
                        odklShare(elementId, count);
                        originalOdCount(elementId, count);
                    };
                }
                var id = this.id;
                return $.ajax({
                    url: serviceURI,
                    dataType: 'jsonp'
                });
            },

            getShareLink: function () {
                return 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st._surl='
                    + this.linkToShare
                    +'&st.comments=' + encodeURIComponent(this.summary);
            },

            getCountLink: function (id, linkToShare) {
                return this.countServiceUrl + id + '&ref=' + linkToShare;
            },

            /*@properties*/
            countServiceUrl: 'https://connect.ok.ru/dk?st.cmd=extLike&uid='
        });
    /***odnoklassniki ***/////


    /***GOOGLE ***/////
    var gplusButton = function ($context, conf, index) {
        this.init($context, conf, index);
        this.type = 'gplusButton';
    };
    gplusButton.prototype = new Button;
    gplusButton.prototype
        = $.extend(gplusButton.prototype,
        {
            /*@methods*/

            countLikes: function ()
            {
                serviceURI = this.linkToShare;
                var id = this.id;
                return $.post(this.domenhref + '/plugins/content/jllike/models/ajax.php', {curl: serviceURI, variant: 'gp', tpget: jllickeproSettings.typeGet}, function (data) {
                    if (data != 0) {
                        var elem = $('#'+id);
                        elem.addClass('like-not-empty');
                        $('span.l-count', elem).text(data);
                        jllikeproAllCouner(elem);
                    }
                });

            },

            getShareLink: function () {
                return 'https://plus.google.com/share?url=' + encodeURIComponent(this.linkToShare);
            },

            /*@properties*/
            countServiceUrl: 'https://clients6.google.com/rpc?key=AIzaSyCKSbrvQasunBoV16zDH9R33D88CeLr9gQ'
        });

    /***GOOGLE ***/////


    /***MAIL ***/////
    var mailButton = function ($context, conf, index) {
        this.init($context, conf, index);
        this.type = 'mailButton';
    };
    mailButton.prototype = new Button;
    mailButton.prototype
        = $.extend(mailButton.prototype,
        {
            /*@methods*/

            countLikes: function ()
            {
                jllikeproShareUrls.mail[this.linkToShare] = this.id;

                w.setMailRuCount = function(data){
                    for (var url in data) if (data.hasOwnProperty(url))
                    {
                        if(!jllikeproShareUrls.mail.hasOwnProperty(url))
                        {
                            return;
                        }
                        var id = jllikeproShareUrls.mail[url];
                        var shares = data[url].shares;
                        if(shares>0){
                            var elem = $('#'+id);
                            elem.addClass('like-not-empty');
                            $('span.l-count', elem).text(shares);
                            jllikeproAllCouner(elem);
                        }
                    }
                }

                serviceURI = this.getCountLink(this.linkToShare);
                return $.ajax({
                    url: serviceURI,
                    dataType: 'jsonp'
                });
            },

            getShareLink: function () {
                return 'http://connect.mail.ru/share' +
                    '?url='+ encodeURIComponent(this.linkToShare) +
                    '&imageurl' + encodeURIComponent(this.images[0]) +
                    '&title' + encodeURIComponent(this.title) +
                    '&description' + encodeURIComponent(this.summary)
                    ;
            },

            /*@properties*/
            countServiceUrl: 'https://connect.mail.ru/share_count?callback=1&func=setMailRuCount&url_list='
        });

    /***MAIL ***/////


    /***LINKIN ***/////
    var linButton = function ($context, conf, index) {
        this.init($context, conf, index);
        this.type = 'linButton';
    };
    linButton.prototype = new Button;
    linButton.prototype
        = $.extend(linButton.prototype,
        {
            /*@methods*/
            countLikes: function ()
            {
                jllikeproShareUrls.linkedin[this.linkToShare] = this.id;

                w.setLinkedInCount = function(data){

                    if(!jllikeproShareUrls.linkedin.hasOwnProperty(data.url))
                    {
                        return;
                    }

                    var id = jllikeproShareUrls.linkedin[data.url];
                    var shares = data.count;
                    if(shares>0){
                        var elem = $('#'+id);
                        elem.addClass('like-not-empty');
                        $('span.l-count', elem).text(shares);
                        jllikeproAllCouner(elem);
                    }

                }

                var serviceURI = this.getCountLink(this.linkToShare);
                return $.ajax({
                    url: serviceURI,
                    dataType: 'jsonp'
                });
            },

            getShareLink: function () {
                return 'http://www.linkedin.com/shareArticle?mini=true&ro=false&trk=bookmarklet&url='
                    + this.linkToShare;//encodeURIComponent(this.linkToShare);
            },

            /*@properties*/
            countServiceUrl: 'https://www.linkedin.com/countserv/count/share?&callback=setLinkedInCount&format=jsonp&url='
        });

    /***LINKIN ***/////


    /***pinteres ***/////
    var pinteresButton = function ($context, conf, index) {
        this.init($context, conf, index);
        this.type = 'pinteresButton';
    };
    pinteresButton.prototype = new Button;
    pinteresButton.prototype
        = $.extend(pinteresButton.prototype,
        {
            /*@methods*/

            countLikes: function ()
            {
                jllikeproShareUrls.pinteres[this.linkToShare] = this.id;

                w.setPinteresCount = function(data)
                {
                    if (data.hasOwnProperty('count'))
                    {
                        if(!jllikeproShareUrls.pinteres.hasOwnProperty(data.url))
                        {
                            return;
                        }

                        var id = jllikeproShareUrls.pinteres[data.url];
                        if(data.count > 0){
                            var elem = $('#'+id);
                            elem.addClass('like-not-empty');
                            $('span.l-count', elem).text(data.count);
                            jllikeproAllCouner(elem);
                        }
                    }
                }

                serviceURI = this.getCountLink(this.linkToShare);
                return $.ajax({
                    url: serviceURI,
                    dataType: 'jsonp'
                });
            },

            getShareLink: function () {
                var media = (this.images[0] != undefined) ? this.images[0] : '';
                return 'http://www.pinterest.com/pin/create/button/?' +
                    'url=' + encodeURIComponent(this.linkToShare)+
                    '&media=' + media +
                    '&description=' + this.summary;
            },

            /*@properties*/
            countServiceUrl: 'https://api.pinterest.com/v1/urls/count.json?callback=setPinteresCount&url=' 
        });

    /***pinteres ***/////
//+++++++++    

    var jllikeproAllCouner = function(element)
    {
        var parent = $(element).parents('.jllikeproSharesContayner');
        var counterSpan = parent.find('span.l-all-count');
        var counterValue = 0;
        var tmpVal;
        parent.find('.l-count').not('.l-all-count').each(function(){
            tmpVal = $(this).text();
            if(tmpVal != ''){
                counterValue += parseInt(tmpVal);
            }
        });
        counterSpan.text(counterValue);
    };

    $.fn.socialButton = function (config) {

        this.each(function (index, element) {
            setTimeout(function () {
                var
                    $element = $(element),
                    conf = new ButtonConfiguration(config),
                    b = false;

                Button.lastIndex++;

                if ($element.is(conf.selectors.facebookButton)) {
                    b = new FacebookButton($element, conf, Button.lastIndex);
                } else if ($element.is(conf.selectors.twitterButton)) {
                    b = new TwitterButton($element, conf, Button.lastIndex);
                } else if ($element.is(conf.selectors.vkontakteButton)) {
                    b = new VkontakteButton($element, conf, Button.lastIndex);
                } else if ($element.is(conf.selectors.odnoklassnikiButton)) {
                    b = new odnoklassnikiButton($element, conf, Button.lastIndex);
                } else if ($element.is(conf.selectors.gplusButton)) {
                    b = new gplusButton($element, conf, Button.lastIndex);
                } else if ($element.is(conf.selectors.mailButton)) {
                    b = new mailButton($element, conf, Button.lastIndex);
                } else if ($element.is(conf.selectors.linButton)) {
                    b = new linButton($element, conf, Button.lastIndex);
                } else if ($element.is(conf.selectors.pinteresButton)) {
                    b = new pinteresButton($element, conf, Button.lastIndex);
                }

                $
                    .when(b.ajaxRequest)
                    .then(
                    function () {
                        $element.trigger('socialButton.done', [b.type]);
                    }
                    , function () {
                        $element.trigger('socialButton.done', [b.type]);
                    }
                );
            }, 0);
        });

        return this;
    };

    $.scrollToButton = function (hashParam, duration) {
        if (!w.location.hash) {
            if (w.location.search) {
                var currentHash = getParam(hashParam);
                if (currentHash) {
                    var $to = $('#' + currentHash);
                    if ($to.length > 0) {
                        $('html,body')
                            .animate({
                                scrollTop: $to.offset().top,
                                scrollLeft: $to.offset().left
                            }, duration || 1000);
                    }
                }
            }
        }

        return this;
    };

})(jQuery, window, document);

jQuery(document).ready(function ($)
{
    $('.like').socialButton();

    var likes = $('div.jllikeproSharesContayner');
    var contayner = jllickeproSettings.buttonsContayner;
    if(contayner != ''
        && $(contayner).length > 0
        && likes.length > 0
        && jllickeproSettings.isCategory == 0)
    {
        likes.remove();
        $(contayner).html(likes);
    }
});