<?php

namespace App\Http\Resources;

use App\Models\BodyType;
use App\Models\LocalCommittee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ApiResource extends JsonResource
{
    /** @var array<string, array{id: string|int, name: string}|null> */
    private static array $relationCache = [];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $data = [];

        foreach ($this->resource->toArray() as $key => $value) {
            if (in_array($key, ['password', 'remember_token', 'token_hash', 'email_verified_at'], true)) {
                continue;
            }

            $data[Str::camel($key)] = $value;
        }

        $attributes = $this->resource->getAttributes();

        if (array_key_exists('body_type_id', $attributes)) {
            $data['bodyType'] = $this->related(BodyType::class, $attributes['body_type_id']);
        }

        if (array_key_exists('local_committee_id', $attributes)) {
            $data['localCommittee'] = $this->related(LocalCommittee::class, $attributes['local_committee_id']);
        }

        return $data;
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return array{id: string|int, name: string}|null
     */
    protected function related(string $modelClass, mixed $id): ?array
    {
        if (! $id) {
            return null;
        }

        $key = $modelClass.'|'.$id;

        if (! array_key_exists($key, self::$relationCache)) {
            /** @var Model|null $related */
            $related = $modelClass::query()->find($id);
            self::$relationCache[$key] = $related
                ? ['id' => $related->getKey(), 'name' => (string) $related->getAttribute('name')]
                : null;
        }

        return self::$relationCache[$key];
    }
}
