<?php

namespace Database\Seeders;

use App\Models\Body;
use App\Models\BodyType;
use App\Models\Commune;
use App\Models\Core;
use App\Models\Function_;
use App\Models\Idiom;
use App\Models\Language;
use App\Models\LocalCommittee;
use App\Models\Member;
use App\Models\Municipality;
use App\Models\Neighborhood;
use App\Models\Province;
use App\Models\SectorialCommittee;
use App\Models\Structure;
use App\Models\Training;
use App\Models\User;
use App\Models\ZonalCommittee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedGeography();
        $this->seedOrganisations();
        $this->seedStructures();
        $this->seedMembers();
        $this->seedCatalogues();
    }

    private function seedUsers(): void
    {
        $users = [
            ['name' => 'Administrador Demo', 'email' => 'admin@sigepa.ao', 'role' => 'admin', 'is_active' => true],
            ['name' => 'Operador Demo', 'email' => 'operador@sigepa.ao', 'role' => 'operador', 'is_active' => true],
            ['name' => 'Visualizador Demo', 'email' => 'visualizador@sigepa.ao', 'role' => 'visualizador', 'is_active' => true],
            ['name' => 'Marta Quissanga', 'email' => 'marta@sigepa.ao', 'role' => 'operador', 'is_active' => false],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [...$user, 'password' => Hash::make('password123')],
            );
        }
    }

    private function seedGeography(): void
    {
        foreach ([
            ['id' => 'prov-luanda', 'name' => 'Luanda', 'code' => 'LD'],
            ['id' => 'prov-benguela', 'name' => 'Benguela', 'code' => 'BG'],
            ['id' => 'prov-huila', 'name' => 'Huíla', 'code' => 'HL'],
            ['id' => 'prov-huambo', 'name' => 'Huambo', 'code' => 'HB'],
            ['id' => 'prov-cabinda', 'name' => 'Cabinda', 'code' => 'CB'],
        ] as $province) {
            Province::query()->updateOrCreate(['id' => $province['id']], $province);
        }

        foreach ([
            ['id' => 'mun-belas', 'name' => 'Belas', 'province_id' => 'prov-luanda'],
            ['id' => 'mun-cacuaco', 'name' => 'Cacuaco', 'province_id' => 'prov-luanda'],
            ['id' => 'mun-lobito', 'name' => 'Lobito', 'province_id' => 'prov-benguela'],
            ['id' => 'mun-catumbela', 'name' => 'Catumbela', 'province_id' => 'prov-benguela'],
            ['id' => 'mun-lubango', 'name' => 'Lubango', 'province_id' => 'prov-huila'],
            ['id' => 'mun-huambo', 'name' => 'Huambo', 'province_id' => 'prov-huambo'],
            ['id' => 'mun-cabinda', 'name' => 'Cabinda', 'province_id' => 'prov-cabinda'],
        ] as $municipality) {
            Municipality::query()->updateOrCreate(['id' => $municipality['id']], $municipality);
        }

        foreach ([
            ['id' => 'com-camama', 'name' => 'Camama', 'municipality_id' => 'mun-belas'],
            ['id' => 'com-kikuxi', 'name' => 'Kikuxi', 'municipality_id' => 'mun-cacuaco'],
            ['id' => 'com-restinga', 'name' => 'Restinga', 'municipality_id' => 'mun-lobito'],
            ['id' => 'com-praia', 'name' => 'Praia do Bispo', 'municipality_id' => 'mun-lobito'],
        ] as $commune) {
            Commune::query()->updateOrCreate(['id' => $commune['id']], $commune);
        }

        foreach ([
            ['id' => 'bai-camama-1', 'name' => 'Camama I', 'commune_id' => 'com-camama'],
            ['id' => 'bai-camama-2', 'name' => 'Camama II', 'commune_id' => 'com-camama'],
            ['id' => 'bai-kikuxi-1', 'name' => 'Kikuxi I', 'commune_id' => 'com-kikuxi'],
        ] as $neighborhood) {
            Neighborhood::query()->updateOrCreate(['id' => $neighborhood['id']], $neighborhood);
        }
    }

    private function seedOrganisations(): void
    {
        foreach (['bt-nacional' => 'Órgão Nacional', 'bt-provincial' => 'Órgão Provincial', 'bt-municipal' => 'Órgão Municipal'] as $id => $name) {
            BodyType::query()->updateOrCreate(['id' => $id], ['name' => $name]);
        }

        foreach ([
            ['id' => 'body-cc', 'name' => 'Comité Central', 'body_type_id' => 'bt-nacional', 'member_count' => 45],
            ['id' => 'body-cp-ld', 'name' => 'Comité Provincial de Luanda', 'body_type_id' => 'bt-provincial', 'member_count' => 32],
            ['id' => 'body-cm-belas', 'name' => 'Comité Municipal de Belas', 'body_type_id' => 'bt-municipal', 'member_count' => 18],
        ] as $body) {
            Body::query()->updateOrCreate(['id' => $body['id']], $body);
        }

        foreach ([
            ['id' => 'fn-presidente', 'name' => 'Presidente', 'body_type_id' => 'bt-nacional'],
            ['id' => 'fn-sec-geral', 'name' => 'Secretário-Geral', 'body_type_id' => 'bt-nacional'],
            ['id' => 'fn-tesoureiro', 'name' => 'Tesoureiro', 'body_type_id' => 'bt-provincial'],
            ['id' => 'fn-secretario', 'name' => 'Secretário', 'body_type_id' => 'bt-municipal'],
        ] as $function) {
            Function_::query()->updateOrCreate(['id' => $function['id']], $function);
        }
    }

    private function seedStructures(): void
    {
        foreach ([
            ['id' => 1, 'name' => 'Assembleia Central', 'description' => 'Estrutura máxima de decisão', 'member_count' => 120],
            ['id' => 2, 'name' => 'Comité Provincial', 'description' => 'Coordenação provincial', 'member_count' => 45],
            ['id' => 3, 'name' => 'Comité Municipal', 'description' => 'Coordenação municipal', 'member_count' => 30],
        ] as $structure) {
            Structure::query()->updateOrCreate(['id' => $structure['id']], $structure);
        }

        foreach ([
            ['id' => 'lc-belas', 'name' => 'Comité Local de Belas', 'province_id' => 'prov-luanda', 'municipality_id' => 'mun-belas', 'member_count' => 20],
            ['id' => 'lc-cacuaco', 'name' => 'Comité Local de Cacuaco', 'province_id' => 'prov-luanda', 'municipality_id' => 'mun-cacuaco', 'member_count' => 14],
            ['id' => 'lc-lobito', 'name' => 'Comité Local do Lobito', 'province_id' => 'prov-benguela', 'municipality_id' => 'mun-lobito', 'member_count' => 9],
        ] as $committee) {
            LocalCommittee::query()->updateOrCreate(['id' => $committee['id']], $committee);
        }

        $committees = [
            ['provinceId' => 'prov-luanda', 'municipalityId' => 'mun-belas', 'memberCount' => 20],
            ['provinceId' => 'prov-benguela', 'municipalityId' => 'mun-lobito', 'memberCount' => 14],
            ['provinceId' => 'prov-huila', 'municipalityId' => 'mun-lubango', 'memberCount' => 8],
        ];

        foreach ([1 => 'Comité Sectorial', 2 => 'Comité Zonal'] as $index => $label) {
            $modelClass = $index === 1 ? SectorialCommittee::class : ZonalCommittee::class;
            $prefix = $index === 1 ? 'sc' : 'zc';

            foreach ($committees as $position => $committee) {
                $modelClass::query()->updateOrCreate(
                    ['id' => $prefix.'-'.($position + 1)],
                    [
                        'name' => "{$label} ".($position + 1),
                        'province_id' => $committee['provinceId'],
                        'municipality_id' => $committee['municipalityId'],
                        'member_count' => $committee['memberCount'],
                    ],
                );
            }
        }

        foreach ([
            ['id' => 'core-1', 'name' => 'Núcleo 1 — Belas', 'local_committee_id' => 'lc-belas', 'neighborhood_id' => 'bai-camama-1', 'member_count' => 12],
            ['id' => 'core-2', 'name' => 'Núcleo 2 — Cacuaco', 'local_committee_id' => 'lc-cacuaco', 'neighborhood_id' => 'bai-kikuxi-1', 'member_count' => 9],
            ['id' => 'core-3', 'name' => 'Núcleo 3 — Lobito', 'local_committee_id' => 'lc-lobito', 'neighborhood_id' => 'bai-camama-2', 'member_count' => 15],
        ] as $core) {
            Core::query()->updateOrCreate(['id' => $core['id']], $core);
        }
    }

    private function seedMembers(): void
    {
        $rows = [
            ['Ana Manuel Domingos', 'Female', 'prov-luanda', 'mun-belas', 'com-camama', 'bai-camama-1'],
            ['Bruno Kiala Ferreira', 'Male', 'prov-luanda', 'mun-cacuaco', 'com-kikuxi', 'bai-kikuxi-1'],
            ['Carla Nzinga Baptista', 'Female', 'prov-benguela', 'mun-lobito', 'com-restinga', 'bai-camama-2'],
            ['David Muanza Silva', 'Male', 'prov-luanda', 'mun-belas', 'com-camama', 'bai-camama-2'],
            ['Esperança Cassule Neto', 'Female', 'prov-huila', 'mun-lubango', '', ''],
            ['Fábio Tchipa Gomes', 'Male', 'prov-huambo', 'mun-huambo', '', ''],
            ['Gabriela Mbala Costa', 'Female', 'prov-cabinda', 'mun-cabinda', '', ''],
            ['Hélio Vunge Machado', 'Male', 'prov-luanda', 'mun-cacuaco', 'com-kikuxi', 'bai-kikuxi-1'],
            ['Inês Kapangala Dias', 'Female', 'prov-benguela', 'mun-catumbela', '', ''],
            ['João Kalunga Matos', 'Male', 'prov-luanda', 'mun-belas', 'com-camama', 'bai-camama-1'],
            ['Kátia Ndombele Rocha', 'Female', 'prov-huila', 'mun-lubango', '', ''],
            ['Luís Semba Andrade', 'Male', 'prov-huambo', 'mun-huambo', '', ''],
        ];

        $levels = ['Primário', 'Secundário', 'Médio', 'Superior'];
        $professions = ['Professor(a)', 'Enfermeiro(a)', 'Engenheiro(a)', 'Comerciante', 'Estudante'];

        foreach ($rows as $index => [$fullName, $gender, $provinceId, $municipalityId, $communeId, $neighbourhoodId]) {
            $firstName = explode(' ', $fullName)[0];
            $active = $index % 5 !== 0;

            Member::query()->updateOrCreate(
                ['id' => 'member-'.($index + 1)],
                [
                    'full_name' => $fullName,
                    'gender' => $gender,
                    'birth_date' => sprintf('19%02d-%02d-1%d', 70 + ($index % 25), ($index % 9) + 1, $index % 9),
                    'father_name' => 'Pai de '.$firstName,
                    'mother_name' => 'Mãe de '.$firstName,
                    'bi' => sprintf('00%06dLA0%d', 100000 + $index, $index),
                    'phone_number' => '9'.(23 + ($index % 6)).(100000 + $index * 137),
                    'email' => strtolower($firstName).'.'.$index.'@exemplo.ao',
                    'residence_province_id' => $provinceId,
                    'residence_municipality_id' => $municipalityId,
                    'residence_commune_id' => $communeId ?: null,
                    'residence_neighbourhood_id' => $neighbourhoodId ?: null,
                    'level' => $levels[$index % 4],
                    'profession' => $professions[$index % 5],
                    'admission_year' => (string) (1995 + ($index % 25)),
                    'status' => $active ? 'active' : 'inactive',
                    'is_active' => $active,
                ],
            );
        }
    }

    private function seedCatalogues(): void
    {
        foreach (['Kimbundu', 'Umbundu', 'Kikongo', 'Nganguela'] as $index => $name) {
            Idiom::query()->updateOrCreate(['id' => 'idiom-'.($index + 1)], ['name' => $name]);
        }

        foreach (['Português', 'Inglês', 'Francês'] as $index => $name) {
            Language::query()->updateOrCreate(['id' => 'lang-'.($index + 1)], ['name' => $name]);
        }

        foreach ([
            ['Formação Básica de Quadros', 'Básico'],
            ['Formação Intermédia de Gestão', 'Intermédio'],
            ['Formação Avançada de Liderança', 'Avançado'],
        ] as $index => [$name, $level]) {
            Training::query()->updateOrCreate(['id' => 'training-'.($index + 1)], ['name' => $name, 'level' => $level]);
        }
    }
}
