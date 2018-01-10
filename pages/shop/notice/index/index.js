// pages/shop/notice/index/index.js
var app = getApp(),
  core = app.requirejs("core");
Page({

  /**
   * 页面的初始数据
   */
  data: {
    page: 1,
    loaded: false,
    loading: false,
    list: []
  },

  getList: function () {
    var t = this;
    t.setData({
      loading: true
    }),
      core.get("shop/notice/get_list", {
        page: this.data.page
      }, function (a) {
        t.setData({
          loading: false,
          show: true
        }),
          a.list.length > 0 ? t.setData({
            page: t.data.page + 1,
            list: t.data.list.concat(a.list)
          }) : a.list.length < a.pagesize && t.setData({
            loaded: true
          })
      })
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.url(core), this.getList()
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    this.data.loaded || this.getList()
  },
})