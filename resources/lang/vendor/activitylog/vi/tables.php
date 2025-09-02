<?php

return [
    'columns' => [
        'log_name' => [
            'label' => 'Loại',
        ],
        'event' => [
            'label' => 'Sự kiện',
        ],
        'subject_type' => [
            'label' => 'Đối tượng',
        ],
        'causer' => [
            'label' => 'Người dùng',
        ],
        'properties' => [
            'label' => 'Thuộc tính',
        ],
        'created_at' => [
            'label' => 'Thời gian ghi nhận',
        ],
    ],
    'filters' => [
        'created_at' => [
            'label'         => 'Thời gian ghi nhận',
            'created_from'  => 'Tạo từ',
            'created_until' => 'Tạo đến',
        ],
        'event' => [
            'label' => 'Sự kiện',
        ],
    ],
];
