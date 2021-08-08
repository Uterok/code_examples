<?php

namespace App\Models\Traits;

trait ModelExtended
{
    public static function deleteSoftModel(?int $id, ?bool $force = false, ?bool $throw_not_found_error = true): void
    {
        $first_method = $throw_not_found_error ? 'firstOrFail' : 'first';
        $item_to_delete = static::when($force, function ($query) {
            $query->withTrashed();
        })
            ->where('id', $id)
            ->{$first_method}();

        if (! isset($item_to_delete)) {
            return;
        }

        $delete_method = $force ? 'forceDelete' : 'delete';
        $item_to_delete->{$delete_method}();
    }

    public static function getModelTable(): string
    {
        return (new static())->getTable();
    }

    public function getRawAttribute($attribute): mixed
    {
        return $this->attributes[$attribute] ?? null;
    }
}
