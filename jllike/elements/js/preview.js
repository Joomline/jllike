/**
 * JL Like Preview Widget Controller
 * Обеспечивает превью социальных кнопок в реальном времени
 */

(function() {
    'use strict';

    var PreviewWidget = {
        // Элементы DOM
        container: null,
        previewContent: null,
        previewSample: null,
        previewButtons: null,
        buttonTextElement: null,
        mobileToggle: null,
        
        // Состояние
        isMobileView: false,
        isUpdating: false,
        
        // Настройки для отслеживания
        watchedFields: {
            'jform_params_button_style': 'buttonStyle',
            'jform_params_position_content': 'position',
            'jform_params_btn_border_radius': 'borderRadius',
            'jform_params_btn_dimensions': 'dimensions',
            'jform_params_btn_margin': 'margin',
            'jform_params_font_size': 'fontSize',
            'jform_params_button_text': 'buttonText'
        },
        
        // Инициализация
        init: function() {
            document.addEventListener('DOMContentLoaded', this.onDOMReady.bind(this));
        },
        
        onDOMReady: function() {
            this.findElements();
            if (this.container) {
                this.setupEventListeners();
                this.initializeMobileButton();
                this.updatePreview();
                console.log('JL Like Preview Widget initialized');
            }
        },
        
        // Поиск элементов DOM
        findElements: function() {
            this.container = document.getElementById('jllike-preview-widget');
            this.previewContent = document.getElementById('preview-content');
            this.previewSample = document.getElementById('preview-sample');
            this.previewButtons = document.getElementById('preview-buttons');
            this.buttonTextElement = document.getElementById('preview-button-text');
            this.mobileToggle = document.getElementById('toggle-mobile-preview');
        },
        
        // Инициализация кнопки мобильного вида
        initializeMobileButton: function() {
            if (!this.mobileToggle) return;
            
            var translations = window.JLLikePreviewTranslations || {mobile: 'Mobile', desktop: 'Desktop'};
            
            // Устанавливаем начальное состояние (десктопный режим)
            this.isMobileView = false;
            this.previewContent.classList.remove('mobile-view', 'mobile-preview');
            this.mobileToggle.classList.remove('active');
            this.mobileToggle.innerHTML = '<span class="icon-mobile" aria-hidden="true"></span> ' + translations.mobile;
        },
        
        // Настройка обработчиков событий
        setupEventListeners: function() {
            // Кнопка переключения мобильного вида
            if (this.mobileToggle) {
                this.mobileToggle.addEventListener('click', this.toggleMobileView.bind(this));
            }
            
            // Отслеживание изменений полей формы
            for (var fieldId in this.watchedFields) {
                this.setupFieldWatcher(fieldId);
            }
            
            // Отслеживание изменений провайдеров
            this.setupProviderWatchers();
        },
        
        // Настройка отслеживания конкретного поля
        setupFieldWatcher: function(fieldId) {
            var field = document.getElementById(fieldId);
            if (field) {
                var handler = this.onFieldChange.bind(this, fieldId);
                
                // Разные типы полей требуют разных событий
                if (field.type === 'radio') {
                    // Для radio buttons нужно отслеживать все элементы с таким именем
                    var radioButtons = document.querySelectorAll('input[name="' + field.name + '"]');
                    for (var i = 0; i < radioButtons.length; i++) {
                        radioButtons[i].addEventListener('change', handler);
                    }
                } else if (field.type === 'text' || field.type === 'number') {
                    field.addEventListener('input', handler);
                    field.addEventListener('change', handler);
                } else if (field.tagName === 'TEXTAREA') {
                    field.addEventListener('input', handler);
                    field.addEventListener('change', handler);
                } else {
                    field.addEventListener('change', handler);
                }
            }
        },
        
        // Настройка отслеживания провайдеров (включение/выключение кнопок)
        setupProviderWatchers: function() {
            var providers = [
                'addfacebook', 'addvk', 'addtw', 'addod', 'addmail', 
                'addlin', 'addpi', 'addlj', 'addbl', 'addwb', 
                'addtl', 'addwa', 'addvi', 'addall'
            ];
            
            var self = this;
            providers.forEach(function(provider) {
                var fieldId = 'jform_params_' + provider;
                var field = document.getElementById(fieldId);
                if (field) {
                    var handler = self.onProviderChange.bind(self);
                    
                    if (field.type === 'radio') {
                        var radioButtons = document.querySelectorAll('input[name="' + field.name + '"]');
                        for (var i = 0; i < radioButtons.length; i++) {
                            radioButtons[i].addEventListener('change', handler);
                        }
                    } else {
                        field.addEventListener('change', handler);
                    }
                }
            });
        },
        
        // Обработчик изменения полей
        onFieldChange: function(fieldId, event) {
            if (this.isUpdating) return;
            
            // Небольшая задержка для группировки изменений
            clearTimeout(this.updateTimeout);
            this.updateTimeout = setTimeout(this.updatePreview.bind(this), 100);
        },
        
        // Обработчик изменения провайдеров
        onProviderChange: function(event) {
            if (this.isUpdating) return;
            
            // Для изменений провайдеров нужно перегенерировать кнопки
            clearTimeout(this.regenerateTimeout);
            this.regenerateTimeout = setTimeout(this.regenerateButtons.bind(this), 300);
        },
        
        // Переключение мобильного вида
        toggleMobileView: function() {
            this.isMobileView = !this.isMobileView;
            
            var translations = window.JLLikePreviewTranslations || {mobile: 'Mobile', desktop: 'Desktop'};
            
            if (this.isMobileView) {
                this.previewContent.classList.add('mobile-view');
                this.previewContent.classList.add('mobile-preview');
                this.mobileToggle.classList.add('active');
                this.mobileToggle.innerHTML = '<span class="icon-desktop" aria-hidden="true"></span> ' + translations.desktop;
            } else {
                this.previewContent.classList.remove('mobile-view');
                this.previewContent.classList.remove('mobile-preview');
                this.mobileToggle.classList.remove('active');
                this.mobileToggle.innerHTML = '<span class="icon-mobile" aria-hidden="true"></span> ' + translations.mobile;
            }
            
            this.updatePreview();
        },
        
        // Основное обновление превью
        updatePreview: function() {
            if (!this.previewSample) return;

            this.showUpdating();

            var styles = this.collectCurrentStyles();
            this.applyStylesToPreview(styles);
            this.updateButtonText();
            this.updateButtonPositioning();
            this.updateButtonStyle();

            setTimeout(this.hideUpdating.bind(this), 200);
        },
        
        // Сбор текущих настроек из формы
        collectCurrentStyles: function() {
            var styles = {};
            
            // Радиус границы
            var borderRadius = this.getFieldValue('jform_params_btn_border_radius', '15');
            styles.borderRadius = parseInt(borderRadius) + 'px';
            
            // Размеры кнопок
            var dimensions = this.getFieldValue('jform_params_btn_dimensions', '30');
            styles.dimensions = parseInt(dimensions) + 'px';
            
            // Отступы
            var margin = this.getFieldValue('jform_params_btn_margin', '6');
            styles.margin = parseInt(margin) + 'px';
            
            // Размер шрифта
            var fontSize = this.getFieldValue('jform_params_font_size', '1');
            styles.fontSize = parseFloat(fontSize) + 'rem';
            
            // Позиционирование
            var position = this.getFieldValue('jform_params_position_content', '0');
            styles.position = position;
            
            return styles;
        },
        
        // Применение стилей к превью
        applyStylesToPreview: function(styles) {
            if (!this.previewSample) return;
            
            // Применяем стили через inline CSS для точности
            var links = this.previewSample.querySelectorAll('a');
            var icons = this.previewSample.querySelectorAll('i');
            var spans = this.previewSample.querySelectorAll('span');
            
            // Стили для ссылок (border-radius, margin)
            for (var i = 0; i < links.length; i++) {
                links[i].style.borderRadius = styles.borderRadius;
                links[i].style.marginLeft = styles.margin;
            }
            
            // Стили для иконок (width, height)
            for (var i = 0; i < icons.length; i++) {
                icons[i].style.width = styles.dimensions;
                icons[i].style.height = styles.dimensions;
            }
            
            // Стили для счетчиков (height, line-height, font-size)
            for (var i = 0; i < spans.length; i++) {
                spans[i].style.height = styles.dimensions;
                spans[i].style.lineHeight = styles.dimensions;
                spans[i].style.fontSize = styles.fontSize;
            }
        },
        
        // Обновление текста кнопок
        updateButtonText: function() {
            if (!this.buttonTextElement) return;
            
            var buttonText = this.getFieldValue('jform_params_button_text', '');
            
            if (buttonText.trim()) {
                this.buttonTextElement.textContent = buttonText;
                this.buttonTextElement.style.display = 'block';
            } else {
                this.buttonTextElement.style.display = 'none';
            }
        },
        
        // Обновление позиционирования кнопок
        updateButtonPositioning: function() {
            if (!this.previewButtons) return;
            
            var position = this.getFieldValue('jform_params_position_content', '0');
            
            // Удаляем существующие классы позиционирования
            this.previewButtons.classList.remove('likes-block_left', 'likes-block_right', 'likes-block_center');
            
            // Добавляем нужный класс
            if (position === '1') {
                this.previewButtons.classList.add('likes-block_right');
            } else if (position === '2') {
                this.previewButtons.classList.add('likes-block_center');
            } else {
                this.previewButtons.classList.add('likes-block_left');
            }
        },
        
        // Перегенерация кнопок при изменении провайдеров
        regenerateButtons: function() {
            // Это более сложная операция, требующая AJAX запроса
            // Пока используем упрощенную версию - показываем/скрываем кнопки
            this.updateProviderVisibility();
        },
        
        // Обновление видимости провайдеров
        updateProviderVisibility: function() {
            var providers = [
                {name: 'addfacebook', selector: '.l-fb'},
                {name: 'addvk', selector: '.l-vk'},
                {name: 'addtw', selector: '.l-tw'},
                {name: 'addod', selector: '.l-ok'},
                {name: 'addmail', selector: '.l-ml'},
                {name: 'addlin', selector: '.l-ln'},
                {name: 'addpi', selector: '.l-pinteres'},
                {name: 'addlj', selector: '.l-lj'},
                {name: 'addbl', selector: '.l-bl'},
                {name: 'addwb', selector: '.l-wb'},
                {name: 'addtl', selector: '.l-tl'},
                {name: 'addwa', selector: '.l-wa'},
                {name: 'addvi', selector: '.l-vi'},
                {name: 'addall', selector: '.l-all'}
            ];
            
            var self = this;
            providers.forEach(function(provider) {
                var isEnabled = self.getFieldValue('jform_params_' + provider.name, '1') === '1';
                var button = self.previewSample.querySelector(provider.selector);
                
                if (button) {
                    button.style.display = isEnabled ? 'inline-block' : 'none';
                }
            });
        },
        
        // Обновление стиля кнопок
        updateButtonStyle: function() {
            if (!this.previewSample) return;

            var buttonStyle = this.getFieldValue('jform_params_button_style', 'default');

            // Удаляем все существующие классы стилей
            var styleClasses = ['jllike-style-minimal', 'jllike-style-gradient', 'jllike-style-outlined', 'jllike-style-floating'];
            for (var i = 0; i < styleClasses.length; i++) {
                this.previewSample.classList.remove(styleClasses[i]);
            }

            // Добавляем новый класс стиля, если не default
            if (buttonStyle !== 'default') {
                this.previewSample.classList.add('jllike-style-' + buttonStyle);
            }
        },

        // Получение значения поля формы
        getFieldValue: function(fieldId, defaultValue) {
            var field = document.getElementById(fieldId);
            
            if (!field) {
                // Попробуем найти radio button
                var radioButtons = document.querySelectorAll('input[name="jform[params][' + fieldId.replace('jform_params_', '') + ']"]');
                for (var i = 0; i < radioButtons.length; i++) {
                    if (radioButtons[i].checked) {
                        return radioButtons[i].value;
                    }
                }
                return defaultValue || '';
            }
            
            return field.value || defaultValue || '';
        },
        
        // Показать индикатор обновления
        showUpdating: function() {
            this.isUpdating = true;
            if (this.previewContent) {
                this.previewContent.classList.add('updating');
            }
        },
        
        // Скрыть индикатор обновления
        hideUpdating: function() {
            this.isUpdating = false;
            if (this.previewContent) {
                this.previewContent.classList.remove('updating');
            }
        }
    };

    // Инициализация виджета
    PreviewWidget.init();
    
    // Экспорт для возможного внешнего использования
    window.JLLikePreviewWidget = PreviewWidget;

})(); 