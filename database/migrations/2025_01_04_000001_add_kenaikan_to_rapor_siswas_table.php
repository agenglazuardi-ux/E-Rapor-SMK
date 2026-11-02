<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rapor_siswas', function (Blueprint $table) {
            if (! Schema::hasColumn('rapor_siswas', 'keputusan')) {
                $table->string('keputusan', 10)->nullable()->after('catatan_wali'); // naik | tinggal | lulus
            }
            if (! Schema::hasColumn('rapor_siswas', 'kelas_tujuan')) {
                $table->string('kelas_tujuan', 100)->nullable()->after('keputusan'); // nama kelas tujuan
            }
        });
    }

    public function down(): void
    {
        Schema::table('rapor_siswas', function (Blueprint $table) {
            foreach (['keputusan', 'kelas_tujuan'] as $c) {
                if (Schema::hasColumn('rapor_siswas', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
