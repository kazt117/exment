<?php

namespace Exceedone\Exment\Services\DataImportExport\Providers\Import;

use Illuminate\Support\Collection;
use Exceedone\Exment\Services\DataImportExport\Providers\Traits\RelationPivotTableTrait;

/**
 * Relation Pivot table (n:n)
 */
class RelationPivotTable extends ProviderBase
{
    use RelationPivotTableTrait{
        RelationPivotTableTrait::__construct as traitconstruct;
    }
    
    public function __construct($args = []){
        parent::__construct();  
        $this->traitconstruct($args);
    }

    /**
     * get pivot data for n:n
     */
    public function getDataObject($data, $options = []){
        $results = [];
        $headers = [];
        foreach ($data as $key => $value) {
            // get header if $key == 0
            if ($key == 0) {
                $headers = $value;
                continue;
            }
            // continue if $key == 1
            elseif ($key == 1) {
                continue;
            }

            // combine value
            $value_custom = array_combine($headers, $value);
            $delete = boolval(array_get($value_custom, 'delete'));
            array_forget($value_custom, 'delete');
            $results[] = ['data' => $value_custom, 'delete' => $delete];
        }

        return $results;
    }
    
    /**
     * validate imported all data.
     * @param $data
     * @return array
     */
    public function validateImportData($dataObjects)
    {
        return [$dataObjects, null];
    }
    
    /**
     * @param $data
     * @return array
     */
    public function dataProcessing($data)
    {
        return $data;
    }
    
    /**
     * import data (n:n relation)
     */
    public function importdata($dataPivot)
    {
        $data = array_get($dataPivot, 'data');
        $delete = array_get($dataPivot, 'delete');

        // get database name
        $table_name = $this->relation->getRelationName();

        // get target id(cannot use Eloquent because not define)
        $id = \DB::table($table_name)
            ->where('parent_id', array_get($data, 'parent_id'))
            ->where('child_id', array_get($data, 'child_id'))
            ->first()->id ?? null;
        
        // if delete
        if (isset($id) && $delete) {
            \DB::table($table_name)->where('id', $id)->delete();
        }elseif(!isset($id)){
            \DB::table($table_name)->insert($data);
        }
    }

}