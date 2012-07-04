# Yii Sortable Model

* Overview
* Requirements
* Setup
* Class Reference
* Resources
* License

## Overview
Yii Sortable Model is a Yii extension that provides with a set of tools to help keeping records of a model manually sorted. Each of these tools can be used alone. Specifically, it provides with:

### SortableCGridView
`CGridView` widget extended to allow drag and drop sorting of records. With it, users will be able to drag and drop with the mouse the rows of the grid to change records order. New order will be automatically saved in the database through an Ajax call.

### SortableCListView
`CListView` widget extended to list records sorted.

### SortableCActiveRecord
`CActiveRecord` extended to keep records order consistent when adding or deleting items. With it, when a new record is added it will get automatically the last position, and when one is deleted the rest of records will be rearranged to fill its gap.

## Requirements
Yii Sortable Model has been tested with Yii v1.1.8, but surely works with previous versions.

## Setup
Download Yii Sortable Model from https://github.com/laMarciana/yiisortablemodel

Extract its contents to `protected/extensions/` in your Yii installation.

Create a field in the database table of your model with an integer field. This field will be the responsable to store records order.

### SortableCGridView
In your view, add:

    <?php $this->widget('ext.yiisortablemodel.widgets.SortableCGridView', array(
      'dataProvider' => $dataProvider,
      'orderField' => 'order',
      'idField' => 'id',
      'orderUrl' => 'order',
    ); ?>

As in its parent, `CGridView`, you must provide a data provider, but in `SortableCGridView` this must be an instance of `CActiveDataProvider` (the standard if you are working from data coming from a model, like the ones generated by gii).

`orderField` property defines which is the field that it is meant to store the records order.

`orderId` property defines which is the field that it is meant to store the primary key of the record.

`orderUrl` property defines the name of the action that the controller from where the widget is called will use to trigger the actual ajax sorting. This must be configured as well in the [`actions()` method of the controller](http://www.yiiframework.com/doc/guide/1.1/en/basics.controller#action). For example, if `orderUrl` is set to `order`, then in your controller you must have:  

    public function actions()
    {
      return array(
        'order' => array(
           'class' => 'ext.yiisortablemodel.actions.AjaxSortingAction',
        ),
      );
    }

Don't forget to update, if needed, the [access rules](http://www.yiiframework.com/doc/guide/1.1/en/topics.auth#access-control-filter) to consider this new action. For example:

    public function accessRules()
    {
      return array(
         ...
         array('allow', 'actions' => array('order'), 'users' => array('@')),
         ...
      );
    }

Look at the class reference for additional options.

### SortableCListView
In your view, add:

    <?php $this->widget('ext.yiisortablemodel.widgets.SortableCListView', array(
      'dataProvider' => $dataProvider,
      'orderField' => 'order',
    ); ?>

As in its parent, `CListView`, you must provide a data provider, but in `SortableCListView` this must be an instance of `CActiveDataProvider`  (the standard if you are working from data coming from a model, like the ones generated by gii).

`orderField` property defines which is the field that it is meant to store records order.

Look at the class reference for additional options.

### SortableCActiveRecord
First, add the models directory of this extension to the `import` option of the main configuration file (located in `protected/config/main.php`):

    'import' => array(
      ...
      'ext.yiisortablemodel.models.*',
      ...
    ),

Tell your model to extend `SortableCActiveRecord` instead of `CActiveRecord`, and set its `$orderField` property to the field in the database table that stores records order:

    class myModel extends SortableCActiveRecord {
      public $orderField = 'order';
      ...

## Class Reference
You have a complete Class Reference, with all the additional options you can set up, in the doc folder. Class Reference generated by [YiiDocumentor](http://www.yiiframework.com/extension/yiidocumentor/).

## Resources
* [Yii Sortable Model homepage](https://github.com/laMarciana/yiiSortableModel)

* [Yii Sortable Model in Yii extensions directory](http://www.yiiframework.com/extension/yiisortablemodel/)

## License
Copyright 2012, Marc Busqué Pérez, under GNU LESSER GENERAL PUBLIC LICENSE
marc@lamarciana.com - http://www.lamarciana.com
