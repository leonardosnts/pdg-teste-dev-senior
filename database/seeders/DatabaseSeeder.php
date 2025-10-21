<?php

namespace Database\Seeders;

use App\Models\AlertChannel;
use App\Models\ExpeditionObservation;
use App\Models\ExpeditionReport;
use App\Models\InstrumentCalibration;
use App\Models\MarineLab;
use App\Models\SalinitySurvey;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Atlas Coordinator',
            'email' => 'atlas.coordinator@example.com',
        ]);

        $lab = MarineLab::query()->create([
            'name' => 'Pelagic Microplastics Observatory',
            'alias' => 'pelagic-lab',
            'ocean_basin' => 'South Atlantic Gyre',
            'metadata' => [
                'coordinator_id' => $admin->id,
                'contact' => 'pelagic.ops@example.com',
            ],
        ]);

        $reports = collect([
            ['expedition_code' => 'XP-441', 'region' => 'Tristan Ridge', 'anomaly_score' => 87],
            ['expedition_code' => 'XP-522', 'region' => 'Tristan Ridge', 'anomaly_score' => 93],
            ['expedition_code' => 'XP-611', 'region' => 'Walvis Corridor', 'anomaly_score' => 74],
            ['expedition_code' => 'XP-702', 'region' => 'Tristan Ridge', 'anomaly_score' => 81],
            ['expedition_code' => 'XP-815', 'region' => 'Tristan Ridge', 'anomaly_score' => 95],
            ['expedition_code' => 'XP-923', 'region' => 'Walvis Corridor', 'anomaly_score' => 68],
            ['expedition_code' => 'XP-1044', 'region' => 'Tristan Ridge', 'anomaly_score' => 89],
            ['expedition_code' => 'XP-1121', 'region' => 'Tristan Ridge', 'anomaly_score' => 77],
            ['expedition_code' => 'XP-1245', 'region' => 'Benguela Current', 'anomaly_score' => 91],
            ['expedition_code' => 'XP-1312', 'region' => 'Tristan Ridge', 'anomaly_score' => 84],
            ['expedition_code' => 'XP-1456', 'region' => 'Tristan Ridge', 'anomaly_score' => 92],
            ['expedition_code' => 'XP-1587', 'region' => 'Walvis Corridor', 'anomaly_score' => 79],
            ['expedition_code' => 'XP-1621', 'region' => 'Tristan Ridge', 'anomaly_score' => 88],
            ['expedition_code' => 'XP-1739', 'region' => 'Tristan Ridge', 'anomaly_score' => 96],
            ['expedition_code' => 'XP-1842', 'region' => 'Tristan Ridge', 'anomaly_score' => 85],
        ])->map(fn($data) => ExpeditionReport::query()->create([
            'marine_lab_id' => $lab->id,
            'expedition_code' => $data['expedition_code'],
            'region' => $data['region'],
            'anomaly_score' => $data['anomaly_score'],
            'metadata' => ['sampling_window' => now()->subDays(random_int(20, 90))->toDateString()],
        ]));

        $reports->each(function (ExpeditionReport $report) {
            $observationCount = random_int(2, 5);
            collect(range(1, $observationCount))->each(function () use ($report) {
                ExpeditionObservation::query()->create([
                    'expedition_report_id' => $report->id,
                    'instrument' => collect(['CTD', 'ADCP', 'LISST', 'FlowCam', 'Niskin'])->random() . '-' . Str::random(3),
                    'summary' => collect([
                        'Profiling run across thermocline layers.',
                        'Acoustic scan for suspended microplastics.',
                        'Particle size distribution analysis.',
                        'Water column fluorescence measurement.',
                        'Discrete sample collection at depth.',
                    ])->random(),
                    'sample_count' => random_int(5, 21),
                ]);
            });
        });

        collect(range(1, 9))->each(function (int $index) use ($lab) {
            SalinitySurvey::query()->create([
                'marine_lab_id' => $lab->id,
                'transect' => 'T-' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'surface_psu' => 35.2 + $index / 100,
                'mid_psu' => 34.7 + $index / 110,
                'deep_psu' => 34.4 + $index / 95,
                'surveyed_at' => now()->subWeeks($index),
            ]);
        });

        InstrumentCalibration::query()->create([
            'marine_lab_id' => $lab->id,
            'instrument' => 'LISST-Deep',
            'drift_ppm' => 2.731,
            'validated_at' => now()->subDays(12),
            'payload' => ['operator' => 'JP-14', 'drift_window' => 'P3M'],
        ]);

        collect([
            ['channel' => 'email', 'endpoint' => 'alerts@pelagic.example.com'],
            ['channel' => 'sms', 'endpoint' => '+5521999990000'],
            ['channel' => 'satellite', 'endpoint' => 'GSAT-4421'],
        ])->each(fn($channel) => AlertChannel::query()->create([
            'marine_lab_id' => $lab->id,
            'channel' => $channel['channel'],
            'endpoint' => $channel['endpoint'],
            'constraints' => ['priority' => 'high', 'batch' => false],
        ]));
    }
}
