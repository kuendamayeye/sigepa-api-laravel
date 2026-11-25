<?php

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

return [
    /*
     |--------------------------------------------------------------------------
     | Recursos da API SIGEPA
     |--------------------------------------------------------------------------
     |
     | Cada chave corresponde ao segmento da rota /api/v1/{recurso}. As regras
     | usam os nomes de campos do frontend (camelCase); o controlador converte
     | automaticamente para as colunas snake_case da base de dados.
     |
     */
    'resources' => [
        'provinces' => [
            'model' => Province::class,
            'label' => 'província',
            'rules' => [
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:10',
                'description' => 'nullable|string',
                'isActive' => 'boolean',
            ],
        ],
        'municipalities' => [
            'model' => Municipality::class,
            'label' => 'município',
            'rules' => [
                'name' => 'required|string|max:255',
                'provinceId' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'isActive' => 'boolean',
            ],
        ],
        'communes' => [
            'model' => Commune::class,
            'label' => 'comuna',
            'rules' => [
                'name' => 'required|string|max:255',
                'municipalityId' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'isActive' => 'boolean',
            ],
        ],
        'neighborhoods' => [
            'model' => Neighborhood::class,
            'label' => 'bairro',
            'rules' => [
                'name' => 'required|string|max:255',
                'communeId' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'isActive' => 'boolean',
            ],
        ],
        'body-types' => [
            'model' => BodyType::class,
            'label' => 'tipo de órgão',
            'rules' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'isActive' => 'boolean',
            ],
        ],
        'bodies' => [
            'model' => Body::class,
            'label' => 'órgão',
            'rules' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'bodyTypeId' => 'nullable|string|max:255',
                'memberCount' => 'nullable|integer|min:0',
                'isActive' => 'boolean',
            ],
        ],
        'functions' => [
            'model' => Function_::class,
            'label' => 'função',
            'rules' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'bodyTypeId' => 'nullable|string|max:255',
                'isActive' => 'boolean',
            ],
        ],
        'structures' => [
            'model' => Structure::class,
            'label' => 'estrutura',
            'rules' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'memberCount' => 'nullable|integer|min:0',
                'isActive' => 'boolean',
            ],
        ],
        'cores' => [
            'model' => Core::class,
            'label' => 'núcleo',
            'rules' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'localCommitteeId' => 'nullable|string|max:255',
                'neighborhoodId' => 'nullable|string|max:255',
                'memberCount' => 'nullable|integer|min:0',
                'isActive' => 'boolean',
            ],
        ],
        'local-committees' => [
            'model' => LocalCommittee::class,
            'label' => 'comité local',
            'rules' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'provinceId' => 'nullable|string|max:255',
                'municipalityId' => 'nullable|string|max:255',
                'memberCount' => 'nullable|integer|min:0',
                'isActive' => 'boolean',
            ],
        ],
        'sectorial-committees' => [
            'model' => SectorialCommittee::class,
            'label' => 'comité sectorial',
            'rules' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'provinceId' => 'nullable|string|max:255',
                'municipalityId' => 'nullable|string|max:255',
                'memberCount' => 'nullable|integer|min:0',
                'isActive' => 'boolean',
            ],
        ],
        'zonal-committees' => [
            'model' => ZonalCommittee::class,
            'label' => 'comité zonal',
            'rules' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'provinceId' => 'nullable|string|max:255',
                'municipalityId' => 'nullable|string|max:255',
                'memberCount' => 'nullable|integer|min:0',
                'isActive' => 'boolean',
            ],
        ],
        'members' => [
            'model' => Member::class,
            'label' => 'membro',
            'rules' => [
                'fullName' => 'required|string|min:3|max:255',
                'gender' => 'required|string|in:Male,Female',
                'birthDate' => 'nullable|date',
                'fatherName' => 'nullable|string|max:255',
                'motherName' => 'nullable|string|max:255',
                'bi' => 'nullable|string|max:50',
                'idEmitDate' => 'nullable|date',
                'phoneNumber' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'birthLocation' => 'nullable|string|max:255',
                'birthCommune' => 'nullable|string|max:255',
                'municipalityCard' => 'nullable|string|max:255',
                'cardEmitDate' => 'nullable|date',
                'residenceProvinceId' => 'required|string|max:255',
                'residenceMunicipalityId' => 'required|string|max:255',
                'residenceCommuneId' => 'nullable|string|max:255',
                'residenceNeighbourhoodId' => 'nullable|string|max:255',
                'level' => 'nullable|string|max:255',
                'gradeOrYear' => 'nullable|string|max:255',
                'qualification' => 'nullable|string|max:255',
                'profession' => 'nullable|string|max:255',
                'admissionYear' => 'nullable|string|max:10',
                'admissionPlace' => 'nullable|string|max:255',
                'status' => 'nullable|string|in:active,inactive',
                'isActive' => 'boolean',
                'memberCard' => 'nullable|string|max:255',
                'memberCardEmitDate' => 'nullable|date',
                'wasMilitar' => 'boolean',
                'militarParty' => 'nullable|string|max:255',
                'wasMemberFromAnotherParty' => 'boolean',
                'previousParty' => 'nullable|string|max:255',
            ],
        ],
        'users' => [
            'model' => User::class,
            'label' => 'utilizador',
            'rules' => [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:6|max:255',
                'role' => 'required|string|in:admin,operador,visualizador',
                'isActive' => 'boolean',
                'provinceId' => 'nullable|string|max:255',
                'municipalityId' => 'nullable|string|max:255',
            ],
        ],
        'idioms' => [
            'model' => Idiom::class,
            'label' => 'idioma',
            'rules' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'isActive' => 'boolean',
            ],
        ],
        'languages' => [
            'model' => Language::class,
            'label' => 'língua',
            'rules' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'isActive' => 'boolean',
            ],
        ],
        'trainings' => [
            'model' => Training::class,
            'label' => 'formação',
            'rules' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'level' => 'nullable|string|max:255',
                'isActive' => 'boolean',
            ],
        ],
    ],
];
