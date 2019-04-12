var  layer = null;
layui.use(['layer', ], function(){
    var layer = layui.layer
});


var nowstep = initStep();
var resetUrl = false;



if (nowstep != 0) {
    resetUrl = true;
    step(nowstep);
} else {
    step(nowstep);
}

//去某个步骤
function step(nextsum) {
    initpage();
    $('.divstep' + nextsum).show();
    $('.listep' + nextsum).addClass('on');
    if (!resetUrl && nowstep > 0 && nextsum > 0) {
        nextsum > nowstep ? nowstep++ : nowstep--;
    }
    console.log(nowstep, nextsum);
    setCookie('step', nextsum);
}


//重置页面
function initpage() {
    for (var i = 0; i <= 5; i++) {
        $('.divstep' + i).hide();
        $('.listep' + i).removeClass('on');
    }
}

//获取当前步骤数
function initStep() {
    var ckstep = getCookie('step');
    if (!ckstep) {
        return 0;
    } else {
        initpage();
        return ckstep;
    }
}

//设置cookie
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toGMTString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

//获取cookie
function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i].trim();
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

//保存信息
function save() {

    //获取表单数据
    var data = {
        'dbhost': $('#dbhost').val(),
        'dbport': $('#dbport').val(),
        'dbname': $('#dbname').val(),
        'dbuser': $('#dbuser').val(),
        'dbpass': $('#dbpass').val(),
        'admin_user': $('#admin_user').val(),
        'admin_pass': $('#admin_pass').val()
    };

    //请求保存数据
    var url = '/Install/index/save';
    $.post(url, data, function (result) {
        if (result.code != 0) {
            layer.open({
                title: '提示'
                ,content: result.msg
            });
        }
        console.log();
    }, 'json');
    console.log(data);
    //step(4);
}


// 安装进度
function jsShow(s) {
    $("#cont").append(s + "<br>").scrollTop(9999);
}

// 验证表单
var Check = {
    adm_user: function () {
        var obj = $("#adm_user");
        Check.remove(obj);
        obj.val() ? Check.tips_yes(obj) : Check.tips_no(obj, "用户名不能为空");
    },

    adm_pass: function () {
        Check.adm_user();

        var obj = $("#adm_pass");
        Check.remove(obj);
        var pass = obj.val();
        if (/ /.test(pass)) {
            Check.tips_no(obj, "建议密码不要使用空格");
        } else if (pass.length < 8) {
            Check.tips_no(obj, "建议密码不小于8位数");
        } else {
            Check.tips_yes(obj);
        }
    },

    // 删除提示
    check_ok: function () {
        var len = $(".tips_yes").length;
        if (len < 2) {
            $("#submit").attr("class", "but grey");
        } else {
            $("#submit").removeClass("grey");
        }
    },

    // 删除提示
    remove: function (obj) {
        obj.parent().children("div").remove();
    },

    // 显示加载
    tips_load: function (obj) {
        obj.after("<div class='tips_load'></div>");
    },

    // 显示正确
    tips_yes: function (obj) {
        obj.after("<div class='tips_yes'></div>");
        Check.check_ok();
    },

    // 显示错误提示
    tips_no: function (obj, s) {
        obj.after("<div class='tips_no'>" + s + "</div>");
        Check.check_ok();
    }
}

// 触发表单提示
$(".inp").focusin(function () {
    var obj = $(this);
    Check.remove(obj);
    obj.before("<div class='inp_tips'>" + obj.attr("tips") + "</div>");
    $(".inp_tips").css({top: obj.position().top - 25, left: obj.position().left + 50});
}).focusout(function () {
    Check.remove($(this));
});

// 检测创始人用户名是否填写正确
$("#adm_user").focusin(function () {
    document.onkeyup = function (e) {
        Check.adm_user();
    }
}).focusout(function () {
    document.onkeyup = null;
    Check.adm_user();
});

// 检测创始人密码是否填写正确
$("#adm_pass").focusin(function () {
    document.onkeyup = function (e) {
        Check.adm_pass();
    }
}).focusout(function () {
    document.onkeyup = null;
    Check.adm_pass();
});

// 阻止提交
$("#form").submit(function () {
    if ($("#submit").is(".grey")) return false;
});
