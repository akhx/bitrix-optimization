# Changelog
## [Unreleased](https://github.com/akhx/bitrix-optimization/compare/v1.1.5...HEAD)

## [1.1.5](https://github.com/akhx/bitrix-optimization/compare/v1.1.4...v1.1.5) - 2021-10-04
### Добавлено
- Трансляция get параметров в запрос на получение области LazyContent

## [1.1.4](https://github.com/akhx/bitrix-optimization/compare/v1.1.3...v1.1.4) - 2021-09-14
### Исправлено
- Не запускать из под крон

## [1.1.3](https://github.com/akhx/bitrix-optimization/compare/v1.1.2...v1.1.3) - 2021-09-14
### Исправлено
- Мелкие баги

## [1.1.2](https://github.com/akhx/bitrix-optimization/compare/v1.1.1...v1.1.2) - 2021-08-10
### Исправлено
- LazyContent `onScroll` теперь корректно загружается, если сразу виден на странице

## [1.1.1](https://github.com/akhx/bitrix-optimization/compare/v1.1.0...v1.1.1) - 2021-07-16
### Исправлено
- Расчёт времени загрузки LazyContent

## [1.1.0](https://github.com/akhx/bitrix-optimization/compare/v1.0.0...v1.1.0) - 2021-07-07
### Добавлено
- Блокировка конвертации по stop_convert=Y
- Сброс конвертации по clear_cache=Y (isAdmin)
- Для отладки LazyContent можно загружать сразу по include_lazy_content=Y (isAdmin)

## [1.0.0](https://github.com/akhx/bitrix-optimization/compare/v0.2.2...v1.0.0) - 2021-06-29
### Добавлено
- Изменено название пакета Optimization -> BitrixOptimization

## [0.2.2](https://github.com/akhx/bitrix-optimization/compare/v0.2.0...v0.2.2) - 2021-06-07
### Исправлено
- Тихий режим конвертации
- Seo оринетированный lazyload (при возможности сохраняется src изображения для роботов)

## [0.2.0](https://github.com/akhx/bitrix-optimization/compare/v0.1.1...v0.2.0) - 2021-06-04
### Добавлено
- Опциональный обход ссылок href на поиск изображений для конвертации, для включения `webp_checkHref = true`

### Исправлено
- Регистр namespace поставщика