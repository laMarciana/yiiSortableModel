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
 * CGridView widget extended to allow drag and drop sorting of records. With it, users will be able to drag and drop with the mouse the rows of the grid to change records order. New order will be automatically saved in the database through an Ajax call.
 * @author Marc Busqué Pérez <marc@lamarciana.com>
 * @package Yii Sortable Model
 * @copyright Copyright &copy; 2012 Marc Busqué Pérez
 * @license LGPL
 * @since 1.0
 */

Yii::import('zii.widgets.grid.CGridView');

class SortableCGridView extends CGridView
{
   /**
    * @var boolean whether to enable the drag and drop sorting, which is the essence of this widget. If it's set to true sorting by clicking in columns names is disabled (so $enableSorting property has no effect) and items are showed ordered by the field defined in $orderField. If it's set to false, the widget defaults to a normal CGridView and the rest of this extension widget properties are ignored. Defaults to true
    */
   public $enableDragDropSorting = true;
   /**
    * @var string the field name in the database table which stores the order for the record. This should be a positive integer field. Defaults to 'order'
    */
   public $orderField = 'order';
   /**
    * @var string the field name in the database table which stores the id for the record. Defaults to 'id'
    */
   public $idField = 'id';
   /**
    * @var string the action name that will be used to trigger drag and drop sorting (through the AjaxSortingAction located in the "actions" directory in this extension). Defaults to 'order'. This must be defined in the "actions" method of the controller in which this widget is called. For example: "public function actions() {return array('order' => array('class' => 'ext.yiisortablemodel.actions.AjaxSortingAction'))}. If needed, its acces rules have to be defined, too"
    */
   public $orderUrl = 'order';
   /**
    * @var CActiveDataProvider the data provider for the view.
    */
   public $dataProvider;
   /**
    * @var array options passed to initialize Jquery Ui Sortable. Look at http://jqueryui.com/demos/sortable/ for the available options. Keys must be options name and values options values
    */
   public $jqueryUiSortableOptions;
   /**
    * @var boolean whether to show records in a descendant order. Defaults to false
    */
   public $descSort = false;
   /**
    * @var boolean whether to update grid content after sorting. Useful to update rows classes which depends on row positions, as during sorting they have been changed. Defaults to true
    */
   public $updateAfterSorting = true;
   /**
    * @var boolean whether to show all items in one page, disabling completely pagination. Defaults to true
    */
   public $allItemsInOnePage = true;
   /**
    * @var string a message to show when drag and drop sorting is completed successfully. If it's an empty string, no message is showed. Defaults to empty string
    */
   public $successMessage = '';
   /**
    * @var string a message to show when there is an error in drag and drop sorting. Defaults to 'An error has occured while sorting'
    */
   public $errorMessage = 'An error has occured while sorting';
   
   /**
    * Initializes the grid view.
    * This method will initialize required property values and instantiate {@link columns} objects.
    */
   public function init()
   {
      if ($this->enableDragDropSorting === true) {
         /*To use this widget, data provider must be an instance of CActiveDataProvider*/
         if (!($this->dataProvider instanceof CActiveDataProvider)) {
            throw new CException(Yii::t('zii', 'Data provider must be an instance of CActiveDataProvider'));
         }
         if ($this->allItemsInOnePage === true) {
            $this->dataProvider->pagination = false;
         }
         $this->enableSorting = false;
         if ($this->descSort !== true) {
            $sort_direction = 'ASC';
         } else {
            $sort_direction = 'DESC';
         }
         $this->dataProvider->setSort(array('defaultOrder' => '`'.$this->orderField.'`'.$sort_direction));
      }

      parent::init();
   }

   /**
    * Renders a table body row.
    * @param integer $row the row number (zero-based).
    */
   public function renderTableRow($row)
   {
      $data=$this->dataProvider->data[$row];
      echo '<tr';
      if($this->rowCssClassExpression !== null) {
         echo ' class="'.$this->evaluateExpression($this->rowCssClassExpression,array('row'=>$row,'data'=>$data)).'"';
      } else if(is_array($this->rowCssClass) && ($n=count($this->rowCssClass))>0) {
         echo ' class="'.$this->rowCssClass[$row%$n].'"';
      }
      /*Render the record id as its 'data-id' atribute. This information will be used to sort*/
      if ($this->enableDragDropSorting === true) {
         echo ' data-id="'.CHtml::value($data, $this->idField).'"';
      }
      echo '>';
      foreach($this->columns as $column) {
         $column->renderDataCell($row);
      }
      echo "</tr>\n";
   }

   /**
    * Returns the javascript code responsable of handling the drag and drop sorting through an Ajax request
    *
    * @return string drag and drop sorting javascript code
    */
   protected function getSortScript()
   {
      return '
         var grid_id = '.Cjavascript::encode($this->getId()).';
         var grid_selector = '.Cjavascript::encode('#'.$this->getId()).';
         var tbody_selector = '.Cjavascript::encode('#'.$this->getId().' tbody').';
         /*apply sortable*/
         $(tbody_selector).sortable('.CJavascript::encode($this->jqueryUiSortableOptions).');
         /*helper to keep each table cell width while dragging*/
         $(tbody_selector).sortable("option", "helper", function(e, ui) {
            ui.children().each(function() {
               $(this).width($(this).width());
            });
            return ui;
         });
         /*add dragged row index before moving as an attribute. Used to know if item is moved forward or backward*/
         $(tbody_selector).bind("sortstart", function(event, ui) {
            ui.item.attr("data-prev-index", ui.item.index());
         });
         /*trigger ajax sorting when grid is updated*/
         $(tbody_selector).bind("sortupdate", function(event, ui) {
            $(grid_selector).addClass('.CJavascript::encode($this->loadingCssClass).');
            var data = {};
            data["dragged_item_id"] = parseInt(ui.item.attr("data-id"));
            var prev_index = parseInt(ui.item.attr("data-prev-index"));
            var new_index = parseInt(ui.item.index());
            /*which item place take dragged item*/
            if (prev_index < new_index) {
               data["replacement_item_id"] = ui.item.prev().attr("data-id");
            } else {
               data["replacement_item_id"] = ui.item.next().attr("data-id");
            }
            data["model"] = '.Cjavascript::encode($this->dataProvider->modelClass).';
            data["order_field"] = '.Cjavascript::encode($this->orderField).';
            data["YII_CSRF_TOKEN"] = '.Cjavascript::encode(Yii::app()->getRequest()->getCsrfToken()).';
            ui.item.removeAttr("data-prev-index");
            '.CHtml::ajax(array(
               'type' => 'POST',
               'url' => Yii::App()->controller->createAbsoluteUrl($this->orderUrl),
               'data' => 'js:data',
               'success' => 'js:function() {
                  $(grid_selector).removeClass('.CJavascript::encode($this->loadingCssClass).');
                  /*update the whole grid again to update row class values*/
                  if ("'.(string)$this->updateAfterSorting.'") {
                     $.fn.yiiGridView.update(grid_id);
                  }
                  if ("'.(string)$this->successMessage.'") {
                     alert('.CJavascript::encode($this->successMessage).');
                  }
                }
               ',
               'error' => 'js:function() {
                  $(grid_selector).removeClass('.CJavascript::encode($this->loadingCssClass).');
                  alert('.CJavascript::encode($this->errorMessage).');
                  $.fn.yiiGridView.update(grid_id);
               }
               '
            )).'
         });
      ';
   }

   /**
    * Registers necessary client scripts.
    */
   public function registerClientScript()
   {
      //Call parent
      parent::registerClientScript();
      if ($this->enableDragDropSorting === true) {
         $cs=Yii::app()->getClientScript();
         //Register jquery-ui
         $cs->registerCoreScript('jquery.ui');
         //Register sort script
         $cs->registerScript(__CLASS__.'-'.$this->id, 
            /*Call sort script when document is ready and each time grid is updated*/
            $this->getSortScript().'
            $("body").ajaxSuccess(function(e, xhr, settings) {
               if (settings.url === $.fn.yiiGridView.getUrl('.Cjavascript::encode($this->getId()).')) {
                  '.$this->getSortScript().'
               }
            });
      ');
      }
   }
}
