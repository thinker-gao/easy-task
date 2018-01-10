const app = getApp(),
  icon = app.requirejs('icons'),
  wxValidate = app.wxValidate,
  c = app.requirejs('core'),
  MAX = 60;
Page({
  data: {
    icon: icon,
    code: '',
    mobile: '',
    isError: false,
    issubmit: false,
    errortext: ''
  },
  onLoad(options) {
    this.WxValidate = app.wxValidate(
      {
        oldpwd: {
          required: true,
          minlength: 6,
          maxlength: 16
        },
        newpwd: {
          required: true,
          minlength: 6,
          maxlength: 16
        },
        repassword: {
          required: true,
          minlength: 6,
          maxlength: 16
        }
      }
      , {
        oldpwd: {
          required: '请输入正确的密码格式'
        },
        newpwd: {
          required: '请输入正确的密码格式'
        },
        repassword: {
          required: '请输入正确的密码格式'
        }
      }
    )
  },
  onShow() {
    const code = app.getCache('ccode') || 0;
    this.setData({
      code
    });
    if (code > 0) {
      this.countdown();
    }
  },
  formsubmit(e) {
    const objvalue = e.detail.value;
    if (!this.WxValidate.checkForm(e)) {
      const error = this.WxValidate.errorList[0];
      c.alert(error.msg);
      return false;
    }
    this.setData({
      issubmit: true
    });
    c.post('member/bind/changepwd', objvalue, (res)=>{
      this.setData({
        issubmit: false
      });
      if (res.status===1) {     
        c.toast('修改密码成功');
        setTimeout(() => {
          wx.redirectTo({
            url: '/pages/member/index/index'
          });
        }, 1500);
      }else {
        c.alert(res.result.message);
      }
    });
  }
})