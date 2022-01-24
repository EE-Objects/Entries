<?php

namespace EeObjects\Model\Channel\Fields;

use ExpressionEngine\Service\Model\Model;

class Relationship extends Model
{
    protected static $_validation_rules = [
        'entry_id' => 'required',
        'item_type' => 'required',
    ];

    protected static $_primary_key = 'relationship_id';
    protected static $_table_name = 'relationships';

    protected $relationship_id;
    protected $parent_id;
    protected $child_id;
    protected $field_id;
    protected $fluid_field_data_id;
    protected $grid_field_id;
    protected $grid_col_id;
    protected $grid_row_id;
    protected $order;
}
