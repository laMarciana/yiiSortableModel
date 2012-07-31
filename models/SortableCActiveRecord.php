<?php
//Copyright 2011, Marc Busqué Pérez
//
//This file is a part of Yii Sortable Model
//
//Yii Sortable Model is free software: you can redistribute it and/or modify
//it under the terms of the GNU Lesser General Public License as published by
//the Free Software Foundation, either version 3 of the License, or
//(at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU Lesser General Public License for more details.
//
//You should have received a copy of the GNU Lesser General Public License
//along with this program.  If not, see <http://www.gnu.org/licenses/>.
//

/**
 * Models extending this class will automatically keep its records order consistent when items are added or deleted.
 * @author Marc Busqué Pérez <marc@lamarciana.com>
 * @package Yii Sortable Model
 * @copyright Copyright &copy; 2012 Marc Busqué Pérez
 * @license LGPL
 * @since 1.0
 */
class SortableCActiveRecord extends CActiveRecord
{
   /**
    * @var string the field name in the database table which stores the order for the record. This should be a positive integer field. Defaults to 'order'
    */
   public $orderField = 'order';

   /**
    * This method is invoked before saving a record (after validation, if any).
    * The default implementation raises the {@link onBeforeSave} event.
    * You may override this method to do any preparation work for record saving.
    * Use {@link isNewRecord} to determine whether the saving is
    * for inserting or updating record.
    * Make sure you call the parent implementation so that the event is raised properly.
    * If it's a new record, assign automatically it's order at the last position.
    * @return boolean whether the saving should be executed. Defaults to true.
    */
   protected function beforeSave()
   {
      if ($this->isNewRecord) {
         $model = call_user_func(array(get_class($this), 'model'));
         $last_record = $model->find(array(
            'order' => '`'.$this->orderField.'` DESC',
            'limit' => 1
         ));
         if ($last_record) {
            $this->{$this->orderField} = $last_record->{$this->orderField} + 1;
         } else {
            $this->{$this->orderField} = 1;
         }
      }

      return parent::beforeSave();
   } 

   /**
    * This method is invoked after deleting a record.
    * The default implementation raises the {@link onAfterDelete} event.
    * You may override this method to do postprocessing after the record is deleted.
    * Make sure you call the parent implementation so that the event is raised properly.
    * Update records order field in a manner that their values are still successively increased by one (so, there is no gap caused by the deleted record)
    */
   protected function afterDelete()
   {
      $model = call_user_func(array(get_class($this), 'model'));
      $following_records = $model->findAll(array(
         'order' => '`'.$this->orderField.'` ASC',
         'condition' => '`'.$this->orderField.'` > '.$this->{$this->orderField},
      ));
      foreach ($following_records as $record) {
         $record->{$this->orderField}--;
         $record->update();
      }

      return parent::afterDelete();
   }
}
