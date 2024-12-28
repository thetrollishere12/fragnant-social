<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;


class WebsiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $settings = [
            [
                'id' => 1,
                'block_key' => 'SOCIAL_MEDIA_FACEBOOK',
                'block_type' => 'string',
                'block_value' => null,
                'created_at' => '2024-08-29 08:50:40',
                'updated_at' => '2024-08-29 08:53:27',
            ],
            [
                'id' => 2,
                'block_key' => 'SOCIAL_MEDIA_PINTEREST',
                'block_type' => 'string',
                'block_value' => null,
                'created_at' => '2024-08-29 08:50:44',
                'updated_at' => '2024-08-29 08:58:06',
            ],
            [
                'id' => 3,
                'block_key' => 'SOCIAL_MEDIA_YOUTUBE',
                'block_type' => 'string',
                'block_value' => null,
                'created_at' => '2024-08-29 08:50:47',
                'updated_at' => '2024-08-29 08:50:47',
            ],
            [
                'id' => 4,
                'block_key' => 'SOCIAL_MEDIA_WHATSAPP',
                'block_type' => 'string',
                'block_value' => null,
                'created_at' => '2024-08-29 08:50:51',
                'updated_at' => '2024-08-29 08:50:51',
            ],
            [
                'id' => 5,
                'block_key' => 'SOCIAL_MEDIA_TIKTOK',
                'block_type' => 'string',
                'block_value' => null,
                'created_at' => '2024-08-29 08:50:55',
                'updated_at' => '2024-08-29 08:53:52',
            ],
            [
                'id' => 6,
                'block_key' => 'SOCIAL_MEDIA_DISCORD',
                'block_type' => 'string',
                'block_value' => null,
                'created_at' => '2024-08-29 08:50:59',
                'updated_at' => '2024-08-29 08:50:59',
            ],
            [
                'id' => 7,
                'block_key' => 'HEADER_MESSAGE',
                'block_type' => 'string',
                'block_value' => null,
                'created_at' => '2024-08-29 08:51:38',
                'updated_at' => '2024-11-02 01:11:05',
            ],
            [
                'id' => 8,
                'block_key' => 'SOCIAL_MEDIA_INSTAGRAM',
                'block_type' => 'string',
                'block_value' => null,
                'created_at' => '2024-08-29 08:51:49',
                'updated_at' => '2024-08-29 08:58:01',
            ],
            [
                'id' => 9,
                'block_key' => 'SOCIAL_MEDIA_ETSY',
                'block_type' => 'string',
                'block_value' => null,
                'created_at' => '2024-08-29 08:51:56',
                'updated_at' => '2024-08-29 08:51:56',
            ],
            [
                'id' => 10,
                'block_key' => 'RECEIPT_EMAIL',
                'block_type' => 'string',
                'block_value' => 'brandonsanghuynh123@gmail.com',
                'created_at' => '2024-08-29 08:52:14',
                'updated_at' => '2024-08-29 08:52:14',
            ],
            [
                'id' => 11,
                'block_key' => 'HEADER_CLASS_COLOR',
                'block_type' => 'string',
                'block_value' => 'main-bg-c',
                'created_at' => '2024-08-29 08:52:36',
                'updated_at' => '2024-08-29 08:52:36',
            ],
            [
                'id' => 12,
                'block_key' => 'WEBSITE_LOGIN',
                'block_type' => 'boolean',
                'block_value' => 'TRUE',
                'created_at' => '2024-09-06 03:31:19',
                'updated_at' => '2024-11-10 18:45:04',
            ],
            [
                'id' => 13,
                'block_key' => 'HEADER_REDIRECT',
                'block_type' => 'string',
                'block_value' => '/register?ref=header_message',
                'created_at' => '2024-10-25 18:45:36',
                'updated_at' => '2024-10-25 18:45:36',
            ],[
                'id' => 14,
                'block_key' => 'PAYMENT_METHOD_PAYPAL',
                'block_type' => 'boolean',
                'block_value' => null,
                'created_at' => '2024-10-25 18:45:36',
                'updated_at' => '2024-10-25 18:45:36',
            ],
        ];

        DB::table('website_blocks')->insert($settings);
    }
}
