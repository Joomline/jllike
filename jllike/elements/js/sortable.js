/**
 * JL Like Sortable - Drag and Drop functionality for social networks order
 *
 * @version 5.3.0
 * @author JoomLine (https://joomline.ru)
 * @copyright (C) 2012-2025 by Joomline
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

document.addEventListener('DOMContentLoaded', function() {
    initSortable();
    initToggleListeners();
});

/**
 * Инициализация drag-and-drop функциональности
 */
function initSortable() {
    var sortableList = document.getElementById('jllike-sortable-list');
    if (!sortableList) return;

    var draggedItem = null;
    var placeholder = null;

    // Создаем placeholder для визуализации позиции
    function createPlaceholder() {
        var el = document.createElement('li');
        el.className = 'jllike-sortable-placeholder';
        return el;
    }

    // Получаем все элементы списка (исключая кнопку "Все")
    function getItems() {
        return sortableList.querySelectorAll('.jllike-sortable-item:not(.jllike-all-counter)');
    }

    // Обновляем номера порядка после перетаскивания
    function updateOrderNumbers() {
        var items = getItems();
        items.forEach(function(item, index) {
            var orderSpan = item.querySelector('.network-order');
            if (orderSpan) {
                orderSpan.textContent = index + 1;
            }
        });
    }

    // Обновляем скрытые поля порядка в форме
    function updateHiddenOrderFields() {
        var items = getItems();
        items.forEach(function(item, index) {
            var orderParam = item.dataset.orderParam;
            var newOrder = index + 1;

            // Находим соответствующее скрытое поле ввода в форме Joomla
            var orderField = document.querySelector('input[name="jform[params][' + orderParam + ']"]');
            if (orderField) {
                orderField.value = newOrder;
                // Триггерим событие change для Joomla
                orderField.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        // Обновляем preview если он доступен
        updatePreviewOrder();
    }

    // Обновляем порядок в preview виджете
    function updatePreviewOrder() {
        var previewButtons = document.getElementById('preview-buttons');
        if (!previewButtons) return;

        var items = getItems();
        var fragment = document.createDocumentFragment();

        items.forEach(function(item) {
            var network = item.dataset.network;
            var isEnabled = item.classList.contains('enabled');

            // Ищем кнопку в preview
            var button = previewButtons.querySelector('.l-' + network);
            if (button && isEnabled) {
                fragment.appendChild(button);
            }
        });

        // Добавляем кнопку "Все" в конец если есть
        var allButton = previewButtons.querySelector('.l-all');
        if (allButton) {
            fragment.appendChild(allButton);
        }

        // Очищаем и пересобираем preview
        while (previewButtons.firstChild) {
            if (!previewButtons.firstChild.classList ||
                (!previewButtons.firstChild.classList.contains('l-all') &&
                 !previewButtons.firstChild.classList.contains('like'))) {
                previewButtons.removeChild(previewButtons.firstChild);
            } else {
                break;
            }
        }

        previewButtons.innerHTML = '';
        previewButtons.appendChild(fragment);
    }

    // Находим ближайший элемент для вставки
    function getClosestItem(y) {
        var items = Array.from(getItems()).filter(function(item) {
            return item !== draggedItem;
        });

        var closest = null;
        var closestOffset = Number.NEGATIVE_INFINITY;

        items.forEach(function(item) {
            var box = item.getBoundingClientRect();
            var offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closestOffset) {
                closestOffset = offset;
                closest = item;
            }
        });

        return closest;
    }

    // Обработчик начала перетаскивания
    sortableList.addEventListener('dragstart', function(e) {
        var item = e.target.closest('.jllike-sortable-item');
        if (!item) return;

        // Запрещаем перетаскивание кнопки "Все"
        if (item.classList.contains('jllike-all-counter')) {
            e.preventDefault();
            return;
        }

        draggedItem = item;
        placeholder = createPlaceholder();

        // Устанавливаем данные для перетаскивания
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', item.outerHTML);

        // Добавляем класс для визуального отображения
        setTimeout(function() {
            item.classList.add('dragging');
        }, 0);
    });

    // Обработчик окончания перетаскивания
    sortableList.addEventListener('dragend', function(e) {
        var item = e.target.closest('.jllike-sortable-item');
        if (!item) return;

        item.classList.remove('dragging');

        // Удаляем placeholder если он есть
        if (placeholder && placeholder.parentNode) {
            placeholder.parentNode.removeChild(placeholder);
        }

        draggedItem = null;
        placeholder = null;

        // Обновляем порядок
        updateOrderNumbers();
        updateHiddenOrderFields();
    });

    // Обработчик перетаскивания над списком
    sortableList.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';

        if (!draggedItem) return;

        var closest = getClosestItem(e.clientY);

        if (closest) {
            sortableList.insertBefore(draggedItem, closest);
        } else {
            sortableList.appendChild(draggedItem);
        }
    });

    // Обработчик входа в зону перетаскивания
    sortableList.addEventListener('dragenter', function(e) {
        e.preventDefault();
    });

    // Обработчик выхода из зоны перетаскивания
    sortableList.addEventListener('dragleave', function(e) {
        // Ничего не делаем при выходе
    });

    // Обработчик drop
    sortableList.addEventListener('drop', function(e) {
        e.preventDefault();
        // Логика уже обработана в dragover
    });

    // Поддержка touch устройств
    initTouchSupport(sortableList);
}

/**
 * Инициализация touch поддержки для мобильных устройств
 */
function initTouchSupport(sortableList) {
    var touchStartY = 0;
    var touchItem = null;
    var touchClone = null;

    sortableList.addEventListener('touchstart', function(e) {
        var item = e.target.closest('.jllike-sortable-item');
        var handle = e.target.closest('.drag-handle');

        if (!item || !handle) return;

        touchItem = item;
        touchStartY = e.touches[0].clientY;

        // Создаем визуальный клон
        touchClone = item.cloneNode(true);
        touchClone.classList.add('touch-dragging');
        touchClone.style.position = 'fixed';
        touchClone.style.zIndex = '9999';
        touchClone.style.width = item.offsetWidth + 'px';
        touchClone.style.left = item.getBoundingClientRect().left + 'px';
        touchClone.style.top = e.touches[0].clientY - item.offsetHeight / 2 + 'px';
        document.body.appendChild(touchClone);

        item.classList.add('dragging');
    }, { passive: true });

    sortableList.addEventListener('touchmove', function(e) {
        if (!touchItem || !touchClone) return;

        e.preventDefault();

        var touchY = e.touches[0].clientY;
        touchClone.style.top = touchY - touchItem.offsetHeight / 2 + 'px';

        // Находим элемент под пальцем
        touchClone.style.display = 'none';
        var elementBelow = document.elementFromPoint(e.touches[0].clientX, touchY);
        touchClone.style.display = '';

        if (!elementBelow) return;

        var targetItem = elementBelow.closest('.jllike-sortable-item');
        if (targetItem && targetItem !== touchItem) {
            var targetRect = targetItem.getBoundingClientRect();
            var targetMiddle = targetRect.top + targetRect.height / 2;

            if (touchY < targetMiddle) {
                sortableList.insertBefore(touchItem, targetItem);
            } else {
                sortableList.insertBefore(touchItem, targetItem.nextSibling);
            }
        }
    }, { passive: false });

    sortableList.addEventListener('touchend', function(e) {
        if (!touchItem) return;

        touchItem.classList.remove('dragging');

        if (touchClone && touchClone.parentNode) {
            touchClone.parentNode.removeChild(touchClone);
        }

        touchItem = null;
        touchClone = null;

        // Обновляем порядок
        var items = sortableList.querySelectorAll('.jllike-sortable-item');
        items.forEach(function(item, index) {
            var orderSpan = item.querySelector('.network-order');
            if (orderSpan) {
                orderSpan.textContent = index + 1;
            }

            // Обновляем скрытые поля
            var orderParam = item.dataset.orderParam;
            var orderField = document.querySelector('input[name="jform[params][' + orderParam + ']"]');
            if (orderField) {
                orderField.value = index + 1;
                orderField.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }, { passive: true });
}

/**
 * Инициализация обработчиков переключателей включения/выключения сетей
 */
function initToggleListeners() {
    var toggles = document.querySelectorAll('.network-enabled-toggle');

    toggles.forEach(function(toggle) {
        toggle.addEventListener('change', function(e) {
            var item = e.target.closest('.jllike-sortable-item');
            var param = e.target.dataset.param;
            var isEnabled = e.target.checked;

            // Обновляем визуальный класс
            if (isEnabled) {
                item.classList.remove('disabled');
                item.classList.add('enabled');
            } else {
                item.classList.remove('enabled');
                item.classList.add('disabled');
            }

            // Находим и обновляем соответствующее radio поле в форме Joomla
            var enabledField = document.querySelector('input[name="jform[params][' + param + ']"][value="' + (isEnabled ? '1' : '0') + '"]');
            if (enabledField) {
                enabledField.checked = true;
                enabledField.dispatchEvent(new Event('change', { bubbles: true }));
            }

            // Обновляем preview
            updatePreviewVisibility(e.target.dataset.network, isEnabled);
        });
    });
}

/**
 * Обновляет видимость кнопки в preview
 */
function updatePreviewVisibility(network, isEnabled) {
    var previewButtons = document.getElementById('preview-buttons');
    if (!previewButtons) return;

    var button = previewButtons.querySelector('.l-' + network);
    if (!button) return;

    if (isEnabled) {
        button.style.display = '';
    } else {
        button.style.display = 'none';
    }
}
