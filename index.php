<?php
include 'includes/load.php';
global $db, $p_db;
prr('hi');
prr('hi2');
$html = '';
$error = '';
$message = '';

if (isset($_POST['parceurl'])) {
    $url = trim($_POST['parceurl']);
    if (!empty($url)) {
        $html = get_page_html($url);
        if ($html === false) {
            $error = "Не удалось получить страницу. Проверьте URL";
        }
    } else {
        $error = "Введите URL.";
    }
    $data = array();
    if($html) {
        $data = parce_html($html);
        if(!empty($data)) {
            $data['url'] = $url;
            $product = new Product($data);
            $pid = $p_db->save_product($product);
            if(is_numeric($pid)) $message = "Продукт сохранен в БД";
            else $message = "Продукт обновлен в БД";
        } else $error = "Не удалось распарсить страницу";
    }
}
if (isset($_POST['geturl'])) {
    $url = trim($_POST['geturl']);
    if (!empty($url)) {
        $db_product = $p_db->get_product($url);
        if (!$db_product) {
            $error = "Такого URL нет в базе данных";
        }
    } else {
        $error = "Введите URL";
    }
}
$db->close();


?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Парсер URL</title>
</head>
<body>
    <h2>Парсер страницы по URL</h2>

    <form method="post">
        <input style="width:600px;height:40px;" type="text" name="parceurl" placeholder="Введите URL" size="50" value="<?= isset($url) ? htmlspecialchars($url) : '' ?>">
        <button type="submit">Parce</button>
    </form>

    <h2>Получение данных из БД по URL страницы</h2>

    <form method="post">
        <input style="width:600px;height:40px;" type="text" name="geturl" placeholder="Введите URL" size="50" value="<?= isset($url) ? htmlspecialchars($url) : '' ?>">
        <button type="submit">Get</button>
    </form>
    <?php if ($error) { ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php } ?>
    <?php if ($message) { ?>
        <p style="color:blue;"><?= htmlspecialchars($message) ?></p>
    <?php } ?>
    <?php if(!empty($db_product)) {
        $db_product = $db_product->to_array();
        prr('Название: ' . $db_product['name']);
        prr('Артикул: ' . $db_product['article']);
        prr('Цена: ' . $db_product['price']);
        prr('Описание: ' . $db_product['description']);
        prr('<h2>Характеристики: </h2>');
        foreach($db_product['chars'] as $kcs => $chars) {
            prr('<h3>' . $kcs . '</h3>');
            foreach($chars as $kc => $char) {
                prr($kc . ': ' . implode(', ' , $char));
            }
            prr('<br>');
        }
    }
    ?>
</body>
</html>