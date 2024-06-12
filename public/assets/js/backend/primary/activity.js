console.log("wr");
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'primary/activity/index' + location.search,
                    add_url: 'primary/activity/add',
                    edit_url: 'primary/activity/edit',
                    del_url: 'primary/activity/del',
                    multi_url: 'primary/activity/multi',
                    import_url: 'primary/activity/import',
                    table: 'primary_activity',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'starttime', title: __('Starttime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('activityData'),
                                    icon: 'fa fa-list',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    title:function (row) {
                                        return __('activityData')+"-"+ row.name;
                                    },
                                    url: 'pad/innerindex',
                                    visible:function(row){
                                        if(row.pad_count>0){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                name: 'detail',
                                text: __('addData'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    title:function (row) {
                                        return __('addData')+"-"+ row.name;
                                    },
                                url: 'pad/add'
                            },
                                {
                                    name: 'import',
                                    text: __('Import'),
                                    icon: 'fa fa-list',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    title:function (row) {
                                        return __('Import')+"-"+ row.name;
                                    },
                                    url: 'pad/import'
                                },
                                {
                                    name: 'export',
                                    text: __('Export'),
                                    icon: 'fa fa-list',
                                    classname: 'btn btn-info btn-xs btn-detail',
                                    title:function (row) {
                                        return __('Export')+"-"+ row.name;
                                    },
                                    url: 'pad/export',
                                    extend:' target="_blank"',
                                    visible:function(row){
                                        if(row.pad_count>0){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name: 'export',
                                    text: __('ExportQrcode'),
                                    icon: 'fa fa-list',
                                    classname: 'btn btn-info btn-xs btn-detail',
                                    title:function (row) {
                                        return __('Export')+"-"+ row.name;
                                    },
                                    url: 'primary/activity/exportqrcode',
                                    extend:' target="_blank"',
                                    visible:function(row){
                                        if(row.pad_count>0){
                                            return true;
                                        }
                                        return false;
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
