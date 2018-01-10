const app = getApp(),
  icon = app.requirejs('icons'),
  wxValidate = app.wxValidate,
  c = app.requirejs('core');
Page({
  data: {
    icon: icon,
    issubmit: false
  },
  onLoad: function (options) {
    this.WxValidate = app.wxValidate(
      {
        mobile: {
          required: true,
          tel: true,
        },
        pwd: {
          required: true,
          minlength: 6,
          maxlength: 16
        }
      }
      , {
        mobile: {
          required: '请填写您的手机号',
          tel: '请填写正确的手机号码格式'
        },
        pwd: {
          required: '请填写正确的密码格式'
        }
      }
    )
  },
  onShow: function () {
  },
  formsubmit(e) {
    const objvalue = e.detail.value;
    if (this.data.isError) return false;
    if (!this.WxValidate.checkForm(e)) {
      const error = this.WxValidate.errorList[0];
      c.alert(error.msg);
      return false;
    }
    this.setData({
      isError: false,
      issubmit: true
    });

    //此处数据请求，暂时模拟
    c.post('member/bind/login', objvalue, (res) => {
      this.setData({
        issubmit: false
      });
      if (res.status === 1) {
        app.setCache('islogin', true);
        c.toast('登录成功');
        setTimeout(() => {
          wx.redirectTo({
            url: '/pages/index/index'
          })
        }, 1500);
      } else {
        c.alert(res.result.message);
      }
    });
  }
})