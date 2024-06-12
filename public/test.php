<?php
$url="http://www.baidu.com/index.html?a=1";
function parseQueryParamsFromUrl($url) {
    $parsedUrl = parse_url($url);

    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $output);
        return $output; // 返回查询参数数组
    } else {
        return []; // 如果没有查询参数，返回一个空数组
    }
}
$output=parseQueryParamsFromUrl($url);
var_dump($output);