<?php

namespace Akh\BitrixOptimization\Tests;

use PHPUnit\Framework\TestCase;
use Akh\BitrixOptimization\Image;

class ImageTest extends TestCase
{
    public function testAddLazyLoadBg()
    {
        $oneGif = Image::getOneGifImage();
        $arTests = [
            [
                'path' => '/images/test.webp',
                'tag' => '<div class="testClass" style="background: url (#PATH)">text</div>',
                'result' => '<div class="lazyload testClass" data-bg="#PATH" style="background: url (' . $oneGif . ')">text</div>'
            ],
            [
                'path' => '/images/test2.webp',
                'tag' => '<div class="testClass" style="background-image: url (#PATH)">text</div>',
                'result' => '<div class="lazyload testClass" data-bg="#PATH" style="background-image: url (' . $oneGif . ')">text</div>'
            ]
        ];

        foreach ($arTests as $arTest) {
            $path = $arTest['path'];
            $tag = str_replace('#PATH', $path, $arTest['tag']);
            $result = str_replace('#PATH', $path, $arTest['result']);

            $test = Image::AddLazyLoadBg($tag, $path);
            $this->assertSame($test, $result);
        }
    }

    public function testCheckWebpSupport()
    {
        $this->assertSame(Image::checkWebpSupport(), false);
    }

    public function testAddClass()
    {
        $arTests = [
            [
                'class' => 'testClass',
                'tag' => '<div class="">text</div>',
                'tagName' => 'div',
                'result' => '<div class="#CLASS ">text</div>'
            ],
            [
                'class' => 'testClass',
                'tag' => '<img src="image.jpg" alt="">',
                'tagName' => 'img',
                'result' => '<img class="#CLASS" src="image.jpg" alt="">'
            ],
            [
                'class' => 'testClass',
                'tag' => '<img src="image2.jpg" class="image" alt="">',
                'tagName' => 'img',
                'result' => '<img src="image2.jpg" class="#CLASS image" alt="">'
            ],
        ];

        foreach ($arTests as $arTest) {
            $result = str_replace('#CLASS', $arTest['class'], $arTest['result']);

            $test = Image::AddClass($arTest['tag'], $arTest['class'], $arTest['tagName']);
            $this->assertSame($test, $result);
        }
    }

    public function testAddLazyLoad()
    {
        $dummy = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
        $arTests = [
            [
                'tag' => '<img src="/tests/testImage.jpg" alt="">',
                'result' => '<img class="lazyload" src="' . $dummy . '" data-src="/tests/testImage.jpg" alt="">'
            ],
            [
                'tag' => '<img src="/tests/testImage.jpg" srcset="/tests/testImage.jpg x1, /tests/testImage.jpg x2" alt="">',
                'result' => '<img class="lazyload" srcset="' . $dummy . '" src="/tests/testImage.jpg" data-srcset="/tests/testImage.jpg x1, /tests/testImage.jpg x2" alt="">'
            ],
            [
                'tag' => '<img class="image" src="/tests/testImage.jpg" alt="">',
                'result' => '<img class="lazyload image" src="' . $dummy . '" data-src="/tests/testImage.jpg" alt="">'
            ],
            [
                'tag' => '<img src="' . $dummy . '" alt="">',
                'result' => '<img src="' . $dummy . '" alt="">',
            ],
            [
                'tag' => '<img class="lazyload image" src="/tests/testImage.jpg" alt="">',
                'result' => '<img class="lazyload image" src="/tests/testImage.jpg" alt="">',
            ],
            [
                'tag' => '<img class="image" src="/tests/testImage.jpg" data-src="/tests/testImage.jpg" alt="">',
                'result' => '<img class="image" src="/tests/testImage.jpg" data-src="/tests/testImage.jpg" alt="">',
            ],
        ];

        foreach ($arTests as $arTest) {
            $test = Image::AddLazyLoad($arTest['tag']);
            $this->assertSame($test, $arTest['result']);
        }
    }

    public function testGetSrc()
    {
        $arTests = [
            [
                'tag' => '<div>test</div>',
                'result' => []
            ],
            [
                'tag' => '<img src="image1.jpg" alt="">',
                'result' => [
                    'image1.jpg'
                ]
            ],
            [
                'tag' => '<img src="image1.jpg" srcset="image1x.jpg x1, image2x.jpg x2" alt="">',
                'result' => [
                    'image1.jpg',
                    'image1x.jpg',
                    'image2x.jpg'
                ]
            ],
            [
                'tag' => '<img src="" data-src="image1.jpg" data-srcset="image1x.jpg x1, image2x.jpg x2" alt="">',
                'result' => [
                    'image1.jpg',
                    'image1x.jpg',
                    'image2x.jpg'
                ]
            ],
        ];

        foreach ($arTests as $arTest) {
            $test = Image::GetSrc($arTest['tag']);
            $this->assertSame($test, $arTest['result']);
        }
    }

    public function testGetSize()
    {
        $arTests = [
            [
                'tag' => '<img width="100" height="100" src="/tests/testImage.jpg" alt="">',
                'result' => [
                    'width' => 100,
                    'height' => 100,
                ]
            ],
            /*[
                'tag' => '<img src="/tests/testImage.jpg" alt="">',
                'result' => [
                    'width' => 153,
                    'height' => 152,
                ]
            ],*/
        ];

        foreach ($arTests as $arTest) {
            $test = Image::GetSize($arTest['tag']);
            $this->assertSame($test, $arTest['result']);
        }
    }

    public function testGetTagName()
    {
        $arTests = [
            [
                'tag' => '<div>test<span></span></div>',
                'result' => 'div'
            ],
            [
                'tag' => '<img src="image1.jpg" alt="">',
                'result' => 'img'
            ],
            [
                'tag' => '<span>test</span>',
                'result' => 'span'
            ],
        ];

        foreach ($arTests as $arTest) {
            $test = Image::GetTagName($arTest['tag']);
            $this->assertSame($test, $arTest['result']);
        }
    }
}
