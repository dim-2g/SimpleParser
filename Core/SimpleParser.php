<?php

namespace Core;

use Core\Product;
use Core\Logger;
use Core\Image;
use Core\Option;
use Core\Utils;

class SimpleParser
{
    /*
     * адрес донора с указанием протокола
     */
    public $remote_host;

    /*
     * Адрес страницы донора
     */
    public $url;

    /*
     * директория хранения кеша
     */
    public $cache_path;
    /*
     * HTML код все страницы
     */
    public $raw_content;

    /*
     * цена товара
     */
    public $price;

    /*
     * наличие товара на складе
     */
    public $available;

    /*
     * гарантия
     */
    public $warranty;

    /*
     * Искомый элемент
     */
    public $product;

    /*
     * артикул
     */
    public $article;

    /*
     * для ведения лога
     */
    public $logger;

    function __construct(Product $product = null)
    {
        $this->remote_host = 'https://www.domain.ru/';
        $this->root = dirname(__DIR__);
        $this->cache_path = $this->root . '/cache';
        $this->images_path = $this->root . '/images';
        if ($product instanceof Product) {
            $this->product = clone $product;
        } else {
            $this->product = new Product();
        }
        $this->log = new Logger('log.txt');
    }

    public function run()
    {

    }

    public function parseOnePageByArticle($article = '')
    {
        if (empty($this->product->article)) {
            die('Не найден артикул');
        }
        $this->article = $this->product->article;
        $url = $this->getRemotePageAddress($this->product->article);
        $this->product->remote_url = $url;
        return $this->parseOnePageByUrl($url);
    }

    public function parseOnePageByUrl($url, $asJson = false)
    {
        $this->raw_content = $this->getCacheContent($url);
        $this->product->price = $this->findPrice($this->raw_content);
        $this->product->available = $this->findAvailable($this->raw_content);
        $this->product->warranty = $this->findWarranty($this->raw_content);
        $this->product->description = $this->findDescription($this->raw_content);
        $this->product->complectation = $this->findComplectation($this->raw_content);
        $this->findOptions($this->raw_content);
        $this->findImages($this->raw_content);

        //$this->product->toString(true);
        //echo $this->product->toJson();

        if ($asJson) {
            return json_encode($this->product);
        } else {
            return $this->product;
        }
    }

    private function getRemotePageAddress($article)
    {
        return "{$this->remote_host}/catalog/{$article}.html";
    }

    private function findPrice($content)
    {
        $price = '';
        if (preg_match('#<span[^>]*id="price_number"[^>]*>([^<]+)</span>#siU', $content, $match)) {
            $price = $match[1];
            $price = (int)trim(str_replace(' ','', $price));
            return $price;
        }
        return false;
    }

    private function findAvailable($content)
    {
        $available = '';
        if (preg_match('#<td\s*colspan="2"\s*>Наличие:</td>\s*<td\s*class="stock_value">[^<]+<div\s*title="([^"]+)"\s*class="[^"]+"></div></td>#siU', $content, $match)) {
            $available = $match[1];
            $available = trim($available);
            return $available;
        }
        return false;
    }

    private function findWarranty($content)
    {
        $warranty = '';
        if (preg_match('#<td\s*colspan="2">\s*Гарантия:\s*</td>\s*<td\s*class="stock_value"\s*>(.*)\.?</td>#siU', $content, $match)) {
            $warranty = $match[1];
            $warranty = trim($warranty);
            return $warranty;
        }
        return false;
    }

    private function findDescription($content)
    {
        $description = '';
        if (preg_match('#<div\s*class="product_detail_full_desc"[^>]*>(.*)<div class="product_detail_full_desc">#siU', $content, $match)) {
            $description = $match[1];
            $description = trim($description);
            $description = preg_replace('#<meta[^>]*>#siU', '', $description);
            $description = preg_replace('#<h2[^>]*>.*</h2>#siU', '', $description);
            $description = str_replace('&#13;', '', $description);
            $description = preg_replace('#^(.*)<h3>Состав комплекта:</h3>.*$#siU', '$1', $description);
            $description = trim($description);
            return $description;
        }
        return false;
    }

    private function findComplectation($content)
    {
        $text = '';
        if (preg_match('#<div\s*class="product_detail_full_desc"[^>]*>(.*<h3>Состав комплекта:</h3>.*)<div class="product_detail_full_desc">#siU', $content, $match)) {
            $text = $match[1];
            $text = trim($text);
            $text = preg_replace('#<meta[^>]*>#siU', '', $text);
            $text = preg_replace('#<h2[^>]*>.*</h2>#siU', '', $text);
            $text = str_replace('&#13;', '', $text);
            $text = preg_replace('#^.*<h3>Состав комплекта:</h3>(.*)$#siU', '$1', $text);
            $text = trim($text);
            if (!empty($text)) {
                return $text;
            }
        }
        return false;
    }

    public function findOptions($content)
    {
        if (preg_match('#<div\s*class="product_detail_full_desc">\s*<table>(.*)</table>\s*</div>#siU', $content, $match)) {
            $contentTable = trim($match[1]);
            if (preg_match_all('#<tr>\s*<td>(?P<name>.+)</td>\s*<td>(?P<value>.+)</td>\s*</tr>#siU', $contentTable, $matchOptions)) {
                foreach ($matchOptions['name'] as $index => $name) {
                    $value = $matchOptions['value'][$index];
                    $value = trim(strip_tags($value));
                    if ($value == 'Посмотреть') continue;
                    $this->product->addOptions($name, $value);
                }
            }
        }

    }

    public function findImages($content)
    {

        if (preg_match_all('#<a[^>]+pr_detail_image_inner[^>]+href="(?P<src>[^"]+)"[^>]+title="(?P<title>[^"]+)"[^>]+>#siU', $content, $matches)) {
            foreach ($matches['src'] as $index => $src) {
                $title = $matches['title'][$index];
                $value = trim($title);

                $image = new Image();
                $image->alt = $title;
                $image->remote_path = $this->findRemoteImagePath($src);
                $image->local_path = $this->generateImageName($src);
                $image->name = basename($image->local_path);
                if (!$this->saveImageToLocalStorage($image)) {
                    $this->log->write('findImages: не удалось загрузить изображение: ' . $image->remote_path);
                }
                if (is_readable($image->local_path)) {
                    $this->product->images[] = $image;
                }
            }
        }
    }

    public function saveImageToLocalStorage(Image $image)
    {
        @unlink($image->local_path);
        $this->createDirectories($image->local_path);
        $responseImageContent = $this->getContent($image->remote_path);
        if ($responseImageContent['success'] && !empty($responseImageContent['content'])) {
            Utils::file_put_contents_force($image->local_path, $responseImageContent['content']);
            return true;
        }
        return false;
    }

    public function findRemoteImagePath($src)
    {
        $url = $this->remote_host . ltrim($src, '/');
        return $url;
    }

    public function generateImageName($path)
    {
        $pathinfo = pathinfo($path);
        $filename = md5($path) . '.' . $pathinfo['extension'];
        $articlePart = !empty($this->article) ? $this->article : substr(uniqid(),7);
        $fullImagePath = $this->images_path . '/' . $articlePart . '/' . $filename;
        return $fullImagePath;
    }

    private function createDirectories($url)
    {
        $directoryPath = dirname($url);
        $result = @mkdir($directoryPath.'/',0755,true);
        return $result;
    }

    private function getContent($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER,1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:64.0) Gecko/20100101 Firefox/64.0');
        $rawdata = curl_exec($curl);
        $response = curl_getinfo($curl);
        curl_close($curl);

        $success = false;
        if ($response['http_code'] == 200) {
            $success = true;
        } else {
            $this->log->write('getContent: не удалось загрузить URL: ' . $url);
        }

        return array(
            'success' => $success,
            'content' => $rawdata,
        );
    }

    private function getCacheContent($url)
    {
        $md5 = md5($url);
        $sCacheFilename = $this->cache_path . '/' . $md5;
        if (!file_exists($sCacheFilename)) {
            $response = $this->getContent($url);
            if ($response['success']) {
                Utils::file_put_contents_force($sCacheFilename, $response['content']);
            }
            return $response['content'];
        } else {
            return file_get_contents($sCacheFilename);
        }
        return false;
    }
}