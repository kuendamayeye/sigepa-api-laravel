<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Http\Resources\MemberResource;
use App\Http\Resources\UserResource;
use App\Models\Core;
use App\Models\LocalCommittee;
use App\Models\Member;
use App\Models\SectorialCommittee;
use App\Models\ZonalCommittee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ResourceController extends Controller
{
    public function index(Request $request, string $resource): JsonResource
    {
        $definition = $this->definition($resource);
        $this->ensureUserCanRead($resource);

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];
        $query = $modelClass::query();

        $this->applyFilters($query, $resource, $request);

        $query->orderByDesc('created_at');

        if ($resource !== 'structures') {
            $query->orderBy('id');
        }

        /** @var Collection<int, Model> $models */
        $models = $query->get();

        return match ($resource) {
            'members' => MemberResource::collection($models),
            'users' => UserResource::collection($models),
            default => ApiResource::collection($models),
        };
    }

    public function show(Request $request, string $resource, string $id): JsonResource
    {
        $definition = $this->definition($resource);
        $this->ensureUserCanRead($resource);

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];

        /** @var Model $model */
        $model = $modelClass::query()->findOrFail($id);

        return $this->resourceFor($resource, $model);
    }

    public function store(Request $request, string $resource): JsonResource
    {
        $definition = $this->definition($resource);

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];
        $attributes = $this->validatePayload($request, $definition['rules'], $resource);

        /** @var Model $model */
        $model = new $modelClass;
        $model->fill($attributes);

        // Os valores por omissão da base não aparecem no modelo até ser recarregado.
        if (Schema::hasColumn($model->getTable(), 'is_active') && ! $model->hasAttribute('is_active')) {
            $model->setAttribute('is_active', true);
        }

        if ($resource === 'members' && ! $model->hasAttribute('status')) {
            $model->setAttribute('status', 'active');
        }

        $model->save();

        return $this->resourceFor($resource, $model);
    }

    public function update(Request $request, string $resource, string $id): JsonResource
    {
        $definition = $this->definition($resource);

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];

        /** @var Model $model */
        $model = $modelClass::query()->findOrFail($id);
        $attributes = $this->validatePayload($request, $definition['rules'], $resource, $model, true);

        $model->fill($attributes);
        $model->save();

        return $this->resourceFor($resource, $model);
    }

    public function destroy(Request $request, string $resource, string $id): JsonResponse
    {
        $definition = $this->definition($resource);

        if ($resource === 'users' && (int) $id === (int) Auth::id()) {
            abort(403, 'Não pode remover o seu próprio utilizador.');
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];

        /** @var Model $model */
        $model = $modelClass::query()->findOrFail($id);
        $model->delete();

        return response()->json(['message' => 'Registo removido com sucesso.']);
    }

    public function toggleActive(string $resource, string $id, string $action): JsonResource
    {
        $definition = $this->definition($resource);
        $active = $action === 'activate';

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];

        /** @var Model $model */
        $model = $modelClass::query()->findOrFail($id);
        $model->setAttribute('is_active', $active);

        if ($model instanceof Member) {
            $model->setAttribute('status', $active ? 'active' : 'inactive');
        }

        $model->save();

        return $this->resourceFor($resource, $model);
    }

    public function statistics(string $resource): JsonResponse
    {
        $definition = $this->definition($resource);

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];
        $query = $modelClass::query();

        $total = $query->count();
        $active = (clone $query)->where('is_active', true)->count();

        // "total" tem de ser a primeira chave: o dashboard usa-a como destaque.
        $stats = [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
        ];

        if ($resource === 'members') {
            $stats['male'] = (clone $query)->where('gender', 'Male')->count();
            $stats['female'] = (clone $query)->where('gender', 'Female')->count();
        }

        if ($resource === 'users') {
            $stats['admins'] = (clone $query)->where('role', 'admin')->count();
        }

        if ($resource === 'structures') {
            $stats['cores'] = Core::query()->count();
            $stats['localCommittees'] = LocalCommittee::query()->count();
            $stats['sectorialCommittees'] = SectorialCommittee::query()->count();
            $stats['zonalCommittees'] = ZonalCommittee::query()->count();
        }

        return response()->json($stats);
    }

    /** @return array{model: class-string<Model>, label: string, rules: array<string, string>} */
    private function definition(string $resource): array
    {
        /** @var array{model: class-string<Model>, label: string, rules: array<string, string>}|null $definition */
        $definition = config("sigepa.resources.{$resource}");

        if (! $definition) {
            abort(404, "Recurso desconhecido: {$resource}");
        }

        return $definition;
    }

    private function ensureUserCanRead(string $resource): void
    {
        if ($resource === 'users' && Auth::user()?->getAttribute('role') !== 'admin') {
            abort(403, 'Apenas administradores podem gerir utilizadores.');
        }
    }

    private function applyFilters(Builder $query, string $resource, Request $request): void
    {
        if ($resource === 'members') {
            $search = trim((string) $request->query('search', ''));

            if ($search !== '') {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('full_name', 'like', "%{$search}%")
                        ->orWhere('bi', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            }

            foreach ([
                'provinceId' => 'residence_province_id',
                'municipalityId' => 'residence_municipality_id',
                'communeId' => 'residence_commune_id',
            ] as $param => $column) {
                if ($request->filled($param)) {
                    $query->where($column, $request->query($param));
                }
            }
        } else {
            $search = trim((string) $request->query('search', ''));

            if ($search !== '' && $resource !== 'structures') {
                $query->where('name', 'like', "%{$search}%");
            }

            $filterColumns = [
                'municipalities' => ['provinceId' => 'province_id'],
                'communes' => ['municipalityId' => 'municipality_id'],
                'neighborhoods' => ['communeId' => 'commune_id'],
                'bodies' => ['bodyTypeId' => 'body_type_id'],
                'functions' => ['bodyTypeId' => 'body_type_id'],
                'cores' => ['localCommitteeId' => 'local_committee_id'],
                'local-committees' => ['provinceId' => 'province_id', 'municipalityId' => 'municipality_id'],
                'sectorial-committees' => ['provinceId' => 'province_id', 'municipalityId' => 'municipality_id'],
                'zonal-committees' => ['provinceId' => 'province_id', 'municipalityId' => 'municipality_id'],
                'users' => ['provinceId' => 'province_id', 'municipalityId' => 'municipality_id'],
            ];

            foreach ($filterColumns[$resource] ?? [] as $param => $column) {
                if ($request->filled($param)) {
                    $query->where($column, $request->query($param));
                }
            }
        }

        if ($request->boolean('isActive')) {
            $query->where('is_active', true);
        }

        if ($resource === 'members' && $request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($resource === 'users' && $request->filled('status')) {
            $query->where('is_active', $request->query('status') === 'active');
        }
    }

    /**
     * @param  array<string, string>  $rules
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, array $rules, string $resource, ?Model $model = null, bool $updating = false): array
    {
        if ($resource === 'users') {
            $rules['email'] = $updating && $model
                ? ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($model->getKey())]
                : 'required|email|max:255|unique:users,email';
        }

        if ($updating) {
            foreach ($rules as $field => $rule) {
                if (is_string($rule)) {
                    $rules[$field] = preg_replace('/^required/', 'sometimes|required', $rule);
                }
            }
        }

        $payload = [];
        foreach (array_keys($rules) as $field) {
            if (! $request->exists($field)) {
                continue;
            }

            $value = $request->input($field);

            // O formulário de utilizadores envia a senha vazia na edição.
            if ($resource === 'users' && $updating && $field === 'password' && blank($value)) {
                continue;
            }

            $payload[$field] = $value;
        }

        $validated = Validator::make($payload, $rules)->validate();

        $attributes = [];
        foreach ($validated as $field => $value) {
            $column = Str::snake($field);

            if (is_string($value) && trim($value) === '' && $this->isNullable($column)) {
                $value = null;
            }

            $attributes[$column] = $value;
        }

        if ($resource === 'members') {
            if (array_key_exists('status', $attributes)) {
                $attributes['is_active'] = $attributes['status'] === 'active';
            }

            if (array_key_exists('is_active', $attributes)) {
                $attributes['is_active'] = (bool) $attributes['is_active'];
            }
        }

        if ($resource === 'users' && array_key_exists('is_active', $attributes)) {
            $attributes['is_active'] = (bool) $attributes['is_active'];
        }

        return $attributes;
    }

    private function isNullable(string $column): bool
    {
        return ! in_array($column, ['name', 'full_name', 'gender', 'email', 'password', 'role'], true);
    }

    private function resourceFor(string $resource, ?Model $model = null): JsonResource
    {
        if ($resource === 'members') {
            return new MemberResource($model);
        }

        if ($resource === 'users') {
            return new UserResource($model);
        }

        return new ApiResource($model);
    }
}
