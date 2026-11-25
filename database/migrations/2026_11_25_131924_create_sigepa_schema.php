<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $catalogues = [
            'provinces' => ['code' => 'string'],
            'municipalities' => ['province_id' => 'string'],
            'communes' => ['municipality_id' => 'string'],
            'neighborhoods' => ['commune_id' => 'string'],
            'body_types' => [],
            'bodies' => ['body_type_id' => 'string', 'member_count' => 'integer'],
            'functions' => ['body_type_id' => 'string'],
            'local_committees' => [
                'province_id' => 'string',
                'municipality_id' => 'string',
                'member_count' => 'integer',
            ],
            'sectorial_committees' => [
                'province_id' => 'string',
                'municipality_id' => 'string',
                'member_count' => 'integer',
            ],
            'zonal_committees' => [
                'province_id' => 'string',
                'municipality_id' => 'string',
                'member_count' => 'integer',
            ],
            'cores' => [
                'local_committee_id' => 'string',
                'neighborhood_id' => 'string',
                'member_count' => 'integer',
            ],
            'idioms' => [],
            'languages' => [],
            'trainings' => ['level' => 'string'],
        ];

        foreach ($catalogues as $tableName => $extraColumns) {
            Schema::create($tableName, function (Blueprint $table) use ($extraColumns): void {
                $table->string('id')->primary();
                $table->string('name');
                $table->text('description')->nullable();

                foreach ($extraColumns as $column => $type) {
                    if ($type === 'integer') {
                        $table->integer($column)->default(0);
                    } else {
                        $table->string($column)->nullable();
                    }
                }

                $table->boolean('is_active')->default(true);
                $table->timestamps();

                foreach (array_keys($extraColumns) as $column) {
                    $table->index($column);
                }
            });
        }

        // Estruturas usam IDs numéricos, tal como o contrato do frontend.
        Schema::create('structures', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('member_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('members', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('full_name');
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('bi')->nullable()->index();
            $table->date('id_emit_date')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('birth_location')->nullable();
            $table->string('birth_commune')->nullable();
            $table->string('municipality_card')->nullable();
            $table->date('card_emit_date')->nullable();
            $table->string('residence_province_id')->nullable()->index();
            $table->string('residence_municipality_id')->nullable()->index();
            $table->string('residence_commune_id')->nullable()->index();
            $table->string('residence_neighbourhood_id')->nullable()->index();
            $table->string('level')->nullable();
            $table->string('grade_or_year')->nullable();
            $table->string('qualification')->nullable();
            $table->string('profession')->nullable();
            $table->string('admission_year')->nullable();
            $table->string('admission_place')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_active')->default(true);
            $table->string('member_card')->nullable();
            $table->date('member_card_emit_date')->nullable();
            $table->boolean('was_militar')->default(false);
            $table->string('militar_party')->nullable();
            $table->boolean('was_member_from_another_party')->default(false);
            $table->string('previous_party')->nullable();
            $table->timestamps();
        });

        Schema::create('api_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('token_hash')->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
        Schema::dropIfExists('members');
        Schema::dropIfExists('structures');

        foreach (array_keys([
            'provinces', 'municipalities', 'communes', 'neighborhoods',
            'body_types', 'bodies', 'functions', 'local_committees',
            'sectorial_committees', 'zonal_committees', 'cores',
            'idioms', 'languages', 'trainings',
        ]) as $tableName) {
            Schema::dropIfExists($tableName);
        }
    }
};
