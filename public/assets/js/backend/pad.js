define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'pad/index' + location.search,
                    add_url: 'pad/add',
                    edit_url: 'pad/edit',
                    del_url: 'pad/del',
                    multi_url: 'pad/multi',
                    import_url: 'pad/import',
                    table: 'pad',
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
                        {field: 'primary_activity_id', title: __('Primary_activity_id')},
                        {field: 'school_id', title: __('School_id')},
                        {field: 'personal', title: __('Personal'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        innerindex: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'pad/innerindex' + location.search,
                    add_url: 'pad/add',
                 /*   edit_url: 'pad/edit',*/
                    del_url: 'pad/del',
                    multi_url: 'pad/multi',
                    import_url: 'pad/import',
                    table: 'pad',
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
                       /* {field: 'primary_activity_id', title: __('Primary_activity_id')},*/
                        {field: 'school_id', title: __('School'),formatter:function(val,row,index){
                                return row.school_name;
                            }},
                        {field: 'organization', title: __('Organization')},
                        {field: 'personal', title: __('Personal'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        /*{field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,  buttons: [
                                {
                                    name: 'detail',
                                    text: __('Del'),
                                    icon: 'fa fa-list',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    url: 'pad/del'
                                },
                            ],formatter: Table.api.formatter.operate}*/
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            function checkOrganizationInputs(){
                var count=0;
                for(var i in organizationInputs.value){
                    if(organizationInputs.value[i].length>0){
                        count++;
                    }
                }
                if(count==0){
                    return false;
                }
                return true;
            }
           // Controller.api.bindevent();
            Form.api.bindevent($("form[role=form]"), function(data, ret){
                console.log("this 哦",this);
                //成功
               // Toastr.success(data);
            }, function(data, ret){
                //console.log("this 哦",this);
                //Toastr.success("失败");
               // Toastr.error(__("失败"));
            }, function(success, error){
                console.log("on submit",this);
                console.log("organizationInputs",organizationInputs.value);
                if(checkOrganizationInputs()==false){
                    console.log("Toastr",Toastr);
                    Toastr.error(__("Enter at least one organization name"));
                    return false;
                }
                Form.api.submit(this, success, error);
                //防止提交后多次提示
                return false;


            });
        },
        import:function(){
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
