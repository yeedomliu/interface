<?php

namespace wii\interfaces\proxy;

use linslin\yii2\curl\Curl;

class BaiduTranslate extends Base
{

    public function getContent() {
        $url = "http://translate.baiducontent.com/transpage?query={$this->getUrl()}&from=en&to=zh&source=url";
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT, 5);
        $result = $curl->get($url, $this->isRaw());
        $result = preg_replace_callback('/http:\/\/translate.baiducontent.com.+?source=url&query=(.+?)&from=en&to=zh&token=&monLang=zh/is', function ($matches) {
            return urldecode($matches[1]);
        }, $result); // 处理翻译url
        $result = preg_replace('/<trans data-src="(.+?)">.+?<\/trans>/is', '$1', $result); // 处理翻译词条

        return $result;
    }

}