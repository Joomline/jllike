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
            yaButton: '.l-ya',
            pinteresButton: '.l-pinteres',
            count: '.l-count',
            ico: '.l-ico',
            shareTitle: 'h2:eq(0)',
            shareSumary: 'p:eq(0)',
            shareImages: 'img[src]'
        },
        parent: 'div.jllikeproSharesContayner',
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

            $parent = $parent.parents(this.config.parent).parent();


            var
                $tmpParent,
                href = $('input.link-to-share', $parent).val(),
                title = $('input.share-title', $parent).val(),
                image = $('input.share-image', $parent).val(),
                origin = jllickeproSettings.url,
                $title = $(this.config.selectors.shareTitle, $parent),
                $summary = $(this.config.selectors.shareSumary, $parent);

            if(!$title.length){
                $title = $(this.config.selectors.shareTitle, $tmpParent);
            }

            if(!$summary.length){
                $summary = $(this.config.selectors.shareSumary, $tmpParent);
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

            if ($summary.length > 0 & !this.config.forceAlternativeSummary) {
                this.summary = $summary.text();
            } else {
                this.summary = this.config.alternativeSummary ? this.config.alternativeSummary : '';
            }

            this.summary = (this.summary.length > 200) ? this.summary.substring(0,200) + '...' : this.summary;

            this.images = [];

            if(!image.length)
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

        openShareWindow: function (e) {
            var
                button = e.data,
                shareUri = button.getShareLink(),
                windowOptions = button.getPopupOptions();

            var
                newWindow = w.open(shareUri, '', windowOptions);

            button.plusOne();

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
                        if (status == 'success' && data[0]) {
                            if (data[0].share_count > 0) {
                                var elem = $('#'+id);
                                elem.addClass('like-not-empty');
                                $('span.l-count', elem).text(data[0].share_count);
                            }
                        }
                    }
                });
            },

            getCountLink: function (url) {
                return this.countServiceUrl + encodeURIComponent(url) + '%27&format=json';
            },

            getShareLink: function ()
            {
//                var url  = 'https://www.facebook.com/sharer/sharer.php?';
//                url += '&u=' + encodeURIComponent(this.linkToShare);
//                return url;
                var url = 'https://www.facebook.com/sharer/sharer.php?s=100';
                url += '&p[url]=' + encodeURIComponent(this.linkToShare);
                url += '&p[title]=' + encodeURIComponent(this.title);
                url += '&p[images][0]=' + encodeURIComponent(this.images[0]);
                url += '&p[summary]=' + encodeURIComponent(this.summary);
                return url;
            },

            /*@properties*/
            countServiceUrl: 'https://api.facebook.com/method/fql.query?query=select%20total_count,like_count,comment_count,share_count,click_count%20from%20link_stat%20where%20url=%27'
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
                        }
                    }
                });
            },

            getShareLink: function () {
//                return 'https://twitter.com/share'
//                    + '?url=' + encodeURIComponent(this.linkToShare)
//                    + (this.title ? '&text=' + encodeURIComponent(this.title) : '');
                return 'https://twitter.com/intent/tweet'
                    + '?url=' + encodeURIComponent(this.linkToShare)
                    + '&text=' + encodeURIComponent(this.title+ ' ' + this.summary);
            },

            /*@properties*/
            countServiceUrl: 'http://urls.api.twitter.com/1/urls/count.json?url='
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
                    }
                }

                if (!w.VK || !w.VK.Share || !w.VK.Share.count) {
                    w.VK = {
                        Share: {
                            count: function (index, count) {
                                vkShare(index, count);
                            }
                        }
                    }
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
            countServiceUrl: 'http://vkontakte.ru/share.php?act=count&url='
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
                    }
                }

                var serviceURI = 'http://www.odnoklassniki.ru/dk?st.cmd=extLike&uid=' + this.id + '&ref=' + this.linkToShare;

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

            /*@properties*/
            countServiceUrl: 'http://www.odnoklassniki.ru/dk?st.cmd=extLike&uid=' + this.id + '&ref=' + this.linkToShare
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
                return $.post(this.domenhref + '/plugins/content/jllikepro/models/ajax.php', {curl: serviceURI, variant: 'gp', tpget: jllickeproSettings.typeGet}, function (data) {
                    if (data != 0) {
                        var elem = $('#'+id);
                        elem.addClass('like-not-empty');
                        $('span.l-count', elem).text(data);
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
            countServiceUrl: 'http://connect.mail.ru/share_count?callback=1&func=setMailRuCount&url_list='
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
            countServiceUrl: 'http://www.linkedin.com/countserv/count/share?&callback=setLinkedInCount&format=jsonp&url='
        });

    /***LINKIN ***/////


    /***YA ***/////
    var yaButton = function ($context, conf, index) {
        this.init($context, conf, index);
        this.type = 'yaButton';
    };
    yaButton.prototype = new Button;
    yaButton.prototype
        = $.extend(yaButton.prototype,
        {
            /*@methods*/

            countLikes: function ()
            {
                serviceURI = 'http://wow.ya.ru/ajax/share-counter.xml?url=' + this.linkToShare;//encodeURIComponent(this.linkToShare);
                var id = this.id;
                return $.post(this.domenhref + '/plugins/content/jllikepro/models/ajax.php', {curl: serviceURI, variant: 'ya', tpget: jllickeproSettings.typeGet}, function (data) {
                    if (data != 0) {
                        var elem = $('#'+id);
                        elem.addClass('like-not-empty');
                        $('span.l-count', elem).text(data);
                    }
                });

            },

            getShareLink: function () {
                return 'http://my.ya.ru/posts_share_link.xml?url='
                    + this.linkToShare + '&title=' + this.title + '&body=' + this.summary;
            },

            /*@properties*/
            countServiceUrl: 'http://wow.ya.ru/ajax/share-counter.xml?url='
        });

    /***YA ***/////

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
            countServiceUrl: 'http://api.pinterest.com/v1/urls/count.json?callback=setPinteresCount&url='
        });

    /***pinteres ***/////
//+++++++++    


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
                } else if ($element.is(conf.selectors.yaButton)) {
                    b = new yaButton($element, conf, Button.lastIndex);
                }else if ($element.is(conf.selectors.pinteresButton)) {
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