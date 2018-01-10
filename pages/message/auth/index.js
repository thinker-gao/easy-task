var t = getApp();
Page({
  data: {},
  onLoad: function (t) {
    console.log(t),
      this.setData({
        close: t.close,
        text: t.text
      })
  },
  onShow: function () {
    var e = t.getCache("sysset").shopname;
    wx.setNavigationBarTitle({
      title: e || "提示"
    })
  }
})