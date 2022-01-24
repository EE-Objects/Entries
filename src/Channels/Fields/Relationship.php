<?php
namespace EeObjects\Channels\Fields;

use EeObjects\Channels\AbstractField;

class Relationship extends AbstractField
{
    /**
     * This Field isn't stored in channel_data table
     * @var bool
     */
    protected $cd_storage = false;

    /**
     * @param $value
     * @return bool|void
     */
    public function save($value)
    {
        if ($this->entry_id && is_array($value)) {
            $this->delete(); //we reset 'cause that's how EE rolls
            $order = 0;
            foreach ($value as $entry_id) {
                $data = [
                    'parent_id' => $this->entry_id,
                    'child_id' => $entry_id,
                    'field_id' => $this->getId(),
                    'order' => $order,
                ];
                $relationship = ee('Model')->make('ee_objects:Relationship');
                $relationship->set($data);
                $relationship->save();
                $order++;
            }
        }
    }

    /**
     * Retuns an array of related entry_id
     * @param mixed $value
     * @return array
     */
    public function read($value)
    {
        return $this->getRelationships();
    }

    /**
     * Removes all the Relationships assigned by this Entry
     * @return bool|void
     */
    public function delete()
    {
        $relationships = ee('Model')->get('ee_objects:Relationship')
                                        ->filter('parent_id', $this->entry_id)
                                        ->filter('field_id', $this->getId());

        if ($relationships->count() >= 1) {
            foreach ($relationships->all() as $relationship) {
                $relationship->delete();
            }
        }
    }

    /**
     * Returns the Relationships for the Entry
     * @return array
     */
    protected function getRelationships()
    {
        $return = [];
        if ($this->entry_id) {
            $where = [
                'parent_id' => $this->entry_id,
                'field_id' => $this->getId(),
            ];

            $result = ee()->db->select()->from('relationships')->where($where)->get();
            if ($result instanceof \CI_DB_mysqli_result) {
                foreach ($result->result_array() as $key => $value) {
                    $return[] = $value['child_id'];
                }
            }
        }

        return $return;
    }
}
