<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --------------------
        // General Settings
        // --------------------
        $generalSettings = [
            [
                'key' => 'site_name',
                'value' => 'BUBT Nexus',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Site Name',
                'order' => 1,
            ],
            [
                'key' => 'site_title',
                'value' => 'BUBT Nexus - Smart Solutions for BUBT Students',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Site Title',
                'order' => 2,
            ],
            [
                'key' => 'site_url',
                'value' => 'https://www.travel.octosyncsoftware.com',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Site URL',
                'order' => 3,
            ],
            [
                'key' => 'site_email',
                'value' => 'info@octosyncsoftware.com',
                'type' => 'email',
                'group' => 'general',
                'label' => 'Site Email',
                'order' => 4,
            ],
            [
                'key' => 'site_phone',
                'value' => '+8801621833839',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Phone Number',
                'order' => 5,
            ],
            [
                'key' => 'site_address',
                'value' => 'Hemayetpur, Savar, Dhaka',
                'type' => 'textarea',
                'group' => 'general',
                'label' => 'Address',
                'order' => 6,
            ],
            [
                'key' => 'site_logo',
                'value' => null,
                'type' => 'image',
                'group' => 'general',
                'label' => 'Site Logo',
                'order' => 7,
            ],
            [
                'key' => 'site_favicon',
                'value' => null,
                'type' => 'image',
                'group' => 'general',
                'label' => 'Favicon',
                'order' => 8,
            ],
        ];

        foreach ($generalSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key'], 'group' => $setting['group']],
                $setting
            );
        }

        // --------------------
        // Social Media
        // --------------------
        $socialSettings = [
            ['key' => 'facebook_url', 'value' => 'https://www.facebook.com/octosyncsoftwareltd', 'type' => 'text', 'group' => 'social', 'label' => 'Facebook URL', 'order' => 1],
            ['key' => 'github_url', 'value' => null, 'type' => 'text', 'group' => 'social', 'label' => 'GitHub URL', 'order' => 4],
            ['key' => 'twitter_url', 'value' => null, 'type' => 'text', 'group' => 'social', 'label' => 'Twitter URL', 'order' => 5],
            ['key' => 'youtube_url', 'value' => null, 'type' => 'text', 'group' => 'social', 'label' => 'Youtube URL', 'order' => 6],
            ['key' => 'linkedin_url', 'value' => null, 'type' => 'text', 'group' => 'social', 'label' => 'LinkedIn URL', 'order' => 7],
            ['key' => 'whatsapp_number', 'value' => '8801621833839', 'type' => 'text', 'group' => 'social', 'label' => 'WhatsApp Number', 'order' => 8],
            ['key' => 'whatsapp_message', 'value' => 'Hello! I have a question about your products.', 'type' => 'text', 'group' => 'social', 'label' => 'Default WhatsApp Message', 'order' => 9],
            ['key' => 'whatsapp_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'social', 'label' => 'Enable WhatsApp Button', 'order' => 10],
        ];

        foreach ($socialSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key'], 'group' => $setting['group']],
                $setting
            );
        }

        // --------------------
        // SEO & Meta Tags
        // --------------------
        $seoSettings = [
            ['key' => 'meta_description', 'value' => null, 'type' => 'textarea', 'group' => 'seo', 'label' => 'Meta Description', 'order' => 1],
            ['key' => 'meta_keywords', 'value' => null, 'type' => 'textarea', 'group' => 'seo', 'label' => 'Meta Keywords', 'order' => 2],
            ['key' => 'meta_author', 'value' => null, 'type' => 'text', 'group' => 'seo', 'label' => 'Meta Author', 'order' => 3],
            ['key' => 'meta_language', 'value' => 'English', 'type' => 'text', 'group' => 'seo', 'label' => 'Meta Language', 'order' => 4],
        ];

        foreach ($seoSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key'], 'group' => $setting['group']],
                $setting
            );
        }

        // --------------------
        // Open Graph (OG Tags)
        // --------------------
        $ogSettings = [
            ['key' => 'og_title', 'value' => null, 'type' => 'text', 'group' => 'opengraph', 'label' => 'OG Title', 'order' => 1],
            ['key' => 'og_description', 'value' => null, 'type' => 'textarea', 'group' => 'opengraph', 'label' => 'OG Description', 'order' => 2],
            ['key' => 'og_image', 'value' => null, 'type' => 'image', 'group' => 'opengraph', 'label' => 'OG Image', 'order' => 3],
            ['key' => 'og_url', 'value' => null, 'type' => 'text', 'group' => 'opengraph', 'label' => 'OG URL', 'order' => 4],
            ['key' => 'og_type', 'value' => 'website', 'type' => 'text', 'group' => 'opengraph', 'label' => 'OG Type', 'order' => 5],
            ['key' => 'fb_app_id', 'value' => null, 'type' => 'text', 'group' => 'opengraph', 'label' => 'Facebook App ID', 'order' => 6],
        ];

        foreach ($ogSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key'], 'group' => $setting['group']],
                $setting
            );
        }

        // --------------------
        // Twitter Card
        // --------------------
        $twitterSettings = [
            ['key' => 'twitter_card', 'value' => 'summary_large_image', 'type' => 'text', 'group' => 'twitter', 'label' => 'Twitter Card Type', 'order' => 1],
            ['key' => 'twitter_title', 'value' => null, 'type' => 'text', 'group' => 'twitter', 'label' => 'Twitter Title', 'order' => 2],
            ['key' => 'twitter_description', 'value' => null, 'type' => 'textarea', 'group' => 'twitter', 'label' => 'Twitter Description', 'order' => 3],
            ['key' => 'twitter_image', 'value' => null, 'type' => 'image', 'group' => 'twitter', 'label' => 'Twitter Image', 'order' => 4],
        ];

        foreach ($twitterSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key'], 'group' => $setting['group']],
                $setting
            );
        }

        // --------------------
        // Mail Settings
        // --------------------
        $mailSettings = [
            [
                'key' => 'mail_mailer',
                'value' => env('MAIL_MAILER', 'smtp'),
                'type' => 'text',
                'group' => 'mail',
                'label' => 'Mail Mailer',
                'order' => 1,
            ],
            [
                'key' => 'mail_host',
                'value' => env('MAIL_HOST', 'smtp.example.com'),
                'type' => 'text',
                'group' => 'mail',
                'label' => 'Mail Host',
                'order' => 2,
            ],
            [
                'key' => 'mail_port',
                'value' => env('MAIL_PORT', '587'),
                'type' => 'text',
                'group' => 'mail',
                'label' => 'Mail Port',
                'order' => 3,
            ],
            [
                'key' => 'mail_username',
                'value' => env('MAIL_USERNAME', null),
                'type' => 'text',
                'group' => 'mail',
                'label' => 'Mail Username',
                'order' => 4,
            ],
            [
                'key' => 'mail_password',
                'value' => env('MAIL_PASSWORD', null),
                'type' => 'text',
                'group' => 'mail',
                'label' => 'Mail Password',
                'order' => 5,
            ],
            [
                'key' => 'mail_encryption',
                'value' => env('MAIL_ENCRYPTION', null),
                'type' => 'text',
                'group' => 'mail',
                'label' => 'Mail Encryption',
                'order' => 6,
            ],
            [
                'key' => 'mail_from_address',
                'value' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'type' => 'text',
                'group' => 'mail',
                'label' => 'Mail From Address',
                'order' => 7,
            ],
            [
                'key' => 'mail_from_name',
                'value' => env('MAIL_FROM_NAME', config('app.name')),
                'type' => 'text',
                'group' => 'mail',
                'label' => 'Mail From Name',
                'order' => 8,
            ],
        ];

        foreach ($mailSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key'], 'group' => $setting['group']],
                $setting
            );
        }
    }
}
