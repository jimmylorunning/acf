<?php
/**
 * Freeform for Craft
 *
 * @package       Solspace:Freeform
 * @author        Solspace, Inc.
 * @copyright     Copyright (c) 2008-2018, Solspace, Inc.
 * @link          https://solspace.com/craft/freeform
 * @license       https://solspace.com/software/license-agreement
 */

namespace Craft;

/**
 * @property int    $id
 * @property int    $integrationId
 * @property string $handle
 * @property string $label
 * @property string $type
 * @property bool   $required
 */
class Freeform_CrmFieldModel extends BaseModel
{
    public static function create()
    {
        $model = new Freeform_CrmFieldModel();

        return $model;
    }

    /**
     * @return array
     */
    protected function defineAttributes()
    {
        return [
            "id"            => AttributeType::Number,
            "integrationId" => AttributeType::Number,
            "handle"        => AttributeType::String,
            "label"         => AttributeType::String,
            "type"          => AttributeType::String,
            "required"      => AttributeType::Bool,
        ];
    }
}
