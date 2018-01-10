// pages/shop/notice/detail/detail.js
var app = getApp(),
  core = app.requirejs("core"),
  wxparse = app.requirejs("wxParse/wxParse");

Page({

  /**
   * 页面的初始数据
   */
  data: {
    id: "-",
    title: "-",
    createtime: "-"
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var r = this;
    r.setData({
      id: options.id
    }),
      app.url(options),
      core.get("shop/notice/detail", {
        id: this.data.id
      }, function (t) {
        var e = t.notice;
        wxparse.wxParse("wxParseData", "html", e.detail, r, "5"),
          r.setData({
            show: true,
            title: e.title,
            createtime: e.createtime
          })
      })
  }
})