<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlignDashboardBuilderSprint6A extends Migration
{
    public function up()
    {
        // Sprint 6A hotfix:
        // Mevcut veritabanı şemasını kaynak gerçeklik kabul ediyoruz.
        // Bu migration bu turda tablo/kolon rename, create veya drop yapmaz.
    }

    public function down()
    {
        // No-op
    }
}
