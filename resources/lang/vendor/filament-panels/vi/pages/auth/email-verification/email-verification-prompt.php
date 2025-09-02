<?php

return [

    'title' => 'Xác thực địa chỉ email',

    'heading' => 'Xác thực địa chỉ email',

    'actions' => [

        'resend_notification' => [
            'label' => 'Gửi email xác thực',
        ],

    ],

    'messages' => [
        'notification_not_received' => 'Xác thực ngay?',
        'notification_sent' => 'Vui lòng chọn vào "Gửi email xác thực" để nhận hướng dẫn về cách xác minh địa chỉ :email của bạn.',
    ],

    'notifications' => [

        'notification_resent' => [
            'title' => 'Chúng tôi đã gửi một email xác thực. Vui lòng kiểm tra email.',
        ],

        'notification_resend_throttled' => [
            'title' => 'Quá nhiều lần thử gửi mail',
            'body' => 'Xin vui lòng thử lại sau :seconds giây.',
        ],

    ],

];
