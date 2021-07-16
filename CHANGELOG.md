# Changelog
## [Unreleased](https://github.com/akhx/bitrix-optimization/compare/v1.1.1...HEAD)

## [1.1.1](https://github.com/akhx/bitrix-optimization/compare/v1.1.0...v1.1.1) - 2021-07-16
### Исправлено
- Расчёт времени загрузки LazyContent

## [1.1.0](https://github.com/akhx/bitrix-optimization/compare/v1.0.0...v1.1.0) - 2021-07-07
### Добавлено
- Блокировка конвертации по stop_convert=Y
- Сброс ковертации по clear_cache=Y (isAdmin)
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