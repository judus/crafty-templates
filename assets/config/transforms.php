<?php
return [
    'transforms' => [
        'teaser-image' => [
            'variants' => [
                [
                    '(max-width: 544px)' => [
                        'width' => 172,
                        'height' => 172,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 1280px)' => [
                        'densities' => ['1x', '2x'],
                        'width' => 384,
                        'height' => 250,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 1024px)' => [
                        'densities' => ['1x', '2x'],
                        'width' => 326,
                        'height' => 212,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 768px)' => [
                        'densities' => ['1x', '2x'],
                        'width' => 308,
                        'height' => 200,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 640px)' => [
                        'densities' => ['1x', '2x'],
                        'width' => 222,
                        'height' => 146,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 545px)' => [
                        'width' => 203,
                        'height' => 172,
                        'mode' => 'crop',
                    ]
                ],
            ],
            'attributes' => [
                'class' => 'img--teaser'
            ]
        ],
        'content-image' => [
            'variants' => [
                [
                    '(max-width: 414px)' => [
                        'width' => 384,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 1280px)' => [
                        'densities' => ['1x', '2x'],
                        'width' => 1050,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 768px)' => [
                        'densities' => ['1x', '2x'],
                        'width' => 973,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 640px)' => [
                        'densities' => ['1x', '2x'],
                        'width' => 717,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 545px)' => [
                        'width' => 609,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 415px)' => [
                        'width' => 514,
                        'mode' => 'crop',
                    ]
                ],
            ],
            'attributes' => [
                'class' => 'img--content'
            ]
        ],
        'person-image' => [
            'variants' => [
                // 0.760252366
                [
                    '(max-width: 544px)' => [
                        'width' => 172,
                        'height' => 172,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 1280px)' => [
                        'densities' => ['1x', '2x'],
                        'width' => 384,
                        'height' => 292,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 1024px)' => [
                        'densities' => ['1x', '2x'],
                        'width' => 326,
                        'height' => 248,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 768px)' => [
                        'densities' => ['1x', '2x'],
                        'width' => 308,
                        'height' => 234,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 640px)' => [
                        'densities' => ['1x', '2x'],
                        'width' => 222,
                        'height' => 169,
                        'mode' => 'crop',
                    ]
                ],
                [
                    '(min-width: 545px)' => [
                        'width' => 203,
                        'height' => 203,
                        'mode' => 'crop',
                    ]
                ],
            ],
            'attributes' => [
                'class' => 'img-content'
            ]
        ],
    ]
];