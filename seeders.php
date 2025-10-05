<?php
// seeders.php
require_once 'config.php';
require_once 'DB.php';
require_once 'NotificationRepository.php';

$cfg = require __DIR__ . '/config.php';
$db = new DB($cfg['db']);
$repo = new NotificationRepository($db->pdo());

// Templates (5)
$templates = [
    [
        'id_name' => 'promo_01',
        'template_name' => 'Promoção relâmpago',
        'title' => '🔥 Promoção relâmpago!',
        'subtitle' => 'Só hoje',
        'content' => 'Desconto de 50% em itens selecionados.',
        'link' => 'https://example.com/promo',
        'base' => 'mobile'
    ],
    [
        'id_name' => 'news_01',
        'template_name' => 'Novidade no app',
        'title' => '🆕 Novidade disponível',
        'subtitle' => 'Confira agora',
        'content' => 'Acabamos de lançar uma nova funcionalidade.',
        'link' => 'https://example.com/news',
        'base' => 'all'
    ],
    [
        'id_name' => 'alert_01',
        'template_name' => 'Alerta importante',
        'title' => '⚠️ Atenção',
        'subtitle' => 'Atualize seu app',
        'content' => 'Versão nova disponível com correções.',
        'link' => 'https://example.com/update',
        'base' => 'android'
    ],
    [
        'id_name' => 'sale_01',
        'template_name' => 'Descontos semanais',
        'title' => '🎯 Descontos na sua área',
        'subtitle' => 'Ofertas selecionadas',
        'content' => 'Veja os itens em promoção esta semana.',
        'link' => 'https://example.com/deals',
        'base' => 'all'
    ],
    [
        'id_name' => 'tips_01',
        'template_name' => 'Dica do dia',
        'title' => '💡 Dica para melhorar seu uso',
        'subtitle' => 'Pequeno truque',
        'content' => 'Aprenda a usar a busca avançada.',
        'link' => 'https://example.com/tip',
        'base' => 'ios'
    ],
];

foreach ($templates as $t) {
    $repo->insertTemplate($t);
}
echo "Inserted templates\n";

// Devices (5)
$devices = [
    [
        'user_id' => 1,
        'device_info' => ['id'=>'dev-1','brand'=>'Samsung','model'=>'A50','os'=>'android','os_version'=>'10'],
        'fcm_token' => 'token_device_1'
    ],
    [
        'user_id' => 2,
        'device_info' => ['id'=>'dev-2','brand'=>'Xiaomi','model'=>'Mi9','os'=>'android','os_version'=>'11'],
        'fcm_token' => 'token_device_2'
    ],
    [
        'user_id' => 3,
        'device_info' => ['id'=>'dev-3','brand'=>'Apple','model'=>'iPhone X','os'=>'ios','os_version'=>'14'],
        'fcm_token' => 'token_device_3'
    ],
    [
        'user_id' => 4,
        'device_info' => ['id'=>'dev-4','brand'=>'Motorola','model'=>'G7','os'=>'android','os_version'=>'9'],
        'fcm_token' => 'token_device_4'
    ],
    [
        'user_id' => 5,
        'device_info' => ['id'=>'dev-5','brand'=>'OnePlus','model'=>'7T','os'=>'android','os_version'=>'10'],
        'fcm_token' => 'token_device_5'
    ],
];

foreach ($devices as $d) {
    $repo->insertDevice($d);
}
echo "Inserted devices\n";
