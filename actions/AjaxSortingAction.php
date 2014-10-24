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
 * This action is triggered by SortableCGridView widget to store in the background the new records order.
 * @author Marc Busqué Pérez <marc@lamarciana.com>
 * @package Yii Sortable Model
 * @copyright Copyright &copy; 2012 Marc Busqué Pérez
 * @license LGPL
 * @since 1.0
 */

class AjaxSortingAction extends CAction
{
   public function run()
   {
      if (isset($_POST))
      {
         $order_field = $_POST['order_field'];
         $model = call_user_func(array($_POST['model'], 'model'));
         $dragged_entry = $model->findByPk($_POST['dragged_item_id']);
         /*load dragged entry before changing orders*/
         $prev = $dragged_entry->{$order_field};
         $new = $model->findByPk($_POST['replacement_item_id'])->{$order_field};
         /*update order only for the affected records*/
         if ($prev < $new)
         {
            for ($i = $prev + 1;$i <= $new; $i++)
            {
               $entry = $model->findByAttributes(array($order_field => $i));
               $entry->{$order_field} = $entry->{$order_field} - 1;
               $entry->update([$order_field]);
            }
         }
         elseif ($prev > $new)
         {
            for ($i = $prev - 1;$i >= $new; $i--)
            {
               $entry = $model->findByAttributes(array($order_field => $i));
               $entry->{$order_field} = $entry->{$order_field} + 1;
               $entry->update([$order_field]);
            }
         }
         /*dragged entry order is changed at last, to not interfere during the changing orders loop*/
         $dragged_entry->{$order_field} = $new;
         $dragged_entry->update([$order_field]);
      }
   }
}
