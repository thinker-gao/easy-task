// index.js
var app = getApp(),
  core = app.requirejs("core"),
  ij = (app.requirejs("icons"), app.requirejs("jquery"));
Page({
  /**
   * 页面的初始数据
   */
  data: {
    route: "category",
    category: {},
    icons: app.requirejs("icons"),
    selector: 0,
    advimg: "",
    recommands: {},
    level: 0,
    back: 0,
    child: {},
    parent: {}
  },

  /**
   * 分类函数--标签切换
   */
  tabCategory: function (t) {
    console.log(t);
    this.setData({
      selector: t.target.dataset.id,
      advimg: t.target.dataset.src,
      child: t.target.dataset.child,
      back: 0
    }),
      ij.isEmptyObject(t.target.dataset.child) ? this.setData({
        level: 0
      }) : this.setData({
        level: 1
      })
  },

  /**
   * 分类函数--更新当前数据
   */
  cateChild: function (t) {
    this.setData({
      parent: t.currentTarget.dataset.parent,
      child: t.currentTarget.dataset.child,
      back: 1
    })
  },

  /**
   * 分类函数--更新上级数据
   */
  backParent: function (t) {
    this.setData({
      child: t.currentTarget.dataset.parent,
      back: 0
    })
  },

  /**
   * 分类函数-获取并更新分类数据
   */
  getCategory: function () {
    var t = this;
    core.get("shop/get_category", {}, function (e) {
      t.setData({
        category: e.category,
        show: true,
        set: e.set,
        advimg: e.set.advimg,
        recommands: e.recommands,
        child: e.recommands
      })
    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.getCategory()
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    return core.onShareAppMessage()
  }
})