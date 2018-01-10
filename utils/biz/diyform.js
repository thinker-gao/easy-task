var a = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (a) {
  return typeof a
}
  : function (a) {
    return a && "function" == typeof Symbol && a.constructor === Symbol && a !== Symbol.prototype ? "symbol" : typeof a
  },
  e = getApp(),
  t = e.requirejs("jquery"),
  r = e.requirejs("core"),
  i = e.requirejs("foxui");
module.exports = {
  getIndex: function (a, e) {
    if ("" == t.trim(a) || !t.isArray(e))
      return [0, 0, 0];
    var r = a.split(" "),
      i = [0, 0, 0];
    for (var n in e)
      if (e[n].name == r[0]) {
        i[0] = Number(n);
        for (var f in e[n].city)
          if (e[n].city[f].name == r[1]) {
            i[1] = Number(f);
            for (var d in e[n].city[f].area)
              if (e[n].city[f].area[d].name == r[2]) {
                i[2] = Number(d);
                break
              }
            break
          }
        break
      }
    return i
  },
  onConfirm: function (a, e) {
    var r = a.data.pval,
      i = a.data.bindAreaField,
      n = t.isEmptyObject(a.data.diyform.f_data) ? {} : a.data.diyform.f_data,
      f = a.data.areas;
    n[i] = n[i] || {},
      n[i].province = f[r[0]].name,
      n[i].city = f[r[0]].city[r[1]].name,
      a.data.noArea || (n[i].area = f[r[0]].city[r[1]].area[r[2]].name),
      a.setData({
        "diyform.f_data": n,
        showPicker: false,
        bindAreaField: false
      })
  },
  onCancel: function (a, e) {
    a.setData({
      showPicker: false
    })
  },
  onChange: function (a, e) {
    var i = e.detail.value,
      n = r.pdata(e).type,
      f = a.data.postData;
    f[n] = t.trim(i),
      a.setData({
        postData: f
      })
  },
  bindChange: function (a, e) {
    var t = a.data.pvalOld,
      r = e.detail.value;
    t[0] != r[0] && (r[1] = 0),
      t[1] != r[1] && (r[2] = 0),
      a.setData({
        pval: r,
        pvalOld: r
      })
  },
  selectArea: function (a, e) {
    var t = e.currentTarget.dataset.area,
      r = e.currentTarget.dataset.field,
      i = 1 != e.currentTarget.dataset.hasarea,
      n = a.getIndex(t, a.data.areas);
    a.setData({
      pval: n,
      pvalOld: n,
      showPicker: true,
      noArea: i,
      bindAreaField: r
    })
  },
  DiyFormHandler: function (e, i) {
    var n = i.target.dataset,
      f = n.type,
      d = n.field,
      o = n.datatype,
      s = e.data.diyform.f_data;
    (t.isArray(s) || "object" != (void 0 === s ? "undefined" : a(s))) && (s = {});
    var l = e.data.diyform.fields;
    if ("input" == f || "textarea" == f || "checkbox" == f || "date" == f || "datestart" == f || "dateend" == f)
      if ("datestart" == f)
        t.isArray(s[d]) || (s[d] = []), s[d][0] = i.detail.value;
      else if ("dateend" == f)
        t.isArray(s[d]) || (s[d] = []), s[d][1] = i.detail.value;
      else if ("checkbox" == f) {
        s[d] = {};
        for (var m in i.detail.value) {
          var u = i.detail.value[m];
          s[d
          ][u] = 1
        }
      } else
        10 == o ? (t.isEmptyObject(s[d]) && (s[d] = {}), s[d][n.name] = i.detail.value) : s[d] = i.detail.value;
    else if ("picker" == f) {
      for (var p in s)
        if (p == d) {
          for (var y in l)
            if (l[y].diy_type == d) {
              s[d] = [i.detail.value, l[y].tp_text[i.detail.value]];
              break
            }
          break
        }
    } else if ("image" == f)
      r.upload(function (a) {
        for (var t in s)
          if (t == d) {
            s[d] || (s[d] = {}),
              s[d].images || (s[d].images = []),
              s[d].images.push({
                url: a.url,
                filename: a.filename
              });
            break
          }
        s[d].count = s[d].images.length,
          e.setData({
            "diyform.f_data": s
          })
      });
    else if ("image-remove" == f) {
      for (var p in s)
        if (p == d) {
          var c = {
            images: []
          };
          for (var y in s[d].images)
            s[d].images[y].filename != n.filename && c.images.push(s[d].images[y]);
          c.count = c.images.length,
            s[d] = c;
          break
        }
    } else if ("image-preview" == f)
      for (var p in s)
        if (p == d) {
          var v = [];
          for (var y in s[d].images)
            v.push(s[d].images[y].url);
          wx.previewImage({
            current: v[n.index],
            urls: v
          });
          break
        }
    e.setData({
      "diyform.f_data": s
    })
  },
  verify: function (a, e) {
    for (var r in e.fields) {
      var n = e.fields[r],
        f = n.diy_type;
      if (1 == n.tp_must)
        if (5 == n.data_type) {
          if (!e.f_data[f] || e.f_data[f].count < 1)
            return i.toast(a, "请选择" + n.tp_name), false
        } else if (9 == n.data_type) {
          if (t.isEmptyObject(e.f_data[f]) || !e.f_data[f].province || !e.f_data[f].city)
            return i.toast(a, "请选择" + n.tp_name), false
        } else if (10 == n.data_type) {
          if (t.isEmptyObject(e.f_data[f]) || !e.f_data[f].name1)
            return i.toast(a, "请填写" + n.tp_name), false;
          if (!e.f_data[f].name2 || "" == e.f_data[f].name2)
            return i.toast(a, "请填写" + n.tp_name2), false
        } else if (!e.f_data[f])
          return i.toast(a, "请填写" + n.tp_name), false;
      if (6 == n.data_type) {
        if (!/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(e.f_data[f]))
          return i.toast(a, "请填写正确的" + n.tp_name), false
      }
      if (10 == n.data_type && (t.isEmptyObject(e.f_data[f]) || e.f_data[f].name1 != e.f_data[f].name2))
        return i.toast(a, n.tp_name + "与" + n.tp_name2 + "不一致"), false
    }
    return true
  }
}