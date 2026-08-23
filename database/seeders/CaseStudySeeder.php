<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\User;
use Illuminate\Database\Seeder;

class CaseStudySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $adminId = $admin?->id;

        $case1Circuit = [
            'version' => 1,
            'components' => [
                [
                    'id' => 'battery-001',
                    'type' => 'battery',
                    'name' => 'Baterai DC 1',
                    'x' => 200,
                    'y' => 220,
                    'rotation' => 0,
                    'width' => 140,
                    'height' => 70,
                    'properties' => ['voltage' => 12, 'internalResistance' => 0.05],
                    'terminals' => [
                        ['id' => 'term_pos', 'name' => '+', 'label' => '+ (12V)', 'relX' => 135, 'relY' => 35, 'color' => '#ef4444'],
                        ['id' => 'term_neg', 'name' => '-', 'label' => '- (GND)', 'relX' => 5, 'relY' => 35, 'color' => '#0f172a'],
                    ]
                ],
                [
                    'id' => 'lamp-002',
                    'type' => 'lamp',
                    'name' => 'Lampu Pijar 2',
                    'x' => 440,
                    'y' => 140,
                    'rotation' => 0,
                    'width' => 110,
                    'height' => 80,
                    'properties' => ['ratedVoltage' => 12, 'ratedPower' => 20, 'resistance' => 7.2],
                    'terminals' => [
                        ['id' => 'term_pos', 'name' => '+', 'label' => 'Terminal Positif (+)', 'relX' => 105, 'relY' => 40, 'color' => '#ef4444'],
                        ['id' => 'term_neg', 'name' => '-', 'label' => 'Terminal Negatif (-)', 'relX' => 5, 'relY' => 40, 'color' => '#0f172a'],
                    ]
                ],
                [
                    'id' => 'switch_spst-003',
                    'type' => 'switch_spst',
                    'name' => 'Saklar Rocker 3',
                    'x' => 440,
                    'y' => 320,
                    'rotation' => 0,
                    'width' => 130,
                    'height' => 75,
                    'properties' => ['isClosed' => false],
                    'terminals' => [
                        ['id' => 'term_1', 'name' => '1', 'label' => 'Pin Input (1)', 'relX' => 10, 'relY' => 37, 'color' => '#38bdf8'],
                        ['id' => 'term_2', 'name' => '2', 'label' => 'Pin Output (2)', 'relX' => 120, 'relY' => 37, 'color' => '#38bdf8'],
                    ]
                ]
            ],
            'connections' => [
                [
                    'id' => 'conn-001',
                    'from' => ['componentId' => 'battery-001', 'terminalId' => 'term_pos'],
                    'to' => ['componentId' => 'lamp-002', 'terminalId' => 'term_pos'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ],
                [
                    'id' => 'conn-002',
                    'from' => ['componentId' => 'lamp-002', 'terminalId' => 'term_neg'],
                    'to' => ['componentId' => 'switch_spst-003', 'terminalId' => 'term_2'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ],
                [
                    'id' => 'conn-003',
                    'from' => ['componentId' => 'switch_spst-003', 'terminalId' => 'term_1'],
                    'to' => ['componentId' => 'battery-001', 'terminalId' => 'term_neg'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ]
            ]
        ];

        $case2Circuit = [
            'version' => 1,
            'components' => [
                [
                    'id' => 'battery-001',
                    'type' => 'battery',
                    'name' => 'Baterai DC 1',
                    'x' => 180,
                    'y' => 220,
                    'rotation' => 0,
                    'width' => 140,
                    'height' => 70,
                    'properties' => ['voltage' => 12, 'internalResistance' => 0.05],
                    'terminals' => [
                        ['id' => 'term_pos', 'name' => '+', 'label' => '+ (12V)', 'relX' => 135, 'relY' => 35, 'color' => '#ef4444'],
                        ['id' => 'term_neg', 'name' => '-', 'label' => '- (GND)', 'relX' => 5, 'relY' => 35, 'color' => '#0f172a'],
                    ]
                ],
                [
                    'id' => 'lamp-002',
                    'type' => 'lamp',
                    'name' => 'Lampu Pijar 2',
                    'x' => 380,
                    'y' => 140,
                    'rotation' => 0,
                    'width' => 110,
                    'height' => 80,
                    'properties' => ['ratedVoltage' => 12, 'ratedPower' => 20, 'resistance' => 7.2],
                    'terminals' => [
                        ['id' => 'term_pos', 'name' => '+', 'label' => 'Terminal Positif (+)', 'relX' => 105, 'relY' => 40, 'color' => '#ef4444'],
                        ['id' => 'term_neg', 'name' => '-', 'label' => 'Terminal Negatif (-)', 'relX' => 5, 'relY' => 40, 'color' => '#0f172a'],
                    ]
                ],
                [
                    'id' => 'resistor-003',
                    'type' => 'resistor',
                    'name' => 'Resistor 3',
                    'x' => 580,
                    'y' => 220,
                    'rotation' => 0,
                    'width' => 120,
                    'height' => 60,
                    'properties' => ['resistance' => 220, 'powerRating' => 0.25],
                    'terminals' => [
                        ['id' => 'term_a', 'name' => 'A', 'label' => 'Terminal A', 'relX' => 5, 'relY' => 30, 'color' => '#f8fafc'],
                        ['id' => 'term_b', 'name' => 'B', 'label' => 'Terminal B', 'relX' => 115, 'relY' => 30, 'color' => '#f8fafc'],
                    ]
                ],
                [
                    'id' => 'switch_spst-004',
                    'type' => 'switch_spst',
                    'name' => 'Saklar Rocker 4',
                    'x' => 380,
                    'y' => 320,
                    'rotation' => 0,
                    'width' => 130,
                    'height' => 75,
                    'properties' => ['isClosed' => true],
                    'terminals' => [
                        ['id' => 'term_1', 'name' => '1', 'label' => 'Pin Input (1)', 'relX' => 10, 'relY' => 37, 'color' => '#38bdf8'],
                        ['id' => 'term_2', 'name' => '2', 'label' => 'Pin Output (2)', 'relX' => 120, 'relY' => 37, 'color' => '#38bdf8'],
                    ]
                ]
            ],
            'connections' => [
                [
                    'id' => 'conn-001',
                    'from' => ['componentId' => 'battery-001', 'terminalId' => 'term_pos'],
                    'to' => ['componentId' => 'lamp-002', 'terminalId' => 'term_pos'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ],
                [
                    'id' => 'conn-002',
                    'from' => ['componentId' => 'lamp-002', 'terminalId' => 'term_neg'],
                    'to' => ['componentId' => 'resistor-003', 'terminalId' => 'term_a'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ],
                [
                    'id' => 'conn-003',
                    'from' => ['componentId' => 'resistor-003', 'terminalId' => 'term_b'],
                    'to' => ['componentId' => 'switch_spst-004', 'terminalId' => 'term_2'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ],
                [
                    'id' => 'conn-004',
                    'from' => ['componentId' => 'switch_spst-004', 'terminalId' => 'term_1'],
                    'to' => ['componentId' => 'battery-001', 'terminalId' => 'term_neg'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ]
            ]
        ];

        $case3Circuit = [
            'version' => 1,
            'components' => [
                [
                    'id' => 'battery-001',
                    'type' => 'battery',
                    'name' => 'Baterai DC 1',
                    'x' => 160,
                    'y' => 240,
                    'rotation' => 0,
                    'width' => 140,
                    'height' => 70,
                    'properties' => ['voltage' => 12, 'internalResistance' => 0.05],
                    'terminals' => [
                        ['id' => 'term_pos', 'name' => '+', 'label' => '+ (12V)', 'relX' => 135, 'relY' => 35, 'color' => '#ef4444'],
                        ['id' => 'term_neg', 'name' => '-', 'label' => '- (GND)', 'relX' => 5, 'relY' => 35, 'color' => '#0f172a'],
                    ]
                ],
                [
                    'id' => 'switch_spst-002',
                    'type' => 'switch_spst',
                    'name' => 'Saklar Rocker 2',
                    'x' => 360,
                    'y' => 120,
                    'rotation' => 0,
                    'width' => 130,
                    'height' => 75,
                    'properties' => ['isClosed' => true],
                    'terminals' => [
                        ['id' => 'term_1', 'name' => '1', 'label' => 'Pin Input (1)', 'relX' => 10, 'relY' => 37, 'color' => '#38bdf8'],
                        ['id' => 'term_2', 'name' => '2', 'label' => 'Pin Output (2)', 'relX' => 120, 'relY' => 37, 'color' => '#38bdf8'],
                    ]
                ],
                [
                    'id' => 'lamp-003',
                    'type' => 'lamp',
                    'name' => 'Lampu Pijar 3',
                    'x' => 560,
                    'y' => 160,
                    'rotation' => 0,
                    'width' => 110,
                    'height' => 80,
                    'properties' => ['ratedVoltage' => 12, 'ratedPower' => 20, 'resistance' => 7.2],
                    'terminals' => [
                        ['id' => 'term_pos', 'name' => '+', 'label' => 'Terminal Positif (+)', 'relX' => 105, 'relY' => 40, 'color' => '#ef4444'],
                        ['id' => 'term_neg', 'name' => '-', 'label' => 'Terminal Negatif (-)', 'relX' => 5, 'relY' => 40, 'color' => '#0f172a'],
                    ]
                ],
                [
                    'id' => 'lamp-004',
                    'type' => 'lamp',
                    'name' => 'Lampu Pijar 4',
                    'x' => 560,
                    'y' => 320,
                    'rotation' => 0,
                    'width' => 110,
                    'height' => 80,
                    'properties' => ['ratedVoltage' => 12, 'ratedPower' => 20, 'resistance' => 7.2],
                    'terminals' => [
                        ['id' => 'term_pos', 'name' => '+', 'label' => 'Terminal Positif (+)', 'relX' => 105, 'relY' => 40, 'color' => '#ef4444'],
                        ['id' => 'term_neg', 'name' => '-', 'label' => 'Terminal Negatif (-)', 'relX' => 5, 'relY' => 40, 'color' => '#0f172a'],
                    ]
                ]
            ],
            'connections' => [
                [
                    'id' => 'conn-001',
                    'from' => ['componentId' => 'battery-001', 'terminalId' => 'term_pos'],
                    'to' => ['componentId' => 'switch_spst-002', 'terminalId' => 'term_1'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ],
                [
                    'id' => 'conn-002',
                    'from' => ['componentId' => 'switch_spst-002', 'terminalId' => 'term_2'],
                    'to' => ['componentId' => 'lamp-003', 'terminalId' => 'term_pos'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ],
                [
                    'id' => 'conn-003',
                    'from' => ['componentId' => 'switch_spst-002', 'terminalId' => 'term_2'],
                    'to' => ['componentId' => 'lamp-004', 'terminalId' => 'term_pos'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ],
                [
                    'id' => 'conn-004',
                    'from' => ['componentId' => 'lamp-003', 'terminalId' => 'term_neg'],
                    'to' => ['componentId' => 'battery-001', 'terminalId' => 'term_neg'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ],
                [
                    'id' => 'conn-005',
                    'from' => ['componentId' => 'lamp-004', 'terminalId' => 'term_neg'],
                    'to' => ['componentId' => 'battery-001', 'terminalId' => 'term_neg'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ]
            ]
        ];

        $case4Circuit = [
            'version' => 1,
            'components' => [
                [
                    'id' => 'battery-001',
                    'type' => 'battery',
                    'name' => 'Baterai DC 1',
                    'x' => 160,
                    'y' => 240,
                    'rotation' => 0,
                    'width' => 140,
                    'height' => 70,
                    'properties' => ['voltage' => 12, 'internalResistance' => 0.05],
                    'terminals' => [
                        ['id' => 'term_pos', 'name' => '+', 'label' => '+ (12V)', 'relX' => 135, 'relY' => 35, 'color' => '#ef4444'],
                        ['id' => 'term_neg', 'name' => '-', 'label' => '- (GND)', 'relX' => 5, 'relY' => 35, 'color' => '#0f172a'],
                    ]
                ],
                [
                    'id' => 'switch_spst-002',
                    'type' => 'switch_spst',
                    'name' => 'Saklar Rocker 2',
                    'x' => 340,
                    'y' => 140,
                    'rotation' => 0,
                    'width' => 130,
                    'height' => 75,
                    'properties' => ['isClosed' => true],
                    'terminals' => [
                        ['id' => 'term_1', 'name' => '1', 'label' => 'Pin Input (1)', 'relX' => 10, 'relY' => 37, 'color' => '#38bdf8'],
                        ['id' => 'term_2', 'name' => '2', 'label' => 'Pin Output (2)', 'relX' => 120, 'relY' => 37, 'color' => '#38bdf8'],
                    ]
                ],
                [
                    'id' => 'diode-003',
                    'type' => 'diode',
                    'name' => 'Dioda 3',
                    'x' => 520,
                    'y' => 140,
                    'rotation' => 0,
                    'width' => 110,
                    'height' => 60,
                    'properties' => ['model' => '1N4007', 'forwardVoltageDrop' => 0.7],
                    'terminals' => [
                        ['id' => 'term_anode', 'name' => 'A', 'label' => 'Anoda (+)', 'relX' => 5, 'relY' => 30, 'color' => '#ef4444'],
                        ['id' => 'term_cathode', 'name' => 'K', 'label' => 'Katoda (-)', 'relX' => 105, 'relY' => 30, 'color' => '#0f172a'],
                    ]
                ],
                [
                    'id' => 'motor_dc-004',
                    'type' => 'motor_dc',
                    'name' => 'Motor DC 4',
                    'x' => 520,
                    'y' => 320,
                    'rotation' => 0,
                    'width' => 130,
                    'height' => 85,
                    'properties' => ['nominalVoltage' => 12, 'internalResistance' => 4.5, 'rpm' => 3000],
                    'terminals' => [
                        ['id' => 'term_pos', 'name' => '+', 'label' => 'Terminal Positif (+)', 'relX' => 15, 'relY' => 42, 'color' => '#ef4444'],
                        ['id' => 'term_neg', 'name' => '-', 'label' => 'Terminal Negatif (-)', 'relX' => 115, 'relY' => 42, 'color' => '#0f172a'],
                    ]
                ]
            ],
            'connections' => [
                [
                    'id' => 'conn-001',
                    'from' => ['componentId' => 'battery-001', 'terminalId' => 'term_pos'],
                    'to' => ['componentId' => 'switch_spst-002', 'terminalId' => 'term_1'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ],
                [
                    'id' => 'conn-002',
                    'from' => ['componentId' => 'switch_spst-002', 'terminalId' => 'term_2'],
                    'to' => ['componentId' => 'diode-003', 'terminalId' => 'term_cathode'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ],
                [
                    'id' => 'conn-003',
                    'from' => ['componentId' => 'diode-003', 'terminalId' => 'term_anode'],
                    'to' => ['componentId' => 'motor_dc-004', 'terminalId' => 'term_pos'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ],
                [
                    'id' => 'conn-004',
                    'from' => ['componentId' => 'motor_dc-004', 'terminalId' => 'term_neg'],
                    'to' => ['componentId' => 'battery-001', 'terminalId' => 'term_neg'],
                    'color' => '#f88c00',
                    'waypoints' => null
                ]
            ]
        ];

        CaseStudy::updateOrCreate(
            ['title' => 'Lampu Tidak Menyala'],
            [
                'description' => 'Sebuah rangkaian lampu pijar terhubung ke baterai 12V dan saklar SPST, namun lampu belum menyala. Lakukan inspeksi saklar dan analisis keterhubungan sirkuit tertutup.',
                'circuit_data' => json_encode($case1Circuit),
                'created_by' => $adminId,
            ]
        );

        CaseStudy::updateOrCreate(
            ['title' => 'Menentukan Nilai Resistor & Pembagian Tegangan'],
            [
                'description' => 'Menganalisis rangkaian resistor pembatas arus yang dipasang seri dengan lampu pijar. Hitung nilai hambatan (R) dan amati perubahan daya serta nyala redup lampu.',
                'circuit_data' => json_encode($case2Circuit),
                'created_by' => $adminId,
            ]
        );

        CaseStudy::updateOrCreate(
            ['title' => 'Rangkaian Paralel 2 Beban Mandiri'],
            [
                'description' => 'Rancang sistem penerangan 2 lampu paralel dengan titik simpul percabangan (node junction). Buktikan kedua lampu mendapatkan tegangan penuh 12V secara independen.',
                'circuit_data' => json_encode($case3Circuit),
                'created_by' => $adminId,
            ]
        );

        CaseStudy::updateOrCreate(
            ['title' => 'Dioda Semikonduktor — Motor Listrik Tidak Berputar'],
            [
                'description' => 'Saklar sudah ON dan baterai 12V aktif, namun Motor Listrik DC sama sekali TIDAK berputar (0 RPM). Periksa polaritas Dioda 1N4007 (saat ini Bias Mundur/Terblokir). Hubungkan secara Bias Maju agar motor berputar!',
                'circuit_data' => json_encode($case4Circuit),
                'created_by' => $adminId,
            ]
        );
    }
}

