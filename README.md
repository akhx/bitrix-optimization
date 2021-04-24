# Оптимизация

Набор инструментов, для оптимизации сайта на bitrix

### Установка

```shell
composer require akh/optimization
```

## Оптимизация изображений

#### Использование

```php
\akh\Optimization\Image::initEvents($option = []);
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
    'webp_support' => [
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG
    ],
    'webp_folder' => '/upload/webp'
];
```

## LazyLoad Контент

#### Использование

```php
\akh\Optimization\LazyContent::includeFile(
	$fileDefferPath,
	$arParams = [],
	$loadOnScroll = false
);
```
#### Параметры
$arParams может содержать особые параметры

'DULL_FILE_PATH' - путь к файлу заглушки

'DATA_OFFSET_LOAD' - расстояние до начала загрузки контента