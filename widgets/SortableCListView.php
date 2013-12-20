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

/**
 * CListView widget extended to list records sorted.
 * @author Marc Busqué Pérez <marc@lamarciana.com>
 * @package Yii Sortable Model
 * @copyright Copyright &copy; 2012 Marc Busqué Pérez
 * @license LGPL
 * @since 1.0
 */

Yii::import('zii.widgets.CListView');

class SortableCListView extends CListView
{
   /**
    * @var boolean whether to list items sorted, which is the essence of this widget. If it's set to true items are listed ordered by the field defined in $orderField. It it's set to false the widget defaults to a normal CListView and the rest of this extension widget properties are ignored. Defaults to true
    */
   public $listSorted = true;
   /**
    * @var string the field name in the database table which stores the order for the record. This should be a positive integer field. Defaults to 'order'
    */
   public $orderField = 'order';
   /**
    * @var CActiveDataProvider the data provider for the view.
    */
   public $dataProvider;
   /**
    * @var boolean whether to show records in a descendant order. Defaults to false
    */
   public $descSort = false;


   /**
    * Initializes the list view.
    * This method will initialize required property values and instantiate {@link columns} objects.
    */
   public function init()
   {
      if ($this->listSorted === true)
      {
         /*To use this widget, data provider must be an instance of CActiveDataProvider*/
         if (!($this->dataProvider instanceof CActiveDataProvider)) {
            throw new CException(Yii::t('zii', 'Data provider must be an instance of CActiveDataProvider'));
         }
         if ($this->descSort !== true) {
            $sort_direction = 'ASC';
         } else {
            $sort_direction = 'DESC';
         }
         $this->dataProvider->setSort(array('defaultOrder' => $this->dataProvider->model->tableAlias.'.'.$this->orderField.' '.$sort_direction));
      }

      parent::init();
   }
}
