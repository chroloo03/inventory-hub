<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('inventory_items')->insert([

            // ── IT Equipment ────────────────────────────────────────────────
            [
                'name'       => 'Dell XPS 15 Laptop',
                'category'   => 'laptop',
                'status'     => 'available',
                'quantity'   => 3,
                'attributes' => json_encode([
                    'brand'       => 'Dell',
                    'model'       => 'XPS 15 9530',
                    'cpu'         => 'Intel Core i9-13900H',
                    'ram_gb'      => 32,
                    'storage_gb'  => 1000,
                    'gpu'         => 'NVIDIA RTX 4060',
                    'screen_inch' => 15.6,
                    'os'          => 'Windows 11 Pro',
                    'year'        => 2023,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ── Monitor ─────────────────────────────────────────────────────
            [
                'name'       => 'LG 27GN950-B UltraGear Monitor',
                'category'   => 'monitor',
                'status'     => 'available',
                'quantity'   => 5,
                'attributes' => json_encode([
                    'brand'           => 'LG',
                    'screen_inch'     => 27,
                    'resolution'      => '3840x2160',
                    'refresh_rate_hz' => 144,
                    'panel_type'      => 'Nano IPS',
                    'hdr'             => 'HDR600',
                    'response_ms'     => 1,
                    'ports'           => ['HDMI 2.1', 'DisplayPort 1.4', 'USB-C'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ── Textbook ────────────────────────────────────────────────────
            [
                'name'       => 'Laravel: Up & Running',
                'category'   => 'book',
                'status'     => 'available',
                'quantity'   => 8,
                'attributes' => json_encode([
                    'author'      => 'Matt Stauffer',
                    'isbn'        => '978-1098153281',
                    'edition'     => 3,
                    'year'        => 2023,
                    'publisher'   => "O'Reilly Media",
                    'pages'       => 620,
                    'topics'      => ['PHP', 'Laravel', 'web routing', 'Eloquent ORM', 'REST APIs'],
                    'language'    => 'English',
                    'format'      => 'Hardcover',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ── Office Furniture ────────────────────────────────────────────
            [
                'name'       => 'Herman Miller Aeron Chair',
                'category'   => 'furniture',
                'status'     => 'available',
                'quantity'   => 12,
                'attributes' => json_encode([
                    'brand'       => 'Herman Miller',
                    'type'        => 'ergonomic chair',
                    'size'        => 'B (Medium)',
                    'color'       => 'Graphite',
                    'adjustable'  => true,
                    'lumbar'      => true,
                    'armrests'    => '4D adjustable',
                    'weight_kg'   => 19.5,
                    'max_load_kg' => 135,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ── Vehicle ─────────────────────────────────────────────────────
            [
                'name'       => 'Toyota Hiace Service Van',
                'category'   => 'vehicle',
                'status'     => 'available',
                'quantity'   => 2,
                'attributes' => json_encode([
                    'brand'        => 'Toyota',
                    'model'        => 'Hiace',
                    'year'         => 2022,
                    'fuel'         => 'Diesel',
                    'seats'        => 12,
                    'plate'        => 'XYZ-1234',
                    'mileage_km'   => 18500,
                    'last_service' => '2024-11-01',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
