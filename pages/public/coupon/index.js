var t = getApp(),
  e = t.requirejs("core"),
  s = t.requirejs("jquery");
Page({
  data: {
    type: 0,
    merchs: [],
    goodslist: [],
    goodsid: 0,
    money: 0,
    list: [],
    loading: true
  },
  onLoad: function (e) {
    if (Number(e.type))
      this.setData({
        money: e.money
      });
    else {
      var s = t.getCache("goodsInfo");
      this.setData({
        goodslist: s.goodslist,
        merchs: s.merchs
      })
    }
    this.setData({
      type: e.type
    }),
      this.getList()
  },
  getList: function () {
    var t = this,
      s = this.data;
    s.type < 2 && e.get("sale/coupon/query", {
      type: s.type,
      money: s.money,
      goods: s.goodslist,
      merchs: s.merchs
    }, function (e) {
      t.setData({
        list: e.list,
        loading: false
      })
    })
  },
  search: function (t) {
    var e = t.detail.value,
      a = this.data.old_list,
      i = this.data.list,
      o = [];
    s.isEmptyObject(a) && (a = i),
      s.isEmptyObject(a) || s.each(a, function (t, s) {
        -1 != s.couponname.indexOf(e) && o.push(s)
      }),
      this.setData({
        list: o,
        old_list: a
      })
  },
  bindBtn: function (e) {
    var s = this.data,
      a = e.currentTarget.dataset;
    s.type < 2 && (t.setCache("coupon", a, 20), wx.navigateBack())
  }
})