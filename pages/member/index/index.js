var app = getApp(),
  c = app.requirejs("core");
Page({
  data: {
    icon: app.requirejs("icons"),
    member: {},
    isShow: true,
    skin: app.getCache('skin'),//皮肤
  },
  onLoad: function (r) {
    const userinfo = app.getCache('userinfo');
    userinfo && this.setData({
      userinfo
    });
  },
  getInfo: function () {
    //获取用户数据
    c.post('member/info', {}, (res)=>{
      this.setData({
        member: res.member
      });
    }, true);
  },
  onShow: function () {
    this.setData({
      skin: app.getCache('skin')
    });
    this.getInfo();
    const islogin = app.getCache('islogin');
    !islogin && wx.redirectTo({
      url: '/pages/member/login/login'
    })
  }
})