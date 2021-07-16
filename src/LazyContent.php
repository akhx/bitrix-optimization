<?php

namespace Akh\BitrixOptimization;

use Bitrix\Main\Context;


/**
 * Class LazyContent
 *
 * @package Akh\BitrixOptimization
 */
class LazyContent
{
    protected static $loadJs;
    /**
     * @var string $requestKey Ключ запроса вызова
     */
    private static $requestKey, $currentKey;

    /**
     * Основная функция класса
     *
     * @param string $filePath Файл для ленивой загрузки
     * @param array $arParams Массив параметров для передачи в CMain::IncludeFile()
     * @param bool $onScroll будет ли подгружаться блок по скролу или сразу по onLoad
     * @uses CMain::IncludeFile()
     * @link https://dev.1c-bitrix.ru/api_help/main/reference/cmain/includefile.php
     */
    public static function includeFile(string $filePath, array $arParams = [], $onScroll = false)
    {
        /**
         * Проверяем существование запрошенного файла
         */
        if (!static::fileExist($filePath)) {
            static::showError(
                str_replace(
                    ["#PATH#"],
                    [$filePath],
                    "Can't find file path '#PATH#' for LazyLoading"
                )
            );

            return;
        }

        /**
         * Генерируем уникальный ключ
         */
        self::$currentKey = self::generateKey($filePath, $arParams, $onScroll);

        /**
         * Если генерация не удалась
         */
        if (!self::$currentKey) {
            static::showError(
                str_replace(
                    ["#PATH#"],
                    [$filePath],
                    "Can't generate ajaxKey to file '#PATH#' for LazyLoading"
                )
            );

            return;
        }

        /**
         * Проверяем требование к файлу-заглушке (до замены) в параметрах
         * @param string $arParams ['DULL_FILE_PATH']
         */
        $dullFilePath = null;
        if (!empty($arParams['DULL_FILE_PATH'])) {
            $dullFilePath = $arParams['DULL_FILE_PATH'];
            if (!static::fileExist($dullFilePath)) {
                static::showError(
                    str_replace(
                        ["#PATH#"],
                        [$dullFilePath],
                        "Can't find dull file path '#PATH#' for LazyLoading"
                    )
                );

                return;
            }
            unset($arParams['DULL_FILE_PATH']);
        }

        $arDataAttr = [];
        if ($onScroll === true) {
            $arDataAttr[] = 'data-on-scroll="Y"';
        }

        /**
         * Проверяем наличие параметра за сколько пикселей при скроле грузить блок
         */
        if (empty($arParams['DATA_OFFSET_LOAD'])) {
            $arParams['DATA_OFFSET_LOAD'] = 600;
        }

        if ($onScroll === true) {
            $arDataAttr[] = 'data-on-scroll-offset="' . $arParams['DATA_OFFSET_LOAD'] . '"';
        }

        unset($arParams['DATA_OFFSET_LOAD']);

        if (static::isAdmin() === true && $_REQUEST['include_lazy_content'] === 'Y') {
            self::getApplication()->IncludeFile(
                $filePath,
                $arParams
            );

            return;
        }

        /**
         * Определяем метод работы в зависимости от типа Http-запроса
         */
        if (!self::isLazyAjax()) {
            /**
             * Это не запрос на отдачу области
             * Отдаем блок для работы js (подразумевается что js перемещается в конец страницы)
             */
            echo static::getJs();
            echo '<div class="js-lazy-page" data-lazyload-key="' . self::$currentKey . '" ' . implode(
                    ' ',
                    $arDataAttr
                ) . '>';
            /**
             * Если параметром задана заглушка, и она существует
             */
            if ($dullFilePath) {
                /**
                 * Подключаем заглушку, и передаем ей общие параметры подключения
                 */
                self::getApplication()->IncludeFile($dullFilePath, $arParams);
            }

            echo '</div>';
        } else {
            if (self::$currentKey == self::$requestKey) {
                /**
                 * Это запрос к нашему классу и ключи запроса на конретный вызов совпали
                 * Отдаем запрошенную область
                 */
                self::getApplication()->RestartBuffer();
                self::getApplication()->IncludeFile(
                    $filePath,
                    $arParams
                );
                require($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/epilog_after.php');
                exit();
            }
        }
    }

    /**
     * Проверка существования запрошенного файла
     *
     * @param string $filePath Файл для ленивой загрузки
     * @return bool
     */
    public static function fileExist(string $filePath): bool
    {
        if (substr($filePath, 0, 1) !== '/') {
            $path = getLocalPath('templates/' . SITE_TEMPLATE_ID . '/' . $filePath, BX_PERSONAL_ROOT);
            if ($path === false) {
                $path = getLocalPath('templates/.default/' . $filePath, BX_PERSONAL_ROOT);
                if ($path === false) {
                    $path = BX_PERSONAL_ROOT . '/templates/' . SITE_TEMPLATE_ID . '/' . $filePath;
                }
            }
        } else {
            $path = $filePath;
        }

        return file_exists($_SERVER['DOCUMENT_ROOT'] . $path);
    }

    /**
     * Показ ошибок
     *
     * @param string $errorMessage Сообщение ошибки
     */
    protected static function showError(string $errorMessage)
    {
        if ($errorMessage <> '') {
            echo '<span style="color: red;">' . $errorMessage . '</font>';
        }
    }

    /**
     * Генерация уникального ключа
     *
     * @param $filePath
     * @param $arParams
     * @param $onScroll
     * @return string
     */
    private static function generateKey($filePath, $arParams, $onScroll): string
    {
        return md5($filePath . serialize($arParams) . $onScroll);
    }

    /**
     * Определение типа и вида запроса
     *
     * @return bool
     */
    private static function isLazyAjax(): bool
    {
        $xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($xhr) {
            $context = Context::getCurrent();
            $request = $context->getRequest();
            if ($request->getPost('LAZY') || $request->getQuery('LAZY')) {
                if ($request->getPost('KEY') || $request->getQuery('KEY')) {
                    self::$requestKey = $request->getPost('KEY') ?: $request->getQuery('KEY');

                    return true;
                }
            }
        }

        return false;
    }

    protected static function getJs(): string
    {
        if (static::$loadJs !== null) {
            return '';
        }

        ob_start();
        ?>
        <script>
            function getLazyContent(target) {
                let key = target.getAttribute('data-lazyload-key');
                let data = {
                    KEY: key,
                    LAZY: 'Y',
                    webpSupport: <?= Image::checkWebpSupport() === true ? 'true' : 'false'; ?>
                };

                let params = '';
                for (let key in data) {
                    if (params !== '') {
                        params += '&';
                    }

                    params += key + '=' + data[key];
                }

                let request = new XMLHttpRequest();
                request.open('GET', window.location.pathname + '?' + params, true);
                request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                request.onload = function () {
                    if (this.status >= 200 && this.status < 400) {
                        target.outerHTML = this.response;

                        let event = document.createEvent('HTMLEvents');
                        event.initEvent('lazycontentloaded', true, false);
                        document.dispatchEvent(event);
                    }
                };
                request.onerror = function () {
                };
                request.send();
            }

            let instantLoad = document.querySelectorAll('.js-lazy-page:not([data-on-scroll="Y"])');
            Array.prototype.forEach.call(instantLoad, function (el) {
                getLazyContent(el);
            });

            document.addEventListener('scroll', function () {
                let scrollLazy = document.querySelectorAll('.js-lazy-page[data-on-scroll="Y"]');
                Array.prototype.forEach.call(scrollLazy, function (el) {
                    let itemOffset = el.getAttribute('data-on-scroll-offset');
                    let rect = el.getBoundingClientRect();
                    if (
                        (rect.top - itemOffset) < document.documentElement.clientHeight
                        && el.classList.contains('loading') === false
                    ) {
                        el.classList.add('loading');
                        getLazyContent(el);
                    }
                });
            });
        </script>
        <?
        $js = ob_get_contents();
        ob_end_clean();
        static::$loadJs = true;

        return $js;
    }

    /**
     * @return \CAllMain|\CMain
     */
    protected static function getApplication()
    {
        global $APPLICATION;

        return $APPLICATION;
    }

    protected static function isAdmin(): bool
    {
        global $USER;
        if (is_object($USER)) {
            return $USER->isAdmin();
        }

        return false;
    }
}
