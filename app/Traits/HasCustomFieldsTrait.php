<?php

namespace Crater\Traits;

use Crater\Models\CustomField;

trait HasCustomFieldsTrait
{
    public function fields()
    {
        return $this->morphMany('Crater\Models\CustomFieldValue', 'custom_field_valuable');
    }

    protected static function booted()
    {
        static::deleting(function ($data) {
            if ($data->fields()->exists()) {
                $data->fields()->delete();
            }
        });
    }

    public function addCustomFields($customFields)
    {
        foreach ($customFields as $field) {
            if (! is_array($field)) {
                $field = (array)$field;
            }
            $customField = CustomField::find($field['id']);

            $customFieldValue = [
                'type' => $customField->type,
                'custom_field_id' => $customField->id,
                'company_id' => $customField->company_id,
                getCustomFieldValueKey($customField->type) => $field['value'],
            ];

            $this->fields()->create($customFieldValue);
        }
    }

    public function updateCustomFields($customFields)
    {
        \Log::info('==>> Starting updateCustomFields:', [
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'input_custom_fields' => $customFields
        ]);

        foreach ($customFields as $field) {
            if (! is_array($field)) {
                $field = (array)$field;
            }

            \Log::info('==>> Processing custom field:', [
                'field_id' => $field['id'] ?? null,
                'field_name' => $field['name'] ?? null,
                'field_value' => $field['value'] ?? null
            ]);

            $customField = CustomField::find($field['id']);
            
            \Log::info('==>> Found custom field definition:', [
                'custom_field_id' => $customField->id,
                'custom_field_name' => $customField->name,
                'custom_field_type' => $customField->type,
                'custom_field_slug' => $customField->slug
            ]);

            $customFieldValue = $this->fields()->firstOrCreate([
                'custom_field_id' => $customField->id,
                'type' => $customField->type,
                'company_id' => $this->company_id,
            ]);

            \Log::info('==>> Found/created custom field value:', [
                'custom_field_value_id' => $customFieldValue->id,
                'previous_value' => $customFieldValue->getDefaultAnswerAttribute()
            ]);

            $type = getCustomFieldValueKey($customField->type);
            $customFieldValue->$type = $field['value'];
            $customFieldValue->save();

            \Log::info('==>> Updated custom field value:', [
                'custom_field_value_id' => $customFieldValue->id,
                'new_value' => $field['value'],
                'value_type' => $type
            ]);
        }

        \Log::info('==>> Completed updateCustomFields for:', [
            'model_type' => get_class($this),
            'model_id' => $this->id
        ]);
    }

    public function getCustomFieldBySlug($slug)
    {
        return $this->fields()
            ->with('customField')
            ->whereHas('customField', function ($query) use ($slug) {
                $query->where('slug', $slug);
            })->first();
    }

    public function getCustomFieldValueBySlug($slug)
    {
        $value = $this->getCustomFieldBySlug($slug);

        if ($value) {
            return $value->defaultAnswer;
        }

        return null;
    }
}
