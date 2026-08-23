<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'module_number' => 1,
                'title' => 'Tegangan Listrik (Voltage)',
                'slug' => 'tegangan-listrik',
                'description' => 'Memahami konsep beda potensial listrik, gaya gerak listrik (GGL), polaritas kutub positif/negatif, dan satuan Volt (V) pada sumber DC.',
                'icon' => 'zap',
            ],
            [
                'module_number' => 2,
                'title' => 'Hambatan Listrik (Resistance)',
                'slug' => 'hambatan-listrik',
                'description' => 'Konsep hambatan jenis konduktor, Hukum Ohm (V = I x R), karakteristik beban resistif, dan pembagian arus/tegangan.',
                'icon' => 'activity',
            ],
            [
                'module_number' => 3,
                'title' => 'Multimeter Digital & Analog',
                'slug' => 'multimeter',
                'description' => 'Cara kerja Voltmeter (pemasangan paralel), Amperemeter (pemasangan seri), Ohmmeter, serta kalibrasi dan keselamatan kerja.',
                'icon' => 'gauge',
            ],
        ];

        foreach ($modules as $mod) {
            Module::updateOrCreate(
                ['slug' => $mod['slug']],
                $mod
            );
        }
    }
}
