var e = getApp(),
  r = e.requirejs("core");
module.exports.getAreas = function (e) {
  r.get("shop/get_areas", {}, function (r) {
    var a = [];
    for (var t in r.areas.province)
      if (0 != t) {
        var i = (r.areas.province[t]["@attributes"].name, []);
        for (var n in r.areas.province[t].city)
          if (0 != n) {
            var c = [];
            r.areas.province[t].city[n].name;
            for (var o in r.areas.province[t].city[n].county) {
              if (r.areas.province[t].city[n].county[o].hasOwnProperty("@attributes"))
                var s = r.areas.province[t].city[n].county[o]["@attributes"].name;
              else
                var s = r.areas.province[t].city[n].county[o].name;
              c.push(s)
            }
            i.push({
              city_name: c
            })
          }
        a.push({
          province_name: i
        })
      }
    "function" == typeof e && e(r.areas)
  })
}