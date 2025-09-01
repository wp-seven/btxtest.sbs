<?php
function get_page_html($url) {
    $options = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0\r\n",
            "timeout" => 10
        ]
    ];
    $context = stream_context_create($options);
    $html = @file_get_contents($url, false, $context);
    if ($html === false) {
        return null;
    }
    return $html;
}

function parce_html($h) {
    $data = array();
    require_once 'simple_html_dom.php';
    $html = str_get_html($h);
    if(!$html) return $data;
	$h1 = $html->find('h1', 0);
    if($h1) $data['name'] = $h1->plaintext;
    $price = $html->find('.product-sidebar-calc__price', 0);
    if($price) $data['price'] = trim(preg_replace('/[^\d]/u', '', html_entity_decode($price->plaintext)));
    $art = $html->find('.product-sidebar-info', 0);
    $arts = $art->find('.product-sidebar__text');
    foreach($arts as $a) {
        if(mb_stripos($a->plaintext, 'артикул', 0, 'UTF-8') !== false) {
            $article = $a->find('.product-sidebar__span', 0);
            if($article) $data['article'] = trim($article->plaintext);
            break;
        }
    }
    $desc = $html->find('.catalog-reference-content', 0);
    if($desc) $data['description'] = $desc->innertext;
    $chars = $html->find('.product-desc-list .product-desc-item');
    foreach($chars as $ch) {
        $ctitle = $ch->find('h2', 0);
        if(!$ctitle) continue;
        $ctitle = trim($ctitle->plaintext);
        $clines = $ch->find('.product-desc-line');
        foreach($clines as $cline) {
            $ltitle = $cline->find('.product-desc-line__title .product__text', 0);
            if($ltitle) $cltitle = trim(str_replace(':', '', $ltitle->plaintext));
            $lvalue = $cline->find('div.product__text', 0);
            if($lvalue) { 
                $clvalue = trim(str_replace('Расчет мощности', '', $lvalue->plaintext)); //костыль)))
                $clvalue = explode(', ', $clvalue); 
            }
            if($cltitle && $clvalue) {
                $data['chars'][$ctitle][$cltitle] = $clvalue;
            }
        }
    }
    return $data;
}

if (!function_exists("prr")) { function prr($str) { echo "<pre>"; print_r($str); echo "</pre>\r\n"; } }
?>
