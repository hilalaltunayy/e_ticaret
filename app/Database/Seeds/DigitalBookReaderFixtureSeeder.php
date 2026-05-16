<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DigitalBookReaderFixtureSeeder extends Seeder
{
    private const TARGET_PRODUCT_ID = '54b2b831-bdfb-4a82-8e93-4d2cf96032ba';
    private const CHARS_PER_PAGE = 1400;

    public function run()
    {
        $db = \Config\Database::connect();

        $product = $db->table('products')
            ->select('id, product_name, type, description')
            ->where('id', self::TARGET_PRODUCT_ID)
            ->where('deleted_at', null)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (! is_array($product)) {
            echo "DigitalBookReaderFixtureSeeder: hedef urun bulunamadi, islem yapilmadi.\n";
            return;
        }

        $type = strtolower(trim((string) ($product['type'] ?? '')));
        if (! in_array($type, ['dijital', 'digital', 'ebook', 'e-book'], true)) {
            echo "DigitalBookReaderFixtureSeeder: hedef urun dijital degil, islem yapilmadi.\n";
            return;
        }

        $oldDescription = (string) ($product['description'] ?? '');
        $newDescription = $this->buildLongTurkishFixtureText();

        $db->table('products')
            ->where('id', self::TARGET_PRODUCT_ID)
            ->update([
                'description' => $newDescription,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $oldLen = strlen($oldDescription);
        $newLen = strlen($newDescription);
        $estimatedPages = (int) ceil(max(1, $newLen) / self::CHARS_PER_PAGE);

        echo "DigitalBookReaderFixtureSeeder: urun description guncellendi.\n";
        echo " - product_id: " . self::TARGET_PRODUCT_ID . "\n";
        echo " - old_len: " . $oldLen . "\n";
        echo " - new_len: " . $newLen . "\n";
        echo " - estimated_pages: " . $estimatedPages . "\n";
    }

    private function buildLongTurkishFixtureText(): string
    {
        $sections = [];

        for ($page = 1; $page <= 10; $page++) {
            $sectionTitle = "Sayfa {$page} - Test Okuma Bolumu";
            $paragraphs = [];

            for ($i = 1; $i <= 3; $i++) {
                $paragraphs[] = "Bu test bolumu okuyucu panelinin sayfa gecis davranisini dogrulamak icin hazirlanmistir. "
                    . "Bolum {$page}, paragraf {$i} icinde karakter ritmi, cumle uzunlugu ve bosluk dagilimi kasitli olarak dengelenir. "
                    . "Kullanici onceki ve sonraki dugmeleriyle gezerken icerik akisi bozulmadan kalmali, satirlar kesintisiz okunmali ve panel stabil kalmalidir. "
                    . "Bu metin telifsiz bir test icerigidir; uygulamanin yalnizca teknik dogrulama amacina hizmet eder.";
            }

            $sections[] = $sectionTitle . "\n\n" . implode("\n\n", $paragraphs);
        }

        return implode("\n\n---\n\n", $sections);
    }
}
