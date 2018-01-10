var t = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (t) {
  return typeof t
}
  : function (t) {
    return t && "function" == typeof Symbol && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t
  },
  e = require("jquery");
module.exports = {
  toQueryPair: function (t, e) {
    return void 0 === e ? t : t + "=" + encodeURIComponent(null === e ? "" : String(e))
  },
  getUrl: function (n, o, i) {
    n = n.replace(/\//gi, ".");
    var a = getApp().globalData.api + "&r=" + n;
    return o && ("object" == (void 0 === o ? "undefined" : t(o)) ? a += "&" + e.param(o) : "string" == typeof o && (a += "&" + o)),
      a
  },
  json: function (t, n, o, i, a, c) {
    var r = getApp(),
      s = r.getCache("userinfo"),
      u = r.getCache("usermid"),
      f = r.getCache("authkey");
    n = n || {},
      n.comefrom = "wxapp",
      s && (n.openid = "sns_wa_" + s.openid, "cacheset" != t && r.getSet()),
      u && (n.mid = u.mid, n.merchid = u.merchid);
    var d = this;
    i && d.loading(),
      n && (n.authkey = f || "");
    var p = a ? this.getUrl(t) : this.getUrl(t, n),
      l = {
        url: p + "&timestamp=" + +new Date,
        method: a ? "POST" : "GET",
        header: {
          "Content-type": a ? "application/x-www-form-urlencoded" : "application/json",
          Cookie: "PHPSESSID=" + s.openid
        }
      };
    c || delete l.header.Cookie,
      a && (l.data = e.param(n)),
      o && (l.success = function (t) {
        i && d.hideLoading(),
          "request:ok" == t.errMsg && "function" == typeof o && (r.setCache("authkey", t.data.authkey || ""), o(t.data))
      }),
      wx.request(l)
  },
  post: function (t, e, n, o, i) {
    this.json(t, e, n, o, true, i)
  },
  get: function (t, e, n, o, i) {
    this.json(t, e, n, o, false, i)
  },
  getDistanceByLnglat: function (t, e, n, o) {
    function i(t) {
      return t * Math.PI / 180
    }
    var a = i(e),
      c = i(o),
      r = a - c,
      s = i(t) - i(n),
      u = 2 * Math.asin(Math.sqrt(Math.pow(Math.sin(r / 2), 2) + Math.cos(a) * Math.cos(c) * Math.pow(Math.sin(s / 2), 2)));
    return u *= 6378137,
      u = Math.round(1e4 * u) / 1e7
  },
  alert: function (e, n) {
    "object" === (void 0 === e ? "undefined" : t(e)) && (e = JSON.stringify(e)),
      wx.showModal({
        title: "提示",
        content: e,
        showCancel: false,
        success: function (t) {
          t.confirm && "function" == typeof confirm && n()
        }
      })
  },
  confirm: function (e, n, o) {
    "object" === (void 0 === e ? "undefined" : t(e)) && (e = JSON.stringify(e)),
      wx.showModal({
        title: "提示",
        content: e,
        showCancel: true,
        success: function (t) {
          t.confirm ? "function" == typeof n && n() : "function" == typeof o && o()
        }
      })
  },
  loading: function (t) {
    void 0 !== t && "" != t || (t = "加载中"),
      wx.showToast({
        title: t,
        icon: "loading",
        duration: 5e6
      })
  },
  hideLoading: function () {
    wx.hideToast()
  },
  toast: function (t, e) {
    e || (e = "success"),
      wx.showToast({
        title: t,
        icon: e,
        duration: 1000
      })
  },
  success: function (t) {
    wx.showToast({
      title: t,
      icon: "success",
      duration: 1000
    })
  },
  upload: function (t) {
    var e = this;
    wx.chooseImage({
      success: function (n) {
        e.loading("正在上传...");
        var o = e.getUrl("util/uploader/upload", {
          file: "file"
        }),
          i = n.tempFilePaths;
        wx.uploadFile({
          url: o,
          filePath: i[0],
          name: "file",
          success: function (n) {
            e.hideLoading();
            var o = JSON.parse(n.data);
            if (0 != o.error)
              e.alert("上传失败");
            else if ("function" == typeof t) {
              var i = o.files[0];
              t(i)
            }
          }
        })
      }
    })
  },
  pdata: function (t) {
    return t.currentTarget.dataset
  },
  data: function (t) {
    return t.target.dataset
  },
  phone: function (t) {
    var e = this.pdata(t).phone;
    wx.makePhoneCall({
      phoneNumber: e
    })
  },
  pay: function (e, n, o) {
    return "object" == (void 0 === e ? "undefined" : t(e)) && ("function" == typeof n && (e.success = n, "function" == typeof o && (e.fail = o), void wx.requestPayment(e)))
  },
  cartcount: function (t) {
    this.get("member/cart/count", {}, function (e) {
      t.setData({
        cartcount: e.cartcount
      })
    })
  },
  onShareAppMessage: function (t) {
    var e = getApp(),
      n = e.getCache("sysset"),
      o = n.share || {},
      i = e.getCache("userinfo"),
      a = "",
      c = n.shopname || "",
      r = n.description || "";
    return o.title && (c = o.title),
      o.desc && (r = o.desc),
      i && (a = i.id),
      t = t || "/pages/index/index",
      t = -1 != t.indexOf("?") ? t + "&" : t + "?", {
        title: c,
        desc: r,
        path: t + "mid=" + a
      }
  }
}