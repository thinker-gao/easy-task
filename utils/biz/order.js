var r = getApp(),
  e = r.requirejs("core");
module.exports = {
  url: function (r) {
    wx.redirectTo({
      url: r
    })
  },
  cancelArray: ["我不想买了", "信息填写错误，重新拍", "同城见面交易", "其他原因"],
  order: ["确认要取消该订单吗?", "确认要删除该订单吗?", "确认要彻底删除该订单吗?", "确认要恢复该订单吗?", "确认已收到货了吗?", "确定您要取消申请?"],
  cancel: function (r, o, t) {
    var i = this,
      n = this.cancelArray[o];
    e.post("order/op/cancel", {
      id: r,
      remark: n
    }, function (r) {
      0 == r.error && i.url(t)
    }, true)
  },
  delete: function (r, o, t, i) {
    var n = this;
    e.confirm(0 == o ? this.order[3] : this.order[o], function () {
      e.post("order/op/delete", {
        id: r,
        userdeleted: o
      }, function (r) {
        if (0 == r.error)
          return void (void 0 !== i ? (i.setData({
            page: 1,
            list: []
          }), i.get_list()) : n.url(t));
        e.toast(r.message, "loading")
      }, true)
    })
  },
  finish: function (r, o) {
    var t = this;
    e.confirm(this.order[4], function () {
      e.post("order/op/finish", {
        id: r
      }, function (r) {
        if (0 == r.error)
          return void t.url(o);
        e.toast(r.message, "loading")
      }, true)
    })
  },
  refundcancel: function (r, o) {
    var t = this;
    e.confirm(this.order[5], function () {
      e.post("order/refund/cancel", {
        id: r
      }, function (r) {
        if (0 == r.error)
          return void ("function" == typeof o ? o() : t.url(o));
        e.toast(r.message, "loading")
      }, true)
    })
  },
  codeshow: function (r, o) {
    var t = e.data(o).orderid;
    e.post("verify/qrcode", {
      id: t
    }, function (r) {
      0 == r.error ? $this.setData({
        code: true,
        qrcode: r.url
      }) : e.alert(r.message)
    }, true)
  },
  codehidden: function (r) {
    r.setData({
      code: false
    })
  }
}