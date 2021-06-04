# Оптимизация

Набор инструментов, для оптимизации сайта на bitrix

### Установка

```shell
composer require akh/optimization
```

## Оптимизация изображений
скрипт умеет 2 вещи
- конвертация изображений в формат webp
- отложенная загрузка изображений lazyload

для конвертации в webp необходима библиотека [cwebp](https://developers.google.com/speed/webp/docs/cwebp)

#### Использование

```php
\Akh\Optimization\Image::initEvents($option = []);
```

#### Параметры

```php
$option = [
    'debug' => false,
    'lazy_addJs' => false,
    'lazy_addJsBgSupport' => false,
    'lazy_active' => true,
    'lazy_background' => false,
    'lazy_dummy' => 'gif',
    'lazy_exclusions' => [
        'lazyload',
        'skip-lazy',
        'data-no-lazy',
        'data-src',
        'data-srcset',
        'data:image/',
        'data-lazyload',
        'swiper-lazy'
    ],
    'webp_active' => true,
    'webp_checkHref' => false,
    'webp_checkHrefExclusions' => [
        '#',
        '.css',
        '.js',
        '.html',
        '.svg',
        '.php',
        'mailto:',
        'tel:',
        'javascript:'
    ],
    'webp_support' => [
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG
    ],
    'webp_folder' => '/upload/webp'
];
```

## LazyLoad Контент
Отложенная загрузка контента  
контент можно загружать сразу после загрузки страницы или же что более актуально для оптимизации, по мере того, как пользователь начинает приближаться к блоку (позиция скрола).
  
данный функционал полезен, чтобы скрыть изначально со страницы часть контента, к примеру блок «Вы смотрели», «Похожие товары».

Работает на основе `$APPLICATION->IncludeFile()`

#### Использование

```php
\Akh\Optimization\LazyContent::includeFile(
	$fileDefferPath,
	$arParams = [],
	$loadOnScroll = false
);
```
#### Параметры
$arParams может содержать особые параметры

'DULL_FILE_PATH' - путь к файлу заглушки

'DATA_OFFSET_LOAD' - расстояние до начала загрузки контента