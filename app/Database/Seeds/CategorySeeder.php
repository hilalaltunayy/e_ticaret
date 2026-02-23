<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $table = 'categories';
        $fields = $db->getFieldNames($table);
        $hasId = in_array('id', $fields, true);
        $hasCreatedAt = in_array('created_at', $fields, true);

        $categories = [
            'Roman',
            'Dergi',
            'Biyografi',
            'Fantastik',
            'Şiir',
            'Karikatür',
        ];

        foreach ($categories as $name) {
            $exists = $db->table($table)
                ->where('category_name', $name)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $data = [
                'category_name' => $name,
            ];

            if ($hasId) {
                $data['id'] = $this->uuidV4();
            }

            if ($hasCreatedAt) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }

            $db->table($table)->insert($data);
        }
    }

    private function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
