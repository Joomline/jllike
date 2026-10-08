// JL Like Social Buttons - Vanilla JS version
// Полный отказ от jQuery

var socialButtonCountObjects = {};
var jllikeproShareUrls = {
    mail: {},
    pinteres: {},
    linkedin: {},
    vkontakte: {}
};

var ButtonConfiguration = function (params) {
    if (params) {
        // Глубокое копирование объекта
        return Object.assign({}, ButtonConfiguration.defaults, params);
    }
    return ButtonConfiguration.defaults;
}

ButtonConfiguration.defaults = {
    selectors: {
        facebookButton: '.l-fb',
        twitterButton: '.l-tw',
        vkontakteButton: '.l-vk',
        odnoklassnikiButton: '.l-ok',
        mailButton: '.l-ml',
        linButton: '.l-ln',
        pinteresButton: '.l-pinteres',
        LivejournalButton: '.l-lj',
        BloggerButton: '.l-bl',
        WeiboButton: '.l-wb',
        TelegramButton: '.l-tl',
        WhatsappButton: '.l-wa',
        ViberButton: '.l-vi',
        MaxButton: '.l-mx',
        count: '.l-count',
        ico: '.l-ico',
        shareTitle: 'h2',
        shareSumary: 'p',
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
        'height=400',
        'personalbar=0',
        'toolbar=0',
        'scrollbars=1',
        'resizable=1'
    ]
};

function cropText(text, max) {
    if (text.length > max) {
        return text.substring(0, max);
    }
    return text;
}

function updateAllCounter(container) {
    var counts = container.querySelectorAll('.l-count:not(.l-all-count)');
    var sum = 0;
    counts.forEach(function (el) {
        var val = parseInt(el.textContent, 10);
        if (!isNaN(val)) sum += val;
    });
    var allCount = container.querySelector('.l-all-count');
    if (allCount) {
        allCount.textContent = sum;
    }
}

function Button(config, context, index) {
    this.config = config;
    this.index = index;
    this.context = context;
    this.id = context.getAttribute('id');
    this.countElem = context.querySelector(this.config.selectors.count);
    this.icoElem = context.querySelector(this.config.selectors.ico);
    this.collectShareInfo();
    this.bindEvents();
    this.ajaxRequest = this.countLikes();
}

Button.prototype = {
    bindEvents: function () {
        // Обработка клика по всей кнопке
        this.context.addEventListener('click', this.openShareWindow.bind(this));
    },
    setCountValue: function (count) {
        this.context.classList.add('like-not-empty');
        if (this.countElem) {
            this.countElem.textContent = count;
        }
        // Обновить общий счетчик
        var container = this.context.closest('.jllikeproSharesContayner');
        if (container) {
            updateAllCounter(container);
            // Обновить ширину кнопки "Ещё" после изменения счетчика
            updateMoreButtonWidth(container);
        }
    },
    getPopupOptions: function () {
        return 'left=0,top=0,width=500,height=400,personalbar=0,toolbar=0,scrollbars=1,resizable=1';
    },
    plusOne: function () {
        var counter = this.countElem;
        var count = counter ? counter.textContent : '';
        count = (count === '') ? 0 : parseInt(count);
        this.context.classList.add('like-not-empty');
        if (counter) counter.textContent = count + 1;
    },
    isAlreadyLiked: function () {
        var parent = this.context.closest('.jllikeproSharesContayner');
        return !!(window.jllickeproSettings && jllickeproSettings.disableMoreLikes &&
            parent && parent.querySelector('.disable_more_likes'));
    },
    disableMoreLikes: function () {
        if (window.jllickeproSettings && jllickeproSettings.disableMoreLikes) {
            var parent = this.context.closest('.jllikeproSharesContayner');
            if (!parent || parent.querySelector('.disable_more_likes')) return;
            var id = (parent.querySelector('.share-id') || {}).value || '';
            var date = new Date(new Date().getTime() + 60 * 60 * 24 * 30 * 1000);
            document.cookie = 'jllikepro_article_' + encodeURIComponent(id) + '=1; path=/; expires=' + date.toUTCString() + '; SameSite=Lax';
            var div = document.createElement('div');
            div.className = 'disable_more_likes';
            parent.prepend(div);
        }
    },
    openShareWindow: function (e) {
        e.preventDefault();
        var shareUri = this.getShareLink();
        var windowOptions = this.getPopupOptions();
        var newWindow = window.open('', '_blank', windowOptions);
        if (newWindow) {
            // Окно соцсети не должно иметь доступа к странице сайта (reverse tabnabbing)
            newWindow.opener = null;
            newWindow.location.href = shareUri;
        }
        // При включённой опции «запретить повторные лайки» счётчик растёт только один раз
        if (!this.isAlreadyLiked()) {
            this.plusOne();
            updateAllCounter(this.context.closest('.jllikeproSharesContayner') || document);
        }
        this.disableMoreLikes();
        if (window.focus && newWindow) {
            newWindow.focus();
        }
    },
    collectShareInfo: function () {
        var parent = this.context;
        var parentContayner = (window.jllickeproSettings && jllickeproSettings.parentContayner) || '.jllikeproSharesContayner';
        var parentElem = null;
        try {
            parentElem = parent.closest(parentContayner);
        } catch (err) {
            // Некорректный селектор в настройках плагина не должен ломать кнопки
        }
        parentElem = parentElem || parent.closest('.jllikeproSharesContayner');
        var href = parentElem ? (parentElem.querySelector('input.link-to-share') || {}).value : '';
        var title = parentElem ? (parentElem.querySelector('input.share-title') || {}).value : '';
        var image = parentElem ? (parentElem.querySelector('input.share-image') || {}).value : '';
        var $title = parentElem ? parentElem.querySelector('h2') : null;
        var $summary = parentElem ? parentElem.querySelector('input.share-desc') : null;
        if (!$title) $title = document.querySelector('h2');
        var summary = $summary ? $summary.value : '';
        if (!summary) {
            var sumEl = parentElem ? parentElem.querySelector('p') : null;
            summary = sumEl ? sumEl.textContent : '';
        }
        if (!summary) summary = parentElem ? parentElem.textContent : '';
        if (!summary && parentElem && parentElem.parentElement) summary = parentElem.parentElement.textContent;
        this.domenhref = window.location.protocol + '//' + window.location.host;
        this.linkhref = (window.jllickeproSettings ? jllickeproSettings.url : '') + window.location.pathname + window.location.search;
        this.linkToShare = (!href) ? this.linkhref : href;
        this.title = title !== '' ? title : ($title ? $title.textContent : document.title);
        this.summary = summary.length > 0 ? summary : '';
        this.summary = (this.summary.length > 200) ? cropText(this.summary, 200) + '...' : this.summary;
        this.images = [];
        if (typeof image === 'undefined' || !image.length) {
            var images = parentElem ? parentElem.querySelectorAll('img[src]') : [];
            if (images.length > 0) {
                images.forEach(function (img, index) {
                    this.images[index] = img.src;
                }, this);
            }
        } else {
            this.images[0] = image;
        }
    },
    // Заглушки для наследников
    countLikes: function () {},
    getShareLink: function () { return '#'; }
};

// VK Button (пример)
function VkontakteButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'VkontakteButton';
    this.countServiceUrl = 'https://vk.com/share.php?act=count&index=';
}
VkontakteButton.prototype = Object.create(Button.prototype);
VkontakteButton.prototype.constructor = VkontakteButton;
VkontakteButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
VkontakteButton.prototype.getShareLink = function () {
    return 'https://vk.com/share.php?url=' + encodeURIComponent(this.linkToShare) + '&title=' + encodeURIComponent(this.title) + '&description=' + encodeURIComponent(this.summary) + (this.images[0] ? '&image=' + encodeURIComponent(this.images[0]) : '');
};

// --- Facebook Button (Vanilla JS) ---
function FacebookButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'FacebookButton';
    this.countServiceUrl = 'https://graph.facebook.com/?id=';
}
FacebookButton.prototype = Object.create(Button.prototype);
FacebookButton.prototype.constructor = FacebookButton;
FacebookButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
FacebookButton.prototype.getShareLink = function () {
    return 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(this.linkToShare) + '&t=' + encodeURIComponent(this.title);
};

// --- Odnoklassniki Button ---
function OdnoklassnikiButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'OdnoklassnikiButton';
}
OdnoklassnikiButton.prototype = Object.create(Button.prototype);
OdnoklassnikiButton.prototype.constructor = OdnoklassnikiButton;
OdnoklassnikiButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
OdnoklassnikiButton.prototype.getShareLink = function () {
    return 'https://connect.ok.ru/offer?url=' + encodeURIComponent(this.linkToShare) + '&description=' + encodeURIComponent(this.summary);
};

// --- LinkedIn Button ---
function LinkedInButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'LinkedInButton';
}
LinkedInButton.prototype = Object.create(Button.prototype);
LinkedInButton.prototype.constructor = LinkedInButton;
LinkedInButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
LinkedInButton.prototype.getShareLink = function () {
    return 'https://www.linkedin.com/shareArticle?mini=true&url=' + encodeURIComponent(this.linkToShare) +
        '&title=' + encodeURIComponent(this.title) +
        '&summary=' + encodeURIComponent(this.summary);
};

// --- Pinterest Button ---
function PinterestButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'PinterestButton';
}
PinterestButton.prototype = Object.create(Button.prototype);
PinterestButton.prototype.constructor = PinterestButton;
PinterestButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
PinterestButton.prototype.getShareLink = function () {
    var media = (this.images[0] != undefined) ? this.images[0] : '';
    return 'https://www.pinterest.com/pin/create/button/?url=' + encodeURIComponent(this.linkToShare) +
        '&media=' + encodeURIComponent(media) +
        '&description=' + encodeURIComponent(this.summary);
};

// --- LiveJournal Button ---
function LivejournalButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'LivejournalButton';
}
LivejournalButton.prototype = Object.create(Button.prototype);
LivejournalButton.prototype.constructor = LivejournalButton;
LivejournalButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
LivejournalButton.prototype.getShareLink = function () {
    return 'https://www.livejournal.com/update.bml?subject=' + encodeURIComponent(this.title) +
        '&event=' + encodeURIComponent('<a href="' + this.linkToShare + '">' + this.title + '</a> ' + this.summary);
};

// --- Blogger Button ---
function BloggerButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'BloggerButton';
}
BloggerButton.prototype = Object.create(Button.prototype);
BloggerButton.prototype.constructor = BloggerButton;
BloggerButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
BloggerButton.prototype.getShareLink = function () {
    return 'https://www.blogger.com/blog-this.g?u=' + encodeURIComponent(this.linkToShare) +
        '&n=' + encodeURIComponent(this.title);
};

// --- Weibo Button ---
function WeiboButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'WeiboButton';
}
WeiboButton.prototype = Object.create(Button.prototype);
WeiboButton.prototype.constructor = WeiboButton;
WeiboButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
WeiboButton.prototype.getShareLink = function () {
    return 'https://service.weibo.com/share/share.php?url=' + encodeURIComponent(this.linkToShare) + '&title=' + encodeURIComponent(this.title);
};

// --- Telegram Button ---
function TelegramButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'TelegramButton';
}
TelegramButton.prototype = Object.create(Button.prototype);
TelegramButton.prototype.constructor = TelegramButton;
TelegramButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
TelegramButton.prototype.getShareLink = function () {
    return 'https://t.me/share/url?url=' + encodeURIComponent(this.linkToShare) + '&text=' + encodeURIComponent(this.title);
};

// --- Whatsapp Button ---
function WhatsappButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'WhatsappButton';
}
WhatsappButton.prototype = Object.create(Button.prototype);
WhatsappButton.prototype.constructor = WhatsappButton;
WhatsappButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
WhatsappButton.prototype.getShareLink = function () {
    return 'https://wa.me/?text=' + encodeURIComponent(this.title + ' ' + this.linkToShare);
};

// --- Viber Button ---
function ViberButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'ViberButton';
}
ViberButton.prototype = Object.create(Button.prototype);
ViberButton.prototype.constructor = ViberButton;
ViberButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
ViberButton.prototype.getShareLink = function () {
    return 'viber://forward?text=' + encodeURIComponent(this.title + ' ' + this.linkToShare);
};

// --- Threads Button ---
function ThreadsButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'ThreadsButton';
}
ThreadsButton.prototype = Object.create(Button.prototype);
ThreadsButton.prototype.constructor = ThreadsButton;
ThreadsButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
ThreadsButton.prototype.getShareLink = function () {
    return 'https://www.threads.net/intent/post?text=' + encodeURIComponent(this.title + ' ' + this.linkToShare);
};

// --- Reddit Button ---
function RedditButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'RedditButton';
}
RedditButton.prototype = Object.create(Button.prototype);
RedditButton.prototype.constructor = RedditButton;
RedditButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
RedditButton.prototype.getShareLink = function () {
    return 'https://reddit.com/submit?url=' + encodeURIComponent(this.linkToShare) + '&title=' + encodeURIComponent(this.title);
};

// --- MAX Button ---
// Официальный диплинк https://max.ru/:share?text=... (dev.max.ru/help/deeplinks):
// параметр только один — text, поэтому ссылку передаём внутри текста
function MaxButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'MaxButton';
}
MaxButton.prototype = Object.create(Button.prototype);
MaxButton.prototype.constructor = MaxButton;
MaxButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
MaxButton.prototype.getShareLink = function () {
    return 'https://max.ru/:share?text=' + encodeURIComponent(this.title + ' ' + this.linkToShare);
};

// --- Универсальная инициализация всех кнопок ---
function jllikeInitButtons() {
    var conf = ButtonConfiguration && ButtonConfiguration.defaults ? ButtonConfiguration.defaults : {};
    var buttonTypes = [
        {selector: '.l-vk', ctor: VkontakteButton},
        {selector: '.l-fb', ctor: FacebookButton},
        {selector: '.l-tw', ctor: TwitterButton},
        {selector: '.l-ok', ctor: OdnoklassnikiButton},
        {selector: '.l-ml', ctor: MailButton},
        {selector: '.l-ln', ctor: LinkedInButton},
        {selector: '.l-pinteres', ctor: PinterestButton},
        {selector: '.l-lj', ctor: LivejournalButton},
        {selector: '.l-bl', ctor: BloggerButton},
        {selector: '.l-wb', ctor: WeiboButton},
        {selector: '.l-tl', ctor: TelegramButton},
        {selector: '.l-wa', ctor: WhatsappButton},
        {selector: '.l-vi', ctor: ViberButton},
        {selector: '.l-th', ctor: ThreadsButton},
        {selector: '.l-rd', ctor: RedditButton},
        {selector: '.l-mx', ctor: MaxButton}
    ];
    buttonTypes.forEach(function (type) {
        var btns = document.querySelectorAll(type.selector);
        btns.forEach(function (button, index) {
            // Защита от повторной инициализации (скрипт может попасть на страницу дважды)
            if (button.getAttribute('data-jllike-init')) return;
            button.setAttribute('data-jllike-init', '1');
            new type.ctor(conf, button, index);
        });
    });

    // Initialize "More" buttons for collapse functionality
    initMoreButtons();
}


// --- Helper function to update "More" button width ---
function updateMoreButtonWidth(container) {
    if (!container) return;
    var moreButton = container.querySelector('.jllike-more-button');
    if (!moreButton) return;

    // Найти первую видимую кнопку с классом .like (не скрытую)
    var firstButton = container.querySelector('.like:not(.jllike-hidden)');
    if (firstButton) {
        // Получить общую ширину кнопки (иконка + счетчик + margins/paddings)
        var buttonWidth = firstButton.offsetWidth;
        if (buttonWidth > 0) {
            moreButton.style.width = buttonWidth + 'px';
            moreButton.style.height = ''; // Сбросить фиксированную высоту
        }
    }
}

// --- Collapse Buttons Functionality ---
function initMoreButtons() {
    var moreButtons = document.querySelectorAll('.jllike-more-button');

    function adjustMoreButtonWidth() {
        moreButtons.forEach(function (btn) {
            var container = btn.closest('.jllikeproSharesContayner');
            updateMoreButtonWidth(container);
        });
    }

    // Установить ширину сразу
    adjustMoreButtonWidth();

    // Повторно установить ширину после загрузки счетчиков (через 600ms)
    setTimeout(adjustMoreButtonWidth, 600);

    moreButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var container = this.closest('.jllikeproSharesContayner');
            if (!container) return;

            var hiddenButtons = container.querySelectorAll('.jllike-hidden');
            hiddenButtons.forEach(function (hidden) {
                hidden.classList.remove('jllike-hidden');
                hidden.classList.add('jllike-visible');
            });

            // Hide the "More" button after revealing all buttons
            this.style.display = 'none';
        });
    });
}

// --- Twitter Button ---
function TwitterButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'TwitterButton';
}
TwitterButton.prototype = Object.create(Button.prototype);
TwitterButton.prototype.constructor = TwitterButton;
TwitterButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
TwitterButton.prototype.getShareLink = function () {
    return 'https://twitter.com/intent/tweet?url=' + encodeURIComponent(this.linkToShare) + '&text=' + encodeURIComponent(this.title);
};

// --- Mail Button ---
function MailButton(config, context, index) {
    Button.call(this, config, context, index);
    this.type = 'MailButton';
}
MailButton.prototype = Object.create(Button.prototype);
MailButton.prototype.constructor = MailButton;
MailButton.prototype.countLikes = function () {
    if (window.jllickeproSettings && jllickeproSettings.enableCounters === false) {
        if (this.countElem) this.countElem.remove();
        return;
    }
    var self = this;
    setTimeout(function () {
        var count = 0;
        if (!window.jllickeproSettings || window.jllickeproSettings.random_likes !== false) {
            count = Math.floor(Math.random() * 100);
        }
        self.setCountValue(count);
    }, 500);
};
MailButton.prototype.getShareLink = function () {
    return 'https://connect.mail.ru/share?url=' + encodeURIComponent(this.linkToShare) +
        '&image_url=' + encodeURIComponent(this.images[0] || '') +
        '&title=' + encodeURIComponent(this.title) +
        '&description=' + encodeURIComponent(this.summary);
};

// Запуск строго в конце файла: все прототипы кнопок к этому моменту уже определены.
// Скрипт может быть подключён после DOMContentLoaded (оптимизаторы JS шаблонов, async/defer),
// тогда событие уже не наступит и кнопки остались бы неактивными
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', jllikeInitButtons);
} else {
    jllikeInitButtons();
}
