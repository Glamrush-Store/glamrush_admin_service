<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('category_product')) {
            Schema::create('category_product', function (Blueprint $table) {
                $table->ulid('id')->primary();
                $table->foreignUlid('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignUlid('category_id')->constrained('categories')->cascadeOnDelete();
                $table->boolean('is_primary')->default(false);
                $table->unsignedInteger('sequence')->default(0);
                $table->timestamps();

                $table->unique(['product_id', 'category_id']);
                $table->index(['category_id', 'sequence']);
                $table->index(['product_id', 'is_primary']);
            });
        }

        if (Schema::hasColumn('products', 'category_id')) {
            $now = now();

            DB::table('products')
                ->whereNotNull('category_id')
                ->select(['id', 'category_id', 'sort_order'])
                ->orderBy('id')
                ->chunk(500, function ($products) use ($now) {
                    foreach ($products as $product) {
                        DB::table('category_product')->updateOrInsert(
                            [
                                'product_id' => $product->id,
                                'category_id' => $product->category_id,
                            ],
                            [
                                'id' => (string) Illuminate\Support\Str::ulid(),
                                'is_primary' => true,
                                'sequence' => (int) ($product->sort_order ?? 0),
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]
                        );
                    }
                });

            $this->dropProductsCategoryForeignKeyIfExists();

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('category_id');
            });
        }

        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS category_product_one_primary_per_product ON category_product (product_id) WHERE is_primary = true');
    }

    private function dropProductsCategoryForeignKeyIfExists(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $constraints = DB::select(<<<'SQL'
                select con.conname
                from pg_constraint con
                join pg_class rel on rel.oid = con.conrelid
                join pg_namespace nsp on nsp.oid = rel.relnamespace
                join unnest(con.conkey) colnum on true
                join pg_attribute att on att.attrelid = rel.oid and att.attnum = colnum
                where con.contype = 'f'
                  and rel.relname = 'products'
                  and att.attname = 'category_id'
            SQL);

            foreach ($constraints as $constraint) {
                $name = str_replace('"', '""', $constraint->conname);
                DB::statement("ALTER TABLE products DROP CONSTRAINT IF EXISTS \"{$name}\"");
            }

            return;
        }

        if ($driver === 'mysql') {
            $constraints = DB::select(<<<'SQL'
                select constraint_name
                from information_schema.key_column_usage
                where table_schema = database()
                  and table_name = 'products'
                  and column_name = 'category_id'
                  and referenced_table_name is not null
            SQL);

            foreach ($constraints as $constraint) {
                $name = str_replace('`', '``', $constraint->constraint_name);
                DB::statement("ALTER TABLE products DROP FOREIGN KEY `{$name}`");
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignUlid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            });

            DB::table('category_product')
                ->where('is_primary', true)
                ->orderBy('product_id')
                ->chunk(500, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('products')
                            ->where('id', $row->product_id)
                            ->update(['category_id' => $row->category_id]);
                    }
                });
        }

        DB::statement('DROP INDEX IF EXISTS category_product_one_primary_per_product');
        Schema::dropIfExists('category_product');
    }
};


