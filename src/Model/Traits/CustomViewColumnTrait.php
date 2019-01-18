<?php

namespace Exceedone\Exment\Model\Traits;

use Exceedone\Exment\Model\CustomViewSummary;
use Exceedone\Exment\Model\Define;
use Exceedone\Exment\Enums\ViewColumnType;
use Exceedone\Exment\Enums\SystemColumn;
use Exceedone\Exment\ColumnItems;

trait CustomViewColumnTrait
{
    /**
     * get ViewColumnTarget.
     * * we have to convert string if view_column_type is system for custom view form-display*
     */
    public function getViewColumnTargetAttribute(){
        return $this->getViewColumnTarget();
    }

    public function getColumnItemAttribute(){
        // if tagret is number, column type is column.
        if ($this->view_column_type == ViewColumnType::COLUMN) {
            return $this->custom_column->column_item;
        }
        // parent_id
        elseif ($this->view_column_type == ViewColumnType::PARENT_ID) {
            return ColumnItems\ParentItem::getItem($this->custom_view->custom_table);
        }
        // child_summary
        elseif ($this->view_column_type == ViewColumnType::CHILD_SUM) {
            return ColumnItems\CustomItem::getItem($this->custom_column)
                ->options([
                    'summary_child' => true,
                ]);
        }
        // system column
        else {
            return ColumnItems\SystemItem::getItem($this->custom_view->custom_table, $this->view_column_target);
        }   
    }
    
    /**
     * set ViewColumnTarget.
     * * we have to convert int if view_column_type is system for custom view form-display*
     */
    public function setViewColumnTargetAttribute($view_column_target){
        $this->setViewColumnTarget($view_column_target);
    }

    protected function getViewColumnTarget($column_type_key = 'view_column_type', $column_type_target_key = 'view_column_target_id'){
        if(!isset($this->{$column_type_key}) || !isset($this->{$column_type_target_key})){
            return null;
        }
        if($this->{$column_type_key} == ViewColumnType::SYSTEM){
            // get VIEW_COLUMN_SYSTEM_OPTIONS and get name.
            return SystemColumn::getOption(['id' => $this->{$column_type_target_key}])['name'] ?? null;
        }
        elseif($this->{$column_type_key} == ViewColumnType::PARENT_ID){
            return 'parent_id';
        }
        elseif($this->{$column_type_key} == ViewColumnType::CHILD_SUM){
            $custom_column = $this->custom_column;
            if(is_null($custom_column)){
                return null;
            }
            return $custom_column->custom_table->id . '_' . $this->view_column_target_id;
        }
        else{
            return $this->view_column_target_id;
        }
    }

    protected function setViewColumnTarget($view_column_target, $column_type_key = 'view_column_type', $column_type_target_key = 'view_column_target_id'){
        if (!is_numeric($view_column_target)) {
            if ($view_column_target === 'parent_id') {
                $this->{$column_type_key} = ViewColumnType::PARENT_ID;
                $this->{$column_type_target_key} = DEFINE::CUSTOM_COLUMN_TYPE_PARENT_ID;
            } elseif(preg_match('/\d+_\d+$/i', $view_column_target) === 1) {
                $items = explode('_', $view_column_target);
                $this->{$column_type_key} = ViewColumnType::CHILD_SUM;
                $this->{$column_type_target_key} = $items[1];
            } else {
                $this->{$column_type_key} = ViewColumnType::SYSTEM;
                $this->{$column_type_target_key} = SystemColumn::getOption(['name' => $view_column_target])['id'] ?? null;
            }
        } else {
            $this->{$column_type_key} = ViewColumnType::COLUMN;
            $this->{$column_type_target_key} = $view_column_target;
        }
    }
}
