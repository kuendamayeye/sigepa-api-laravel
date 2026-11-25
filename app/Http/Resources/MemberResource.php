<?php

namespace App\Http\Resources;

use App\Models\Commune;
use App\Models\Municipality;
use App\Models\Neighborhood;
use App\Models\Province;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class MemberResource extends ApiResource
{
    /** @var array<string, string> */
    private static array $nameCache = [];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        $data['residenceProvince'] = $this->lookupName(Province::class, $this->residence_province_id);
        $data['residenceMunicipality'] = $this->lookupName(Municipality::class, $this->residence_municipality_id);
        $data['residenceCommune'] = $this->lookupName(Commune::class, $this->residence_commune_id);
        $data['residenceNeighbourhood'] = $this->lookupName(Neighborhood::class, $this->residence_neighbourhood_id);

        return $data;
    }

    /** @param class-string<Model> $modelClass */
    private function lookupName(string $modelClass, mixed $id): string
    {
        if (! $id) {
            return '';
        }

        $key = $modelClass.'|'.$id;

        if (! array_key_exists($key, self::$nameCache)) {
            self::$nameCache[$key] = (string) ($modelClass::query()->find($id)?->getAttribute('name') ?? '');
        }

        return self::$nameCache[$key];
    }
}
