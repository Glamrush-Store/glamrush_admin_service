<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            '
            CREATE UNIQUE INDEX one_default_variant_per_product
            ON product_variants (product_id)
            WHERE is_default = true
        '
        );

        // this allows to use null for sku on product table
        // use this for mysql or any other database that doesn't support unique index with null values'

        //        DB::statement(
        //            "CREATE UNIQUE INDEX products_sku_unique
        //                    ON products (sku)
        //                    WHERE sku IS NOT NULL
        //                    "
        //        );
    }

    public function down(): void
    {
        DB::statement(
            '
            DROP INDEX IF EXISTS one_default_variant_per_product
        '
        );

        //        DB::statement(
        //            "
        //            DROP INDEX IF EXISTS products_sku_unique
        //        "
        //        );
    }
};
