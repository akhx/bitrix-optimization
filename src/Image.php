<?php

namespace akh\Optimization;

use Bitrix\Main\EventManager;

/**
 * Class Image
 * @package akh\Optimization
 */
class Image
{
    static $option = [
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

    /**
     * Инициализация Битрикс событий
     * @param $option
     */
    public static function initEvents($option = [])
    {
        static::$option = array_merge(static::$option, $option);

        if (stripos($_SERVER['REQUEST_URI'], '/bitrix/admin/') !== 0) {
            EventManager::getInstance()->addEventHandler(
                'main',
                'OnEndBufferContent',
                [__CLASS__, 'init']
            );
        }
    }

    /**
     * Обработка содержимого
     *
     * @param $content - контент страницы
     */
    public static function init(&$content)
    {
        $start = microtime(true);
        $clearContent = preg_replace('#<(?:no)?script.*?<\/(?:no)?script>#is', '', $content);

        /**
         * Поиск изображений
         */
        if (preg_match_all('#<img[^>]*src[^>]*>#Usmi', $clearContent, $matches)) {
            $toReplace = [];
            foreach ($matches[0] as $tag) {
                $tempTag = $tag;

                if (static::$option['webp_active'] === true) {
                    $arSrc = static::getSrc($tempTag);

                    if (!empty($arSrc)) {
                        foreach ($arSrc as $src) {
                            $endSrc = static::webpConvert($src);
                            $tempTag = str_replace($src, $endSrc, $tempTag);
                        }
                    }
                }

                if (static::$option['lazy_active'] === true) {
                    $tempTag = static::addLazyLoad($tempTag);
                }

                if ($tag != $tempTag) {
                    $toReplace[$tag] = $tempTag;
                }
            }

            $content = str_replace(array_keys($toReplace), array_values($toReplace), $content);
        }

        /**
         * поиск инлайновых стилей background(-image)
         */
        if (preg_match_all('#<[^>]*background(-image)?:.*?url\(\s*(.*?)\s*\)[^>]*>#', $clearContent, $matches)) {
            $toReplace = [];
            foreach ($matches[0] as $k => $tag) {
                $tempTag = $tag;

                if (static::$option['webp_active'] === true) {
                    $endSrc = static::webpConvert($matches[2][$k]);
                    $tempTag = str_replace($matches[2][$k], $endSrc, $tempTag);
                    $matches[2][$k] = $endSrc;
                }

                if (static::$option['lazy_active'] === true && static::$option['lazy_background'] === true) {
                    $tempTag = static::addLazyLoadBg($tempTag, $matches[2][$k]);
                }

                if ($tag != $tempTag) {
                    $toReplace[$tag] = $tempTag;
                }
            }

            $content = str_replace(array_keys($toReplace), array_values($toReplace), $content);
        }

        if (static::$option['lazy_active'] === true) {
            $js = static::getLazyJs(static::$option['lazy_addJs'], static::$option['lazy_addJsBgSupport']);
            if ($js) {
                $content = str_replace('</body>', $js . '</body>', $content);
            }
        }

        if (static::$option['debug'] === true) {
            $time = microtime(true) - $start;
            $content .= 'Время на оптимизацию ' . $time . ' сек.';
        }
    }

    /**
     * Получение путей файла из src
     *
     * @param $tag
     * @return array
     */
    public static function getSrc($tag): array
    {
        $res = [];
        preg_match_all('#(src|srcset|data-src|data-srcset)=(["\'])(.*)(["\'])#Usmi', $tag, $matches);

        if (empty($matches[3])) {
            return $res;
        }

        foreach ($matches[3] as $attr) {
            $arSrc = explode(',', $attr);
            foreach ($arSrc as $src) {
                $src = trim($src);
                if (!empty($src)) {
                    $res[] = explode(' ', $src)[0];
                }
            }
        }

        return array_unique($res);
    }

    /**
     * Конвертация изображения в webp
     *
     * @param string $src
     * @param string $destSrc
     * @return string
     */
    public static function webpConvert(string $src, string $endSrc = ''): string
    {
        if (static::checkWebpSupport() === false) {
            return $src;
        }

        if ($src) {
            $exif = exif_imagetype($_SERVER['DOCUMENT_ROOT'] . $src);

            if (in_array($exif, static::$option['webp_support'])) {
                $clearSrc = str_replace(['.png', '.jpg', '.jpeg'], '', $src);

                if (empty($endSrc)) {
                    $endSrc = static::$option['webp_folder'] . str_replace('/upload/', '/', $clearSrc) . '.webp';
                }

                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $endSrc)) {
                    $convert = true;
                } else {
                    $distPath = explode('/', $_SERVER['DOCUMENT_ROOT'] . $endSrc);
                    unset($distPath[count($distPath) - 1]);
                    $distPath = implode('/', $distPath);
                    mkdir($distPath, 0755, true);

                    $command = 'cwebp -q 80 ' . $_SERVER['DOCUMENT_ROOT'] . $src . ' -o ' . $_SERVER['DOCUMENT_ROOT'] . $endSrc;
                    exec($command, $a, $b);
                    $convert = file_exists($_SERVER['DOCUMENT_ROOT'] . $endSrc);
                }

                if ($convert) {
                    $src = $endSrc;
                }
            }
        }

        return $src;
    }

    /**
     * Проверка поддержки webp браузером
     *
     * @return bool
     */
    public static function checkWebpSupport(): bool
    {
        if ($_REQUEST['webpSupport']) {
            return in_array($_REQUEST['webpSupport'], ['true', 1]);
        }

        return strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
    }

    /**
     * Проверка подходит ли тег под требования и замена данных
     *
     * @param $tag
     * @return string
     */
    public static function addLazyLoad($tag): string
    {
        if (str_ireplace(static::$option['lazy_exclusions'], '', $tag) === $tag) {
            switch (static::$option['lazy_dummy']) {
                case 'svg':
                    $wh = static::getSize($tag);
                    $dummy = static::getDefaultReplaceImage($wh['width'], $wh['height']);
                    break;
                default:
                    $dummy = static::getOneGifImage();
            }


            $tag = static::addClass($tag, 'lazyload');
            $tag = str_replace(
                ' src=',
                ' src="' . $dummy . '" data-src=',
                $tag
            );
            $tag = str_replace(' srcset=', ' data-srcset=', $tag);
            $tag = str_replace('sizes=', 'data-sizes=', $tag);
        }

        return $tag;
    }

    /**
     * Получение width и height изображения
     *
     * @param $tag
     * @return array
     */
    public static function getSize($tag): array
    {
        $width = '';
        $height = '';

        if (preg_match('#width=(["\'])(.*)(["\'])#Usmi', $tag, $_width)) {
            if (strpos($_width[2], '%') === false) {
                $width = (int)$_width[2];
            }
        }

        if (preg_match('#height=(["\'])(.*)(["\'])#Usmi', $tag, $_height)) {
            if (strpos($_height[2], '%') === false) {
                $height = (int)$_height[2];
            }
        }

        if (!$width && !$height) {
            if (preg_match('#src=(["\'])(.*)(["\'])#Usmi', $tag, $src)) {
                if ($src[2]) {
                    $sizes = getimagesize($_SERVER['DOCUMENT_ROOT'] . $src[2]);
                }

                if (isset($sizes[0])) {
                    $width = $sizes[0];
                }

                if (isset($sizes[1])) {
                    $height = $sizes[1];
                }
            }
        }

        return [
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * Добавление класса к тегу
     *
     * @param $tag
     * @param $class
     * @param string $tagName
     * @return string
     */
    public static function addClass($tag, $class, $tagName = 'img'): string
    {
        if (strpos($tag, 'class=') !== false) {
            $tag = preg_replace('/(\sclass\s?=\s?(["\']))/', '$1' . $class . ' ', $tag);
        } else {
            $tag = str_replace('<' . $tagName, '<' . $tagName . ' class="' . trim($class) . '"', $tag);
        }

        return $tag;
    }

    /**
     * Изображение для замены
     *
     * @param int $width
     * @param int $height
     * @return string
     */
    public static function getDefaultReplaceImage($width = 200, $height = 150): string
    {
        return 'data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%20' . $width . '%20' . $height . '%22%3E%3C/svg%3E';
    }

    /**
     * Проверка подходит ли тег под требования и замена данных
     *
     * @param $tag
     * @param $path - явно указанный путь к изображению в теге
     * @return string
     */
    public static function addLazyLoadBg($tag, $path): string
    {
        if (str_ireplace(static::$option['lazy_exclusions'], '', $tag) === $tag) {
            $tag = static::addClass($tag, 'lazyload', static::getTagName($tag));
            $tag = str_replace($path, static::getOneGifImage(), $tag);
            $tag = str_replace('style=', 'data-bg="' . $path . '" style=', $tag);
        }

        return $tag;
    }

    /**
     * Получение названия тега
     *
     * @param $tag
     * @return string
     */
    public static function getTagName($tag): string
    {
        if (preg_match('#<([^\s>]+)[\s|>]#Usmi', $tag, $math)) {
            if ($math[1]) {
                return $math[1];
            }
        }

        return '';
    }

    /**
     * 1gif изображение
     *
     * @return string
     */
    public static function getOneGifImage(): string
    {
        return 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
    }

    /**
     * Получение js lazy
     *
     * @return string
     */
    public static function getLazyJs($addJs = true, $bgSupport = true): string
    {
        $jsContent = '';
        if ($addJs === true) {
            $jsContent .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.0/lazysizes.min.js"></script>';
        }

        if ($bgSupport === true) {
            ob_start();
            ?>
            <script>
                /**
                 * Добавление поддержки lazy для background
                 */
                document.addEventListener('lazybeforeunveil', function (e) {
                    let bg = e.target.getAttribute('data-bg');
                    if (bg) {
                        e.target.style.backgroundImage = 'url(' + bg + ')';
                    }
                });
            </script>
            <?
            $jsLazyBgSupport = ob_get_contents();
            ob_end_clean();
            $jsContent .= $jsLazyBgSupport;
        }

        return $jsContent;
    }
}
