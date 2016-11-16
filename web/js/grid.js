gridController = {
    _messages: { // содержит переводы сообщений
        scretchToFill: 'Stretch to fill',
        scretchToArea: 'Stretch to area',
    },
    _menuItems: [], // содержит элементы контекстного меню
    _init: function(options) {
        if (options.messages) {
            this._setTranslations(options.messages);
        }
    },
    // Переопределить переводы
    _setTranslations: function(messages) {
        if (messages.scretchToFill !== undefined && messages.scretchToFill !=  '') {
            this._messages.scretchToFill = messages.scretchToFill;
        }
        if (messages.scretchToArea !== undefined && messages.scretchToArea !=  '') {
            this._messages.scretchToArea = messages.scretchToArea;
        }
    },
    // Получить перевод одного сообщения
    _getTranslation: function(message) {
        if (this._messages[message] !== undefined) {
            return this._messages[message];
        }
        
        return '';
    },
    // Возвращает эдементы меню
    _getMenuItems: function() {
        return this._menuItems;
    },
    // Генерирует элементы меню на основе колонок
    _generateMenuItems: function(grid, columns) {
        var data = columns;
        if (data.length == 0) {
            for (var i = 0; i < grid.config.columns.length; i++) {
                var column = grid.config.columns[i];
                if (column.header[0].text != "_hidden" && column.header[0].text != "") {
                    data.push({ id:column.id, value:column.header[0].text });
                }
            }
            var hidden = grid.getState().hidden;
            for (var i = hidden.length - 1; i >= 0; i--) {
                if (grid.getColumnConfig(hidden[i]).header[0].text != "_hidden" &&
                    grid.getColumnConfig(hidden[i]).header[0].text != "") {
                    data.push({ id:hidden[i], value: grid.getColumnConfig(hidden[i]).header[0].text, hidden:1 });
                }
            }
        }
        
        // Добавить кнопки управления размером грида в контекстное меню
        data.push({ id: 'scretchToFill', value: this._getTranslation('scretchToFill'), selected: false }); // Растянуть по наполнению
        data.push({ id: 'scretchToArea', value: this._getTranslation('scretchToArea'), selected: true }); // Растянуть по области
        
        this._menuItems = data;
    },
    // "Растянуть по наполнению"
    scretchToFill: function(grid, headerMenu, menuItem, tableHeader) {
        // обновить элементы меню
        menuItem.selected = true;
        headerMenu.getItem('scretchToArea').selected = false;
        headerMenu.refresh(menuItem.id);
        headerMenu.refresh('scretchToArea');

        // обновить колонки
        grid.eachColumn(function(columnId) {
            var columnIndex = grid.getColumnIndex(columnId);
            grid.config.columns[columnIndex].fillspace = false;
            grid.config.columns[columnIndex].adjust = 'all';
            grid.adjustColumn(columnId, "all"); // расстянуть колонку по контенту
        });

        /**
         * Если после текущей операци ("Растянуть по наполнению") суммарная ширина колонок меньше, 
         * чем ширина грида, растянуть колонки по всей ширине грида.
         * При расстягивании по всей ширине, колонки получают одиноковую ширину.
         * Если одной из колонок не хватает ширины, её отдельно нужно расстянуть по её контенту.
         * Остальные колонки расстянуть автоматически, что б заполнить ширину грида.
         */
        var headerColumnsContainer = $(tableHeader).children('.webix_hs_center');
        var gridWidth = headerColumnsContainer.width(); // ширина грида
        var columnsWidth = headerColumnsContainer.children('table').width(); // суммарная ширина колонок

        // Ширина грида больше чем суммарная ширина колонок
        if (gridWidth > columnsWidth) {
            var columnsWidth = []; // массив, содержащий width каждой колонки

            var headerColumns = headerColumnsContainer.find('table tr[section=header]');
            grid.eachColumn(function(columnId) {
                var columnIndex = grid.getColumnIndex(columnId);
                columnsWidth[columnIndex] = headerColumns.children('td[column=' + columnIndex + ']').outerWidth(); // получить ширину текущей колонки
            });
            var columnAutoWidth = gridWidth / columnsWidth.length; // ширина каждой колонки, если включить режим "Растянуть по области"
            var maxColumnWidth = Math.max.apply(null, columnsWidth); // ширина самой широков колонки

            grid.eachColumn(function(columnId) {
                var columnIndex = grid.getColumnIndex(columnId);
                if (columnAutoWidth >= columnsWidth[columnIndex]) {
                    grid.config.columns[columnIndex].fillspace = 1;
                    grid.config.columns[columnIndex].adjust = false;
                }
            });
            grid.refreshColumns();
        }
    },
    // "Растянуть по области"
    scretchToArea: function(grid, headerMenu, menuItem) {
        // обновить элементы меню
        menuItem.selected = true;
        headerMenu.getItem('scretchToFill').selected = false;
        headerMenu.refresh(menuItem.id);
        headerMenu.refresh('scretchToFill');

        // обновить колонки
        grid.eachColumn(function(columnId) {
            var columnIndex = grid.getColumnIndex(columnId);
            grid.config.columns[columnIndex].fillspace = 1;
            grid.config.columns[columnIndex].adjust = false;
        });
        grid.refreshColumns();
    },
    // Добавить контекстное меню в таблицу
    addMenu: function(grid, gridId, columns, options) {
        // Предварительная инициализация
        this._init(options);
        // Сгенерировать элементы меню
        this._generateMenuItems(grid, columns);
        
        var controller = this;
        // Получить header таблицы
        var tableHeader = $('[view_id="' + gridId + '"] .webix_ss_header')[0];
        // меню выбора колонок
        var headerMenu = webix.ui({
            view: "contextmenu",
            autoheight: true,
            width: '200',
            template:"<span class='webix_icon {common.hidden()}'></span>#value#",
            type: {
                hidden:function(obj){
                    if (obj.id === 'scretchToFill' || obj.id === 'scretchToArea') {
                        if (obj.selected) {
                            return "fa-circle fa-lg";
                        } else {
                            return "fa-circle-o fa-lg";
                        }
                    }

                    if (obj.hidden) {
                        return "fa-circle-o fa-lg item-unchecked";
                    } else {
                        return "fa-check-circle-o fa-lg item-checked";
                    }
                }
            },
            data: this._getMenuItems()
        });

        /**
         * Обработчик клика по элементу меню
         * id id item ID
         * Event e mouse event object
         * node HTMLElement target HTML element
         */
        headerMenu.attachEvent("onMenuItemClick", function(id, e, node) {
            var menuItem = headerMenu.getItem(id);

            // обработчик "Растянуть по наполнению"
            if (menuItem.id === 'scretchToFill') {
                if (menuItem.selected) {
                    return false;
                }
                controller.scretchToFill(grid, headerMenu, menuItem, tableHeader);

                return false;
            }

            // обработчик "Растянуть по области"
            if (menuItem.id === 'scretchToArea') {
                if (menuItem.selected) {
                    return false;
                }
                controller.scretchToArea(grid, headerMenu, menuItem);

                return false;
            }

            // обработчик отображения\скрытия колонок
            var state = menuItem.hidden;
            menuItem.hidden = !state;
            headerMenu.refresh(menuItem.id);
            headerMenu.$blockRender = true;

            if (state) {
                grid.showColumn(menuItem.id);
            } else {
                grid.hideColumn(menuItem.id);
            }

            headerMenu.$blockRender = false;

            return false;
        });

        headerMenu.attachTo(tableHeader); // Добавить contextmenu к header-у таблицы
    }
};
