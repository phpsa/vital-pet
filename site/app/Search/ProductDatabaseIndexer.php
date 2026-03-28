<?php

namespace App\Search;

use Illuminate\Database\Eloquent\Model;
use Lunar\Search\ScoutIndexer;

class ProductDatabaseIndexer extends ScoutIndexer
{
    /**
     * Build a Scout payload using only real database columns.
     *
     * The Scout database engine translates keys from this array into SQL
     * "where ... like" clauses, so every key must map to an actual column.
     */
    public function toSearchableArray(Model $model): array
    {
        return [
            'id' => (string) $model->id,
            'status' => $model->status,
            'attribute_data' => (string) $model->getRawOriginal('attribute_data'),
            'brand_id' => (string) ($model->brand_id ?? ''),
            'product_type_id' => (string) ($model->product_type_id ?? ''),
        ];
    }
}
