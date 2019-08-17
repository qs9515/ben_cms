var dialog = {
    // 错误弹出层
    error: function(message) {
        layer.open({
            content:message,
            icon:2,
            title : '错误提示',
        });
    },

    //成功弹出层
    success : function(message,url) {
        layer.open({
            content : message,
            icon : 1,
            yes : function(){
                location.href=url;
            },
        });
    },

    // 确认弹出层
    confirm : function(message, url) {
        layer.open({
            content : message,
            icon:3,
            btn : ['是','否'],
            yes : function(){
                location.href=url;
            },
        });
    },

    //无需跳转到指定页面的确认弹出层
    toconfirm : function(message) {
        layer.open({
            content : message,
            icon:3,
            btn : ['确定'],
        });
    },
}

function err_500(msg) {
    var err_500='<section class="content-header">'+
        '<h1>'+
        '系统出错啦'+
        '</h1>'+
        '</section>'+
    '<section class="content">'+
    '<div class="error-page">'+
    '<h2 class="headline text-red">500</h2>'+
    '<div class="error-content">'+
    '<h3><i class="fa fa-warning text-red"></i> 程序运行意外终止！</h3>'+
    '<p>'+msg+
    '</p>'+
    '</div>'+
    '</div>'+
    '</section>';
    return err_500;
}
//右侧框架加载内容页面
function main_right_load(target_uri,obj_nav) {
    $.ajax({
        type: "GET",
        url: target_uri,
        data: '',
        beforeSend:function()
        {
            var index = layer.load(0, {shade: false});
        },
        success: function(data){
            if($("#main-content").html()!=undefined)
            {
                $("#main-content").html('');
                layer.closeAll('loading');
                $("#main-content").html(data);
            }
            else
            {
                //子页面操作父页面内容
                $("#main-content",window.parent.document).html('');
                layer.closeAll('loading');
                $("#main-content",window.parent.document).html(data);
            }
            //设置激活状态
            $(".treeview li").removeClass("active");
            $(".treeview-menu li").removeClass("active");
            if(obj_nav!='')
            {
                $(obj_nav).parent().addClass("active");
                $(obj_nav).parent().parent().parent().addClass("active");
            }
            else
            {
                //关闭layer
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                parent.layer.close(index); //再执行关闭
            }
        },
        error:function (obj,data) {
            layer.closeAll('loading');
            var res_txt=obj.responseJSON.error.message;
            if(res_txt==undefined)
            {
                if (obj.responseText!=undefined)
                {
                    res_txt=obj.responseText;
                }
                else
                {
                    res_txt = '系统发生错误！';
                }
            }
            var error_msg=err_500(res_txt);
            $("#main-content").html(error_msg);
        }
    });
}
//ajax分页
function ajax_page(target_uri) {
    if (target_uri) {
        $.ajax({
            type: "GET",
            url: target_uri,
            data: '',
            beforeSend: function () {
                var index = layer.load(0, {shade: false});
            },
            success: function (data) {
                layer.closeAll('loading');
                $("#main-content").html(data);
            },
            error: function (obj, data) {
                layer.closeAll('loading');
                var res_txt = obj.responseJSON.error.message;
                if(res_txt==undefined)
                {
                    if (obj.responseText!=undefined)
                    {
                        res_txt=obj.responseText;
                    }
                    else
                    {
                        res_txt = '系统发生错误！';
                    }
                }
                var error_msg=err_500(res_txt);
                $("#main-content").html(error_msg);
            }
        });
    }
}
//列表添加按钮
function layer_add(obj) {
    //参数
    var attr_params = $(obj).attr('attr-params');
    //弹出框标题
    var op_title = $(obj).attr('attr-title');
    var url = COMM_LIST_URI.add_url + attr_params;
    layer.open({
        type: 2,
        title:op_title,
        skin: 'layui-layer-rim', //加上边框
        area: ['60%', '70%'], //宽高
        fix: false, //不固定
        maxmin: true,
        content:url,
        end:function(){
        }
    });
};
//列表修改按钮
function layer_edit(obj) {
    //参数
    if($("input[name^='ids']:checked").length>1)
    {
        layer.msg('被选中修改的记录数大于1条，请保证修改时仅能修改一条记录！');
        return false;
    }
    if($("input[name^='ids']:checked").length<1)
    {
        layer.msg('你未选中任何记录，请先选中要修改的记录！');
        return false;
    }
    //弹出框标题
    var op_title = $(obj).attr('attr-title');
    var url = COMM_LIST_URI.add_url + '?'+$("input[name^='ids']:checked").serialize();
    layer.open({
        type: 2,
        title:op_title,
        skin: 'layui-layer-rim', //加上边框
        area: ['60%', '70%'], //宽高
        fix: false, //不固定
        maxmin: true,
        content:url,
        end:function(){
        }
    });
};
//列表修改状态
function layer_status(action,obj) {
    $(obj).prop("disabled",true);
    var jump_url = COMM_LIST_URI.search_url;
    //参数
    var attr_params = $(obj).attr('attr-params');
    if($("input[name^='ids']:checked").length<1 && attr_params==undefined)
    {
        $(obj).prop("disabled",false);
        layer.msg('你未选中任何记录，请先选中要修改的记录！');
        return false;
    }
    //弹出框标题
    var op_title = $(obj).attr('attr-title');
    if(action=='status')
    {
        if(attr_params!=undefined)
        {
            var url = COMM_LIST_URI.status_url + attr_params;
        }
        else
        {
            var url = COMM_LIST_URI.status_url + '?'+$("input[name^='ids']:checked").serialize();
        }
    }
    else if(action=='delete')
    {
        if(!confirm('确定要进行删除操作吗？'))
        {
            layer.msg('用户已取消操作！');
            $(obj).prop("disabled",false);
            return false;
        }
        if(attr_params!=undefined)
        {
            var url = COMM_LIST_URI.delete_url + attr_params;
        }
        else
        {
            var url = COMM_LIST_URI.delete_url + '?'+$("input[name^='ids']:checked").serialize();
        }
    }
    else
    {
        dialog.error('参数错误！');
        $(obj).prop("disabled",false);
        return false;
    }
    $.ajax({
        type: "GET",
        url: url,
        dataType:"json",
        beforeSend:function()
        {
            var index = layer.load(0, {shade: false});
        },
        success: function(data){
            layer.closeAll('loading');
            if(data.code=='200')
            {
                $(obj).prop("disabled",false);
                layer.msg(data.msg);
                main_right_load(jump_url);
            }
            else
            {
                $(obj).prop("disabled",false);
                dialog.error(data.msg);
            }
        },
        error:function (obj,data) {
            layer.closeAll('loading');
            var res_txt=obj.responseJSON.msg;
            if(!res_txt)
            {
                res_txt='系统发生错误！';
            }
            dialog.error(res_txt);
            $(obj).prop("disabled",false);
        }
    });
};
//表单保存
function layer_save(form_id) {
    var jump_url = COMM_LIST_URI.jump_url;
    var save_url = COMM_LIST_URI.save_url;
    if(form_id && save_url)
    {
        var postData = $("#"+form_id).serialize();
        //console.log(postData);
        // 将获取到的数据post给服务器
        $.ajax({
            type: "POST",
            url: save_url,
            data: postData,
            dataType:"json",
            beforeSend:function()
            {
                var index = layer.load(0, {shade: false});
            },
            success: function(data){
                layer.closeAll('loading');
                if(data.code=='200')
                {
                    layer.msg(data.msg);
                    main_right_load(jump_url,'');
                }
                else
                {
                    dialog.error(data.msg);
                }
            },
            error:function (obj,data) {
                layer.closeAll('loading');
                if(undefined==obj.responseJSON)
                {
                    var res_txt=obj.responseText;
                }
                else
                {
                    var res_txt=obj.responseJSON.msg;
                }
                if(!res_txt)
                {
                    res_txt='系统发生错误！';
                }
                dialog.error(res_txt);
            }
        });
    }
    else
    {
        layer.msg('参数错误！');
        return false;
    }
}
//列表检索
function table_search(form_id) {
    $("#"+form_id+" button").attr("disabled",true);
    var search_url = COMM_LIST_URI.search_url;
    if(form_id && search_url)
    {
        var postData = $("#"+form_id).serialize();
        // 将获取到的数据post给服务器
        $.ajax({
            type: "POST",
            url: search_url,
            data: postData,
            beforeSend:function()
            {
                var index = layer.load(0, {shade: false});
                $("#main-content",window.parent.document).html('');
            },
            success: function(data){
                //子页面操作父页面内容
                layer.closeAll('loading');
                $("#main-content",window.parent.document).html(data);
            },
            error:function (obj,data) {
                layer.closeAll('loading');
                var res_txt = obj.responseJSON.error.message;
                if(!res_txt)
                {
                    res_txt='系统发生错误！';
                }
                dialog.error(res_txt);
            }
        });
    }
    else
    {
        layer.msg('参数错误！');
        return false;
    }
}
//全选，反选
function chk_all(obj,input_id) {
    if($(obj).is(":checked"))
    {
        $("input[name^='ids']").prop("checked",true);
    }
    else
    {
        $("input[name^='ids']").prop("checked",false);
    }
}
//发送验证码
function btnCheck(obj,form_uri) {
    $(obj).removeClass("am-btn-warning");
    //发送验证码
    if($("#phone").val()=='')
    {
        layer.msg('请输入手机号码！');
        $("#phone").focus();
        $(obj).addClass("am-btn-warning");
        return false;
    }
    $.ajax({
        type: "POST",
        url: form_uri,
        data: $("#log-form").serialize(),
        dataType:"json",
        success: function(msg){
            layer.msg(msg.msg);
            if(msg.code==200)
            {
                var time = 120;
                $(obj).attr("disabled", true);
                var timer = setInterval(function() {
                    if (time == 0)
                    {
                        clearInterval(timer);
                        $(obj).attr("disabled", false);
                        $(obj).html("获取验证码");
                        $(obj).addClass("am-btn-warning");
                    }
                    else {
                        $(obj).html(time + "秒");
                        time--;
                    }
                }, 1000);
            }
            else
            {
                $(obj).attr("disabled", false);
                $(obj).html("获取验证码");
                $(obj).addClass("am-btn-warning");
                return false;
            }
        },
        error:function (obj) {
            if(undefined==obj.responseJSON)
            {
                var res_txt=obj.responseText;
            }
            else
            {
                var res_txt=obj.responseJSON.msg;
            }
            if(!res_txt)
            {
                res_txt='系统发生错误！';
            }
            dialog.error(res_txt);
            $(obj).attr("disabled", false);
            $(obj).html("获取验证码");
            $(obj).addClass("am-btn-warning");
            return false;
        }
    });
}
//高级搜索
function search_plus(obj) {
    if(!$("#search-more").is(":visible"))
    {
        $(obj).html('<i class="fa fa-search-minus"></i> 高级搜索');
        $("#search-more").removeClass("hidden");
        $("#search-more").show();
    }
    else
    {
        $(obj).html('<i class="fa fa-search-plus"></i> 高级搜索');
        $("#search-more").hide();
    }
}
//表单取消
function form_cacel() {
    if(confirm("确定取消保存吗?"))
    {
        var jump_url = COMM_LIST_URI.jump_url;
        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.layer.close(index); //再执行关闭
        main_right_load(jump_url);
    }
}